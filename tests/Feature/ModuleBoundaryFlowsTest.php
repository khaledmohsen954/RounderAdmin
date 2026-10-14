<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\{Role, User};
use App\Modules\Properties\Models\{City, Home, ResidentHome};
use App\Modules\Providers\Models\{ProviderProfile, ProviderCommissionEntry};
use App\Modules\Bookings\Models\{Booking, BookingStatus};
use App\Modules\Emergencies\Models\{EmergencyRequest, EmergencyRequestStatus, EmergencyType};
use App\Modules\Catalog\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
/** Exercise cross-module writes and emergency route identifiers. */
class ModuleBoundaryFlowsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @return void
     */
    public function test_provider_accounts_and_commissions_follow_their_domain_services(): void
    {
        $this->seed();
        $admin = User::create(['name'=>'Admin','email'=>'module-admin@example.test',
            'password'=>'StrongPassword123!','role_id'=>Role::where('code','admin')->value('id'),'locale'=>'en']);
        $resident = User::create(['name'=>'Resident','email'=>'module-resident@example.test',
            'password'=>'StrongPassword123!','role_id'=>Role::where('code','resident')->value('id'),'locale'=>'en']);
        $home = Home::create(['city_id'=>City::value('id'),'name'=>'Building','address'=>'Address','property_code'=>'MOD-001']);
        $membership = ResidentHome::create(['user_id'=>$resident->id,'home_id'=>$home->id,'unit_label'=>'1']);
        $this->actingAs($admin)->post('/admin/providers', [
            'name'=>'Provider','email'=>'module-provider@example.test','phone'=>'+966500000019',
            'locale'=>'en','password'=>'StrongPassword123!','password_confirmation'=>'StrongPassword123!',
            'company_name'=>'Test provider','is_approved'=>1,'service_commission_percent'=>10,
            'emergency_commission_percent'=>15,'starts_on'=>today()->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $provider = ProviderProfile::firstOrFail();
        $this->assertSame('provider', $provider->user->role->code);
        $this->assertTrue($provider->is_approved);

        $this->post('/admin/service-orders', [
            'resident_home_id'=>$membership->id,'service_id'=>Service::value('id'),
            'scheduled_at'=>now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasNoErrors();
        $booking = Booking::firstOrFail();
        $this->put('/admin/service-orders/'.$booking->id, [
            'provider_profile_id'=>$provider->id,
            'booking_status_id'=>BookingStatus::where('code','completed')->value('id'),
            'gross_amount'=>1000,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals('100.00', ProviderCommissionEntry::where('booking_id',$booking->id)->firstOrFail()->commission_amount);

        $this->post('/admin/emergencies', [
            'resident_home_id'=>$membership->id,'emergency_type_id'=>EmergencyType::value('id'),
            'contact_phone'=>'+966500000020','location_details'=>'Building floor 1',
            'description'=>'Urgent water leak in the main building.','confirm_emergency'=>true,
        ])->assertSessionHasNoErrors();
        $emergency = EmergencyRequest::firstOrFail();
        $this->put('/admin/emergencies/'.$emergency->id, [
            'provider_profile_id'=>$provider->id,
            'status_id'=>EmergencyRequestStatus::where('code','resolved')->value('id'),
            'gross_amount'=>1000,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals('150.00', ProviderCommissionEntry::where('emergency_request_id',$emergency->id)->firstOrFail()->commission_amount);

        // A resident cancellation must receive the path ID after controller extraction.
        Sanctum::actingAs($resident);
        $new = $this->postJson('/api/v1/resident/emergency-requests', [
            'resident_home_id'=>$membership->id,'emergency_type_id'=>EmergencyType::value('id'),
            'contact_phone'=>'+966500000020','location_details'=>'Building floor 1',
            'description'=>'Urgent water leak in the main building.','confirm_emergency'=>true,
        ])->assertCreated()->json('data.id');
        $this->deleteJson('/api/v1/resident/emergency-requests/'.$new)->assertOk();
    }
}
