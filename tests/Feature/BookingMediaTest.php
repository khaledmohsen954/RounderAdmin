<?php

namespace Tests\Feature;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingMedia;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingMediaTest extends TestCase
{
    use DatabaseTransactions;

    private User $resident;
    private Admin $admin;
    private int $homeId;
    private int $residentHomeId;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $residentRole = Role::where('code', 'resident')->firstOrFail();
        $this->resident = User::create([
            'role_id' => $residentRole->id,
            'name' => 'Resident Tester',
            'phone' => '0501112233',
            'locale' => 'ar',
            'password' => 'Password123!',
        ]);

        $this->admin = Admin::create([
            'name' => 'Admin Tester',
            'phone' => '0559998877',
            'email' => 'admin.test@rounder-sa.com',
            'password' => 'AdminPass123!',
            'locale' => 'ar',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $cityId = DB::table('cities')->insertGetId(['code' => 'RUH', 'created_at' => now(), 'updated_at' => now()]);
        $this->homeId = DB::table('homes')->insertGetId([
            'city_id' => $cityId,
            'name' => 'Al-Nakheel Home',
            'address' => 'Riyadh, Al-Nakheel',
            'property_code' => 'PROP-NAKHEEL',
            'health_percent' => 100,
            'association_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->residentHomeId = DB::table('resident_homes')->insertGetId([
            'user_id' => $this->resident->id,
            'home_id' => $this->homeId,
            'unit_label' => 'U-101',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = ServiceCategory::create(['code' => 'plumbing', 'is_active' => true]);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'PIPE-REPAIR',
            'base_price' => 250.00,
            'is_active' => true,
        ]);
        $this->service->translations()->create(['locale' => 'ar', 'name' => 'إصلاح أنابيب']);
    }

    public function test_resident_can_create_booking_with_images_via_api(): void
    {
        Sanctum::actingAs($this->resident, ['*']);

        $image1 = UploadedFile::fake()->image('issue_photo1.jpg', 600, 600);
        $image2 = UploadedFile::fake()->image('issue_photo2.png', 800, 800);

        $response = $this->post('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'notes' => 'يرجى مراجعة تسريب المياه الموضح بالصور',
            'images' => [$image1, $image2],
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201);
        $bookingId = $response->json('data.id');
        $this->assertNotNull($bookingId);

        // Check media table
        $this->assertDatabaseCount('booking_media', 2);
        $mediaRecords = BookingMedia::where('booking_id', $bookingId)->get();
        $this->assertCount(2, $mediaRecords);

        foreach ($mediaRecords as $media) {
            $this->assertEquals($this->resident->id, $media->uploaded_by);
            $this->assertNull($media->admin_uploaded_by);
            $this->assertEquals('photo', $media->kind);
            $this->assertNotEmpty($media->url);
            Storage::disk('public')->assertExists($media->path);
        }

        // Test index API returns images list
        $indexResponse = $this->getJson('/api/v1/resident/bookings');
        $indexResponse->assertStatus(200);
        $bookingData = collect($indexResponse->json('data'))->firstWhere('id', $bookingId);
        $this->assertNotNull($bookingData);
        $this->assertCount(2, $bookingData['images']);
        $this->assertNotEmpty($bookingData['images'][0]['url']);
    }

    public function test_resident_can_create_booking_with_sub_service_id_in_api(): void
    {
        Sanctum::actingAs($this->resident, ['*']);

        $subService = \App\Modules\Catalog\Models\SubService::create([
            'service_id' => $this->service->id,
            'code' => 'SUB-PIPE-LEAK',
            'is_active' => true,
        ]);
        $subService->translations()->create(['locale' => 'ar', 'name' => 'إصلاح تسريب مواسير خارجي']);

        $response = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'sub_service_id' => $subService->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'notes' => 'حجز مع تحديد خدمة فرعية',
        ]);

        $response->assertStatus(201);
        $bookingId = $response->json('data.id');
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'sub_service_id' => $subService->id,
            'service_id' => $this->service->id,
        ]);

        // Verify index response includes sub_service_id and sub_service_name
        $indexResponse = $this->getJson('/api/v1/resident/bookings');
        $indexResponse->assertStatus(200);
        $bookingData = collect($indexResponse->json('data'))->firstWhere('id', $bookingId);
        $this->assertNotNull($bookingData);
        $this->assertEquals($subService->id, $bookingData['sub_service_id']);
        $this->assertEquals('إصلاح تسريب مواسير خارجي', $bookingData['sub_service_name']);
    }

    public function test_resident_can_create_booking_with_sub_services_id_alias_in_api(): void
    {
        Sanctum::actingAs($this->resident, ['*']);

        $subService = \App\Modules\Catalog\Models\SubService::create([
            'service_id' => $this->service->id,
            'code' => 'SUB-PIPE-VALVE',
            'is_active' => true,
        ]);
        $subService->translations()->create(['locale' => 'ar', 'name' => 'تغيير محبس رئيسي']);

        // Test passing plural sub_services_id without explicit service_id (auto-resolved)
        $response = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'sub_services_id' => $subService->id,
            'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'notes' => 'حجز باستخدام sub_services_id بالصيغة الجمع',
        ]);

        $response->assertStatus(201);
        $bookingId = $response->json('data.id');
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'sub_service_id' => $subService->id,
            'service_id' => $this->service->id,
        ]);
    }

    public function test_admin_can_create_service_order_with_images(): void
    {
        $image = UploadedFile::fake()->image('admin_photo.jpg', 500, 500);

        $response = $this->actingAs($this->admin)->post(route('admin.service-orders.store'), [
            'resident_home_id' => $this->residentHomeId,
            'service_id' => $this->service->id,
            'scheduled_at' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'notes' => 'طلب مدخل من الإدارة مع مرفقات',
            'images' => [$image],
        ]);

        $response->assertRedirect(route('admin.service-orders.index'));

        $booking = Booking::latest('id')->first();
        $this->assertNotNull($booking);

        $media = BookingMedia::where('booking_id', $booking->id)->first();
        $this->assertNotNull($media);
        $this->assertEquals($this->admin->id, $media->admin_uploaded_by);
        $this->assertNull($media->uploaded_by);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_admin_can_update_service_order_and_attach_more_images(): void
    {
        $statusPending = BookingStatus::where('code', 'pending')->firstOrFail();
        $booking = Booking::create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->residentHomeId,
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'booking_status_id' => $statusPending->id,
            'scheduled_at' => now()->addDays(2),
            'total_amount' => 250.00,
        ]);

        $newImage = UploadedFile::fake()->image('completion.jpg', 400, 400);

        $response = $this->actingAs($this->admin)->put(route('admin.service-orders.update', $booking->id), [
            'booking_status_id' => $statusPending->id,
            'note' => 'تم إضافة صور فحص المعاينة',
            'images' => [$newImage],
        ]);

        $response->assertSessionHas('status');

        $media = BookingMedia::where('booking_id', $booking->id)->first();
        $this->assertNotNull($media);
        $this->assertEquals($this->admin->id, $media->admin_uploaded_by);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_deleting_booking_cleans_up_media_files(): void
    {
        $statusCancelled = BookingStatus::where('code', 'cancelled')->firstOrFail();
        $booking = Booking::create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->residentHomeId,
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'booking_status_id' => $statusCancelled->id,
            'scheduled_at' => now()->addDays(2),
            'total_amount' => 250.00,
        ]);

        $image = UploadedFile::fake()->image('test_delete.jpg', 300, 300);
        $storage = new \App\Modules\Bookings\Services\BookingMediaStorage();
        $media = $storage->store($booking, $image, $this->resident, 'photo');

        Storage::disk('public')->assertExists($media->path);

        $response = $this->actingAs($this->admin)->delete(route('admin.service-orders.destroy', $booking->id));
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseMissing('booking_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->path);
    }
}
