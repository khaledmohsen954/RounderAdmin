<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Services\ProviderAccountService;
use App\Modules\Identity\Events\IdentityChanged;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\{DB, Event};
use Tests\TestCase;
/** Verify that subscribers never see changes rolled back by an outer transaction. */
class DomainEventsTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * @return void
     */
    public function test_events_wait_for_commit_and_are_discarded_on_rollback(): void
    {
        Role::create(['code'=>'provider']);
        Event::fake([IdentityChanged::class]);
        $service = app(ProviderAccountService::class);
        $data = ['name'=>'Provider','email'=>'event@example.test','phone'=>'+966500000021',
            'locale'=>'en','password'=>'StrongPassword123!'];
        DB::beginTransaction();
        $service->create($data, null);
        Event::assertNotDispatched(IdentityChanged::class);
        DB::rollBack();
        Event::assertNotDispatched(IdentityChanged::class);
        $this->assertDatabaseMissing('users',['email'=>$data['email']]);
        $service->create($data, null);
        Event::assertDispatchedTimes(IdentityChanged::class, 1);
    }
}
