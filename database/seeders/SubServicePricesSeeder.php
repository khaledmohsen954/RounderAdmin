<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServicePrice;
use App\Modules\Properties\Models\Region;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServicePricesSeeder extends Seeder
{
    /**
     * تشغيل بذر الأسعار لجميع الخدمات الفرعية مقسمة لكل منطقة جغرافية على حدة
     * وتغطية كافة مدن كل منطقة بنسبة 100%.
     */
    public function run(): void
    {
        $provider = ProviderProfile::where('is_approved', true)->first() ?? ProviderProfile::first();

        if (!$provider) {
            $this->command?->error('لا يوجد مزود خدمة في قاعدة البيانات.');
            return;
        }

        // 1. ربط المزود بجميع الخدمات الـ 16 المتاحة
        $allServiceIds = Service::where('is_active', true)->pluck('id')->all();
        $provider->services()->syncWithoutDetaching($allServiceIds);

        // 2. تفريغ الجداول القديمة لإعادة الهيكلة المنظمة
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sub_service_price_cities')->truncate();
        DB::table('sub_service_prices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. جلب المناطق الأربعة النشطة التي تحتوي على مدن
        $regions = Region::where('is_active', true)
            ->whereHas('cities')
            ->orderBy('id')
            ->get();

        // 4. جلب جميع الخدمات الفرعية النشطة
        $subServices = SubService::with('service')->where('is_active', true)->get();

        $basePrices = [
            'plumbing' => 120,
            'electrical' => 110,
            'home_cleaning' => 220,
            'appliances' => 140,
            'pest_control' => 180,
            'carpentry' => 130,
            'water_heaters' => 120,
            'ikea' => 85,
            'water_filters' => 95,
            'blacksmith' => 150,
            'ac_split' => 140,
            'ac_window' => 100,
            'ac_central' => 280,
            'ac_concealed' => 200,
            'refrigerators' => 150,
            'ovens' => 130,
            'washing_machines' => 140,
        ];

        // فروق بسيطة واقعية بين المناطق
        $regionPriceAdjustments = [
            5 => 0,    // المنطقة الوسطى (الأساس)
            7 => 10,   // المنطقة الغربية
            6 => 5,    // المنطقة الشرقية
            8 => -5,   // المنطقة الشمالية
        ];

        $this->command?->info("بدء إضافة الأسعار لـ {$subServices->count()} خدمة فرعية عبر " . $regions->count() . " مناطق جغرافية...");

        $createdCount = 0;

        foreach ($subServices as $subService) {
            $serviceCode = $subService->service?->code ?? '';
            $base = $basePrices[$serviceCode] ?? ($subService->service?->base_price > 0 ? (float) $subService->service->base_price : 120);

            // تدرج تسعيري واقعي لكل خدمة فرعية
            $subVariation = (($subService->id * 17) % 65) - 15;

            foreach ($regions as $region) {
                $regionAdj = $regionPriceAdjustments[$region->id] ?? 0;
                $finalPrice = max(50, round(($base + $subVariation + $regionAdj) / 5) * 5);

                $priceRecord = SubServicePrice::create([
                    'provider_profile_id' => $provider->id,
                    'sub_service_id' => $subService->id,
                    'region_id' => $region->id,
                    'price' => $finalPrice,
                    'is_active' => true,
                ]);

                $createdCount++;

                // ربط كافة مدن هذه المنطقة بالسجل
                DB::statement(
                    'INSERT IGNORE INTO sub_service_price_cities (sub_service_price_id, city_id) 
                     SELECT ?, id FROM cities WHERE region_id = ? AND is_active = 1',
                    [$priceRecord->id, $region->id]
                );
            }
        }

        $this->command?->info("تم بنجاح! تم إنشاء {$createdCount} تسعيرة مقسمة ومربوطة بجميع مدن كل منطقة.");
    }
}
