<?php
namespace Database\Seeders;
use App\Modules\Properties\Services\SaudiCityImportService;
use Illuminate\Database\Seeder;
/** يمكن تشغيله عند ترقية قاعدة قائمة دون تغيير باقي بيانات المشروع. */
class SaudiCitiesSeeder extends Seeder
{
    /** @return void @throws \Throwable */
    public function run(): void
    {
        app(SaudiCityImportService::class)->import();
    }
}
