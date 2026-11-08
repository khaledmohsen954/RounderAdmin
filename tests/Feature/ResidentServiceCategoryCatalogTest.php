<?php

namespace Tests\Feature;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Catalog\Models\ServiceTranslation;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServiceTranslation;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResidentServiceCategoryCatalogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_categories_include_active_services_and_their_active_subservices(): void
    {
        $this->seed();
        app()->setLocale('en');

        $resident = User::create([
            'role_id' => Role::where('code', 'resident')->value('id'),
            'name' => 'Catalog Resident',
            'phone' => '0501000001',
            'locale' => 'en',
            'password' => 'Password123!',
        ]);
        Sanctum::actingAs($resident);

        $category = ServiceCategory::create(['code' => 'home_care', 'is_active' => true]);
        $category->translations()->create(['locale' => 'en', 'name' => 'Home care']);

        $service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'cleaning',
            'base_price' => 120,
            'is_active' => true,
        ]);
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Cleaning',
            'description' => 'Home cleaning',
        ]);

        $subService = SubService::create([
            'service_id' => $service->id,
            'code' => 'deep_cleaning',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        SubServiceTranslation::create([
            'sub_service_id' => $subService->id,
            'locale' => 'en',
            'name' => 'Deep cleaning',
            'description' => 'Detailed cleaning',
        ]);

        SubService::create([
            'service_id' => $service->id,
            'code' => 'disabled_option',
            'is_active' => false,
        ]);
        Service::create([
            'service_category_id' => $category->id,
            'code' => 'disabled_service',
            'base_price' => 100,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/resident/service-categories');

        $response->assertOk();
        $categoryResult = collect($response->json('data'))->firstWhere('id', $category->id);

        $this->assertSame('Home care', $categoryResult['name']);
        $this->assertCount(1, $categoryResult['services']);
        $this->assertSame('Cleaning', $categoryResult['services'][0]['name']);
        $this->assertEquals(120, $categoryResult['services'][0]['price']);
        $this->assertCount(1, $categoryResult['services'][0]['sub_services']);
        $this->assertSame('Deep cleaning', $categoryResult['services'][0]['sub_services'][0]['name']);
        $this->assertSame($service->id, $categoryResult['services'][0]['sub_services'][0]['service_id']);
    }
}
