<?php

namespace Database\Seeders;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Community\Models\Announcement;
use App\Modules\Community\Models\AnnouncementTranslation;
use App\Modules\Community\Models\Initiative;
use App\Modules\Community\Models\InitiativeTranslation;
use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Providers\Models\ProviderCommissionStatus;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $residentRole = Role::firstOrCreate(['code' => RoleCode::Resident->value]);
        $providerRole = Role::firstOrCreate(['code' => RoleCode::Provider->value]);

        $city = City::first() ?? City::create(['code' => 'RUH', 'is_active' => true]);

        // 1. Create Demo Properties / Homes
        $home1 = Home::firstOrCreate(['name' => '1'], [
            'city_id' => $city->id,
            'address' => 'طريق الملك فهد، الرياض',
            'district' => 'العليا',
            'property_code' => 'HOM-001',
            'health_percent' => 95,
            'association_balance' => 15000,
            'floors_count' => 6,
        ]);

        $home252 = Home::firstOrCreate(['name' => '252'], [
            'city_id' => $city->id,
            'address' => 'شارع التخصصي، الرياض',
            'district' => 'المحمدية',
            'property_code' => 'HOM-252',
            'health_percent' => 90,
            'association_balance' => 24000,
            'floors_count' => 8,
        ]);

        $homeDsg = Home::firstOrCreate(['name' => 'dsgasgag'], [
            'city_id' => $city->id,
            'address' => 'طريق أنس بن مالك، الرياض',
            'district' => 'الملقا',
            'property_code' => 'HOM-301',
            'health_percent' => 88,
            'association_balance' => 8500,
            'floors_count' => 4,
        ]);

        $homeTower = Home::firstOrCreate(['name' => 'برج النخيل السكني'], [
            'city_id' => $city->id,
            'address' => 'طريق عثمان بن عفان، الرياض',
            'district' => 'الواحة',
            'property_code' => 'HOM-104',
            'health_percent' => 98,
            'association_balance' => 32000,
            'floors_count' => 12,
        ]);

        // 2. Create Demo Residents
        $residentsData = [
            [
                'name' => 'سليبيلي',
                'email' => 'mob@gmail.com',
                'phone' => '0565656565',
                'created_at' => '2026-11-02 09:15:00',
                'homes' => [],
                'bookings_count' => 0,
            ],
            [
                'name' => 'هشام',
                'email' => 'Heshamnagia@gmail.com',
                'phone' => '0583211106',
                'created_at' => '2026-11-02 10:20:00',
                'homes' => [
                    ['home_id' => $home1->id, 'unit_label' => '1', 'is_primary' => true],
                ],
                'bookings_count' => 1,
            ],
            [
                'name' => 'shereen',
                'email' => 'shereen.shapaan12@gmail.com',
                'phone' => '0587789999',
                'created_at' => '2026-11-02 11:45:00',
                'homes' => [],
                'bookings_count' => 0,
            ],
            [
                'name' => 'Mohamed',
                'email' => 'Mo120@gmail.com',
                'phone' => '0557475147',
                'created_at' => '2026-11-01 14:10:00',
                'homes' => [
                    ['home_id' => $home252->id, 'unit_label' => '85', 'is_primary' => true],
                    ['home_id' => $homeDsg->id, 'unit_label' => '11', 'is_primary' => false],
                ],
                'bookings_count' => 5,
            ],
            [
                'name' => 'Mohamed',
                'email' => 'mohammedkhaled15621@gmail.com',
                'phone' => '0566666666',
                'created_at' => '2026-11-01 16:30:00',
                'homes' => [],
                'bookings_count' => 0,
            ],
            [
                'name' => 'عبدالله القحطاني',
                'email' => 'abdullah.qahtani@gmail.com',
                'phone' => '0507778899',
                'created_at' => '2026-11-03 08:00:00',
                'homes' => [
                    ['home_id' => $homeTower->id, 'unit_label' => '402', 'is_primary' => true],
                ],
                'bookings_count' => 2,
            ],
            [
                'name' => 'نورة السبيعي',
                'email' => 'noura.subaie@gmail.com',
                'phone' => '0543332211',
                'created_at' => '2026-11-03 12:30:00',
                'homes' => [
                    ['home_id' => $homeTower->id, 'unit_label' => '501', 'is_primary' => true],
                ],
                'bookings_count' => 1,
            ],
        ];

        $pendingStatus = BookingStatus::where('code', 'pending')->first() ?? BookingStatus::first();
        $service = Service::first();

        foreach ($residentsData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $residentRole->id,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'locale' => 'ar',
                    'password' => '12341234',
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ]
            );

            // Link homes
            foreach ($data['homes'] as $hInfo) {
                ResidentHome::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'home_id' => $hInfo['home_id'],
                    ],
                    [
                        'unit_label' => $hInfo['unit_label'],
                        'is_primary' => $hInfo['is_primary'],
                        'residence_type' => 'apartment',
                        'ownership_type' => 'owner',
                    ]
                );
            }

            // Create bookings
            for ($i = 0; $i < $data['bookings_count']; $i++) {
                $linkedHomeId = !empty($data['homes']) ? $data['homes'][0]['home_id'] : $home1->id;
                Booking::create([
                    'resident_id' => $user->id,
                    'home_id' => $linkedHomeId,
                    'service_id' => $service?->id,
                    'booking_status_id' => $pendingStatus?->id,
                    'scheduled_at' => now()->addDays($i + 1),
                    'total_amount' => 250.00,
                    'notes' => 'حجز تجريبي رقم ' . ($i + 1),
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ]);
            }
        }

        // 3. Create Demo Service Provider
        $providerUser = User::updateOrCreate(
            ['email' => 'bidusosid@mailinator.com'],
            [
                'role_id' => $providerRole->id,
                'name' => 'Nasim Schultz',
                'phone' => '0512344444',
                'locale' => 'ar',
                'password' => '12341234',
                'created_at' => '2026-11-01 08:00:00',
                'updated_at' => '2026-11-01 08:00:00',
            ]
        );

        $providerProfile = ProviderProfile::updateOrCreate(
            ['user_id' => $providerUser->id],
            [
                'company_name' => 'شركة تطبيق المستقبل لتقنيه نظم المعلومات',
                'is_approved' => true,
                'created_at' => '2026-11-01 08:00:00',
                'updated_at' => '2026-11-01 08:00:00',
            ]
        );

        // Sync services to provider
        $providerServices = Service::whereIn('code', [
            'ac_concealed',
            'electrical',
            'ovens',
            'refrigerators',
            'water_heaters',
            'home_cleaning',
        ])->pluck('id')->toArray();

        if (empty($providerServices)) {
            $providerServices = Service::take(5)->pluck('id')->toArray();
        }
        $providerProfile->services()->sync($providerServices);

        // Contract
        $activeContractStatus = ProviderContractStatus::where('code', 'active')->first() ?? ProviderContractStatus::first();

        $contract = ProviderContract::updateOrCreate(
            ['provider_profile_id' => $providerProfile->id],
            [
                'status_id' => $activeContractStatus->id,
                'reference' => 'PC-FUT-' . Str::upper(Str::random(8)),
                'service_commission_percent' => 10.00,
                'emergency_commission_percent' => 15.00,
                'starts_on' => '2026-11-01',
                'ends_on' => null,
                'terms' => 'عقد شراكة معتمد لتقديم خدمات الصيانة المنزلية والتشغيل السكني وفق المعايير المعتمدة.',
                'created_at' => '2026-11-01 08:00:00',
                'updated_at' => '2026-11-01 08:00:00',
            ]
        );

        // Commission Accrued Entry (SAR 40.00)
        $settledStatus = ProviderCommissionStatus::where('code', 'settled')->first()
            ?? ProviderCommissionStatus::firstOrCreate(['code' => 'settled']);

        ProviderCommissionEntry::firstOrCreate(
            [
                'provider_profile_id' => $providerProfile->id,
                'provider_contract_id' => $contract->id,
            ],
            [
                'status_id' => $settledStatus->id,
                'gross_amount' => 400.00,
                'commission_percent_snapshot' => 10.00,
                'commission_amount' => 40.00,
                'calculated_at' => '2026-11-02 12:00:00',
                'settled_at' => '2026-11-02 12:00:00',
                'created_at' => '2026-11-02 12:00:00',
                'updated_at' => '2026-11-02 12:00:00',
            ]
        );

        // 4. Create Demo Announcements, Initiatives & Facility Reports for Content
        $announcement = Announcement::create([
            'home_id' => null,
            'reference' => 'AN-' . Str::upper(Str::random(10)),
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_published' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        AnnouncementTranslation::create([
            'announcement_id' => $announcement->id,
            'locale' => 'ar',
            'title' => 'ترحيب بسكان المجمع السكني الكرام وتحديث التطبيق',
            'body' => 'يسر إدارة راوندر الترحيب بجميع السكان، ونرجو تحديث التطبيق إلى آخر إصدار لتفعيل خدمات الصيانة والمبادرات المجتمعية المباشرة.',
        ]);
        AnnouncementTranslation::create([
            'announcement_id' => $announcement->id,
            'locale' => 'en',
            'title' => 'Welcome to Residents and New App Update',
            'body' => 'Rounder administration welcomes all residents. Please update your mobile application to access maintenance services and community initiatives.',
        ]);

        $initiative = Initiative::create([
            'home_id' => $homeTower->id,
            'reference' => 'IN-' . Str::upper(Str::random(10)),
            'title' => 'مبادرة تشجير وتجميل مدخل المجمع السكني',
            'description' => 'مبادرة مجتمعية مشتركة بين السكان لتشجير المساحات المشتركة وزيادة المساحات الخضراء والجمالية.',
            'target_amount' => 5000.00,
            'starts_at' => now(),
            'ends_at' => now()->addDays(20),
            'is_published' => true,
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);
        InitiativeTranslation::create([
            'initiative_id' => $initiative->id,
            'locale' => 'ar',
            'title' => 'مبادرة تشجير وتجميل مدخل المجمع السكني',
            'description' => 'مبادرة مجتمعية مشتركة بين السكان لتشجير المساحات المشتركة وزيادة المساحات الخضراء والجمالية.',
        ]);
        InitiativeTranslation::create([
            'initiative_id' => $initiative->id,
            'locale' => 'en',
            'title' => 'Community Greenery & Landscaping Initiative',
            'description' => 'A joint resident initiative to plant common areas and enhance landscaping and green areas.',
        ]);

        // Create a facility category
        $facilityCat = \App\Modules\Facilities\Models\FacilityCategory::firstOrCreate(
            ['code' => 'lighting'],
            ['is_active' => true]
        );
        \App\Modules\Facilities\Models\FacilityCategoryTranslation::updateOrCreate(
            ['facility_category_id' => $facilityCat->id, 'locale' => 'ar'],
            ['name' => 'الإنارة والمصاعد']
        );
        \App\Modules\Facilities\Models\FacilityCategoryTranslation::updateOrCreate(
            ['facility_category_id' => $facilityCat->id, 'locale' => 'en'],
            ['name' => 'Lighting & Elevators']
        );

        $firstResidentHome = ResidentHome::first();

        if ($firstResidentHome) {
            FacilityReport::create([
                'home_id' => $firstResidentHome->home_id,
                'resident_home_id' => $firstResidentHome->id,
                'facility_category_id' => $facilityCat->id,
                'status_id' => $pendingStatus?->id ?? 1,
                'reference' => 'FR-2026-081',
                'title' => 'عطل في إنارة الممر الجانبي بالدور الأرضي',
                'description' => 'لوحظ انقطاع الكهرباء عن الإضاءة الرئيسية في الممر المؤدي للمصاعد.',
                'location' => 'الدور الأرضي - الممر الشرقي',
                'reported_at' => now()->subHours(5),
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ]);
        }
    }
}
