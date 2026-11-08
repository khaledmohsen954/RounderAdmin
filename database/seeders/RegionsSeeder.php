<?php

namespace Database\Seeders;

use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Region;
use App\Modules\Properties\Models\RegionTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionsSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            [
                'code' => 'central',
                'country_code' => 'SA',
                'is_active' => true,
                'names' => [
                    'ar' => 'المنطقة الوسطى',
                    'en' => 'Central Region',
                ],
            ],
            [
                'code' => 'eastern',
                'country_code' => 'SA',
                'is_active' => true,
                'names' => [
                    'ar' => 'المنطقة الشرقية',
                    'en' => 'Eastern Region',
                ],
            ],
            [
                'code' => 'western',
                'country_code' => 'SA',
                'is_active' => true,
                'names' => [
                    'ar' => 'المنطقة الغربية',
                    'en' => 'Western Region',
                ],
            ],
            [
                'code' => 'northern',
                'country_code' => 'SA',
                'is_active' => true,
                'names' => [
                    'ar' => 'المنطقة الشمالية',
                    'en' => 'Northern Region',
                ],
            ],
        ];

        $regionIdsByCode = [];

        foreach ($regions as $item) {
            $region = Region::firstOrCreate(
                ['code' => $item['code']],
                [
                    'country_code' => $item['country_code'],
                    'is_active' => $item['is_active'],
                ]
            );

            $regionIdsByCode[$item['code']] = $region->id;

            foreach ($item['names'] as $locale => $name) {
                RegionTranslation::updateOrCreate(
                    [
                        'region_id' => $region->id,
                        'locale' => $locale,
                    ],
                    [
                        'name' => $name,
                    ]
                );
            }
        }

        // ربط المدن بالمناطق استناداً إلى بيانات المدن السعودية
        $jsonPath = base_path('database/data/saudi-cities/cities.json');
        if (file_exists($jsonPath)) {
            $citiesData = json_decode(file_get_contents($jsonPath), true) ?: [];

            // خريطة المناطق الإدارية (13 منطقة) إلى المناطق الجغرافية الـ 4
            $adminToGeographic = [
                // المنطقة الشرقية
                5 => 'eastern',

                // المنطقة الوسطى (الرياض، القصيم، حائل)
                1 => 'central',
                4 => 'central',
                8 => 'central',

                // المنطقة الغربية والجنوبية الغربية (مكة، المدينة، عسير، جازان، نجران، الباحة)
                2 => 'western',
                3 => 'western',
                6 => 'western',
                10 => 'western',
                11 => 'western',
                12 => 'western',

                // المنطقة الشمالية (تبوك، الحدود الشمالية، الجوف)
                7 => 'northern',
                9 => 'northern',
                13 => 'northern',
            ];

            // تجميع معرفات المدن حسب المنطقة الجغرافية
            $cityIdsGroupedByRegionCode = [];
            foreach ($citiesData as $c) {
                $sourceCityId = (int) ($c['city_id'] ?? 0);
                $adminRegionId = (int) ($c['region_id'] ?? 0);
                $geoCode = $adminToGeographic[$adminRegionId] ?? 'central';

                $cityIdsGroupedByRegionCode[$geoCode][] = $sourceCityId;
            }

            foreach ($cityIdsGroupedByRegionCode as $geoCode => $sourceCityIds) {
                $regionId = $regionIdsByCode[$geoCode] ?? null;
                if ($regionId && !empty($sourceCityIds)) {
                    foreach (array_chunk($sourceCityIds, 1000) as $chunk) {
                        City::whereIn('source_city_id', $chunk)->update(['region_id' => $regionId]);
                    }
                }
            }
        }
    }
}

