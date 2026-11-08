<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Catalog\Models\SubService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * يستورد ملحق أجير (database/seeders/data/ajeer_prices.csv) إلى services / sub_services
 * مع الترجمات (ar + en) في service_translations و sub_service_translations.
 *
 * - sub_services.code = {service_code}_{legacy_id} لأن code unique وأرقام الـ PDF تتكرر بين الأقسام.
 * - آمن للتشغيل أكتر من مرة (updateOrCreate).
 */
class AjeerCatalogSeeder extends Seeder
{
    /** service_code => [category_code, الاسم العربي, الاسم الإنجليزي] */
    private const SERVICES = [
        'plumbing'         => ['maintenance', 'السباكة', 'Plumbing'],
        'electrical'       => ['maintenance', 'الكهرباء', 'Electrical'],
        'home_cleaning'    => ['cleaning', 'النظافة', 'Cleaning'],
        'ac_split'         => ['maintenance', 'مكيف سبليت', 'Split AC'],
        'ac_window'        => ['maintenance', 'مكيف شباك', 'Window AC'],
        'ac_central'       => ['maintenance', 'مكيف مركزي', 'Central AC'],
        'ac_concealed'     => ['maintenance', 'مكيف مخفي', 'Concealed AC'],
        'refrigerators'    => ['maintenance', 'الثلاجات', 'Refrigerators'],
        'ovens'            => ['maintenance', 'الأفران', 'Ovens'],
        'washing_machines' => ['maintenance', 'الغسالات', 'Washing Machines'],
        'pest_control'     => ['cleaning', 'مكافحة الحشرات', 'Pest Control'],
        'carpentry'        => ['maintenance', 'النجارة والأثاث', 'Carpentry & Furniture'],
        'water_heaters'    => ['maintenance', 'السخانات', 'Water Heaters'],
        'ikea'             => ['maintenance', 'تركيبات إيكيا', 'IKEA Assembly'],
        'water_filters'    => ['maintenance', 'أجهزة تحلية المياه', 'Water Filters'],
        'blacksmith'       => ['maintenance', 'الحدادة', 'Blacksmith'],
    ];

    public function run(): void
    {
        $handle = fopen(database_path('seeders/data/ajeer_prices.csv'), 'r');
        $header = fgetcsv($handle);
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF"); // إزالة BOM

        DB::transaction(function () use ($handle, $header) {
            $services = [];
            $sort = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $r = array_combine($header, $row);
                $code = $r['service_code'];

                if (! isset(self::SERVICES[$code])) {
                    $this->command?->warn("تخطي ID {$r['legacy_id']}: service_code غير معروف ({$code})");
                    continue;
                }

                $services[$code] ??= $this->service($code);

                $sub = SubService::updateOrCreate(
                    ['code' => $code.'_'.$r['legacy_id']],
                    [
                        'service_id'                 => $services[$code]->id,
                        'sort_order'                 => ++$sort,
                        'is_active'                  => true,
                        'price_eastern'              => $r['price_eastern'] !== '' ? $r['price_eastern'] : null,
                        'price_qassim_madinah_kharj' => $r['price_qassim_madinah_kharj'] !== '' ? $r['price_qassim_madinah_kharj'] : null,
                    ]
                );

                foreach (['ar' => 'name_ar', 'en' => 'name_en'] as $locale => $column) {
                    if (filled($r[$column] ?? null)) {
                        $sub->translations()->updateOrCreate(['locale' => $locale], ['name' => $r[$column]]);
                    }
                }
            }
        });

        fclose($handle);
    }

    private function service(string $code): Service
    {
        [$categoryCode, $nameAr, $nameEn] = self::SERVICES[$code];

        $service = Service::firstOrCreate(
            ['code' => $code],
            [
                'service_category_id' => ServiceCategory::where('code', $categoryCode)->firstOrFail()->id,
                'base_price'          => 0,
                'is_active'           => true,
            ]
        );

        $service->translations()->firstOrCreate(['locale' => 'ar'], ['name' => $nameAr]);
        $service->translations()->firstOrCreate(['locale' => 'en'], ['name' => $nameEn]);

        return $service;
    }
}