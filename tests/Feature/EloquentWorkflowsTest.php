<?php

namespace Tests\Feature;

use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServicePackage;
use App\Modules\Catalog\Models\PackageBillingCycle;
use App\Modules\Catalog\Models\ResidentSubscription;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Regression coverage for ownership, snapshots and model writes after the ORM refactor. */
class EloquentWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    private User $resident;
    private ResidentHome $membership;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->resident = User::create([
            'name' => 'Resident test', 'email' => 'orm@example.test',
            'password' => 'StrongPassword123!', 'locale' => 'en',
            'role_id' => Role::where('code', 'resident')->value('id'),
        ]);
        $home = Home::create([
            'city_id' => City::value('id'), 'name' => 'ORM home',
            'address' => 'Test address', 'property_code' => 'ORM-001',
        ]);
        $this->membership = $this->resident->memberships()->create([
            'home_id' => $home->id, 'unit_label' => '12', 'is_primary' => true,
        ]);
        Sanctum::actingAs($this->resident);
    }

    public function test_account_hash_and_existing_home_relationship_remain_compatible(): void
    {
        $this->assertTrue(Hash::check('StrongPassword123!', $this->resident->password));
        $this->assertArrayNotHasKey('password', $this->resident->toArray());
        $this->assertEquals($this->membership->home_id, $this->resident->residentHomes()->first()->id);
        $this->getJson('/api/v1/resident/dashboard')->assertOk()->assertJsonPath('data.has_home', true);
    }

    public function test_subscription_copies_price_and_quantities_and_blocks_duplicates(): void
    {
        $service = Service::firstOrFail();
        $package = ServicePackage::create([
            'billing_cycle_id' => PackageBillingCycle::where('code', 'monthly')->value('id'),
            'code' => 'orm-package', 'price' => 100, 'currency_code' => 'SAR',
            'duration_months' => 1, 'is_active' => true,
        ]);
        $package->translations()->create(['locale' => 'en', 'name' => 'Test package']);
        $package->includedServices()->create([
            'service_id' => $service->id, 'included_quantity' => 3, 'is_unlimited' => false,
        ]);
        $this->getJson('/api/v1/resident/packages', ['Accept-Language' => 'en'])
            ->assertOk()->assertJsonPath('data.0.name', 'Test package')
            ->assertJsonPath('data.0.services.0.included_quantity', 3);
        $payload = ['resident_home_id' => $this->membership->id, 'service_package_id' => $package->id];
        $this->postJson('/api/v1/resident/subscriptions', $payload)
            ->assertCreated()->assertJsonPath('payment_required', true);
        $this->postJson('/api/v1/resident/subscriptions', $payload)->assertConflict();
        $subscription = ResidentSubscription::with('includedServices')->firstOrFail();
        $package->update(['price' => 500]);
        $package->includedServices()->update(['included_quantity' => 1]);
        $this->assertEquals('100.00', $subscription->fresh()->price_snapshot);
        $this->assertSame(3, $subscription->includedServices->first()->included_quantity);
        $this->deleteJson('/api/v1/resident/subscriptions/'.$subscription->id)->assertOk();
        $this->assertSame('cancelled', $subscription->fresh()->status->code);
    }

    public function test_community_reactions_update_one_record_and_other_homes_are_hidden(): void
    {
        $post = $this->postJson('/api/v1/resident/community/posts', [
            'body' => 'Maintenance notice for our residents.',
        ])->assertCreated()->json('data.id');
        $this->putJson("/api/v1/resident/community/posts/$post/reaction", ['reaction_code' => 'like'])->assertOk();
        $this->putJson("/api/v1/resident/community/posts/$post/reaction", ['reaction_code' => 'support'])->assertOk();
        $this->assertSame(1, CommunityReaction::where('community_post_id', $post)->count());
        $this->assertSame('support', CommunityReaction::first()->reaction_code);
        $this->postJson("/api/v1/resident/community/posts/$post/comments", ['body' => 'Thank you'])->assertCreated();
        $this->assertSame(1, CommunityPost::findOrFail($post)->communityComments()->count());

        $other = User::create(['name' => 'Other', 'email' => 'other-orm@example.test',
            'password' => 'StrongPassword123!', 'locale' => 'en', 'role_id' => $this->resident->role_id]);
        Sanctum::actingAs($other);
        $this->putJson("/api/v1/resident/community/posts/$post/reaction", ['reaction_code' => 'like'])->assertForbidden();
    }

    public function test_booking_creates_audit_rows_and_preserves_recorded_price(): void
    {
        $service = Service::firstOrFail();
        $price = $service->base_price;
        $id = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->membership->home_id, 'service_id' => $service->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertCreated()->json('data.id');
        $service->update(['base_price' => 999]);
        $this->assertEquals($price, Booking::findOrFail($id)->total_amount);
        $this->assertSame(1, BookingStatusHistory::where('booking_id', $id)->count());
        $this->postJson("/api/v1/resident/bookings/$id/cancel", ['reason' => 'No longer needed'])->assertOk();
        $this->assertSame(2, Booking::findOrFail($id)->statusHistory()->count());
    }
}
