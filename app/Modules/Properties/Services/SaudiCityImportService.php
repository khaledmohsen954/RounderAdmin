<?php
namespace App\Modules\Properties\Services;
use App\Modules\Properties\Enums\CountryCode;
use App\Modules\Properties\Models\{City, CityTranslation};
use Illuminate\Support\Facades\DB;
/** استيراد مرجعي قابل لإعادة التشغيل؛ لا تُحذف مدينة مرتبطة بعقار. */
final class SaudiCityImportService
{
    /**
     * upsert بدفعات يقلل عدد الاستعلامات مع الحفاظ على Eloquent والمفاتيح المحلية.
     * @return void
     * @throws \Throwable
     */
    public function import(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/saudi-cities/cities.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($rows) {
            $now = now();
            $cities = array_map(fn (array $row) => [
                'code'=>'sa-'.$row['city_id'], 'country_code'=>CountryCode::SaudiArabia->value,
                'source_city_id'=>$row['city_id'], 'is_active'=>true, 'created_at'=>$now, 'updated_at'=>$now,
            ], $rows);
            foreach (array_chunk($cities, 500) as $chunk) {
                // إعادة الاستيراد لا تلغي تعطيل مدينة أجراه المدير.
                City::upsert($chunk, ['code'], ['country_code', 'source_city_id', 'updated_at']);
            }
            $ids = City::where('country_code', CountryCode::SaudiArabia->value)->pluck('id', 'source_city_id');
            $translations = [];
            foreach ($rows as $row) {
                foreach (['ar', 'en'] as $locale) {
                    $translations[] = ['city_id'=>$ids[$row['city_id']], 'locale'=>$locale,
                        'name'=>$row['name_'.$locale], 'created_at'=>$now, 'updated_at'=>$now];
                }
            }
            foreach (array_chunk($translations, 500) as $chunk) {
                CityTranslation::upsert($chunk, ['city_id', 'locale'], ['name', 'updated_at']);
            }
            // نحتفظ بالقاهرة لعلاقات العقارات القديمة مع منع اختيارها لعقار جديد.
            City::where('code', 'cairo')->update(['country_code'=>CountryCode::Egypt->value, 'is_active'=>false]);
        });
    }
}
