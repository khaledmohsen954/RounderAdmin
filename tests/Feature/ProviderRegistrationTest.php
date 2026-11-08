<?php

namespace Tests\Feature;

use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProviderRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_external_registration_waits_for_admin_approval_and_contract_acceptance(): void
    {
        $this->seed();
        $this->withHeader('Sec-Fetch-Site', 'same-origin');
        Storage::fake('local');
        $service = Service::query()->where('is_active', true)->firstOrFail();

        $this->get('/provider/register')->assertOk()
            ->assertSee('class="site-header sticky-top"', false)
            ->assertSee('class="site-footer"', false)
            ->assertSee('name="service_ids[]"', false)
            ->assertSee('name="password"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="commercial_registration_document"', false)
            ->assertSee('name="legal_representative_id_document"', false);

        $this->post('/provider/register', [
            'company_name' => 'شركة تجريبية',
            'trade_name' => 'مزود تجريبي',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 5,
            'service_ids' => [$service->id],
            'phone' => '0551234567',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'commercial_registration_document' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('provider.login'));

        $profile = ProviderProfile::query()->where('company_name', 'شركة تجريبية')->firstOrFail();
        $this->assertNotNull($profile->commercial_registration_document);
        Storage::disk('local')->assertExists($profile->commercial_registration_document);
        $contract = $profile->contracts()->firstOrFail();
        $this->assertFalse($profile->is_approved);
        $this->assertSame(ContractStatusCode::Draft->value, $contract->status->code);
        $this->assertDatabaseHas('admin_alerts', ['type' => 'provider', 'subject_id' => $profile->id]);

        $this->post('/provider/login', [
            'login' => '0551234567',
            'password' => 'StrongPassword123!',
        ])->assertRedirect(route('provider.pending'));
        $this->get('/provider/pending')->assertOk();
        $this->get('/provider/dashboard')->assertForbidden();

        $admin = Admin::query()->create([
            'role_id' => Role::query()->where('code', 'admin')->value('id'),
            'is_super_admin' => true,
            'is_active' => true,
            'name' => 'Provider reviewer',
            'email' => 'provider-reviewer@example.test',
            'password' => 'StrongPassword123!',
            'locale' => 'ar',
        ]);
        $approval = [
            'name' => $profile->user->name,
            'phone' => $profile->user->phone,
            'email' => null,
            'locale' => 'ar',
            'company_name' => $profile->company_name,
            'trade_name' => $profile->trade_name,
            'provider_type' => $profile->provider_type,
            'years_of_experience' => $profile->years_of_experience,
            'commercial_registration' => '1010101010',
            'legal_representative' => 'الممثل القانوني',
            'legal_representative_id' => '1000000000',
            'is_approved' => 1,
            'contract_id' => $contract->id,
            'service_commission_percent' => 10,
            'emergency_commission_percent' => 15,
            'starts_on' => today()->toDateString(),
            'ends_on' => null,
            'terms' => '<p>شروط العقد</p>',
        ];
        $this->actingAs($admin, 'web')->put('/admin/providers/' . $profile->id, $approval)
            ->assertSessionHasErrors([
                'company_seal',
                'legal_representative_id_document',
                'signed_annex_a',
                'municipal_license',
            ]);
        $this->assertFalse($profile->fresh()->is_approved);

        $approval += [
            'company_seal' => UploadedFile::fake()->create('seal.pdf', 100, 'application/pdf'),
            'commercial_registration_document' => UploadedFile::fake()->create('registration.pdf', 100, 'application/pdf'),
            'legal_representative_id_document' => UploadedFile::fake()->create('representative-id.pdf', 100, 'application/pdf'),
            'signed_annex_a' => UploadedFile::fake()->create('annex-a.pdf', 100, 'application/pdf'),
            'municipal_license' => UploadedFile::fake()->create('municipal-license.pdf', 100, 'application/pdf'),
        ];
        $this->put('/admin/providers/' . $profile->id, $approval)
            ->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();

        $profile->refresh();
        $this->assertTrue($profile->is_approved);
        $this->assertNotNull($profile->company_seal);
        Storage::disk('local')->assertExists($profile->company_seal);
        $this->get('/admin/providers/' . $profile->id . '/documents/company_seal')->assertOk();

        $this->get('/provider/contract')->assertOk()->assertSee('شروط العقد');
        $this->post('/provider/contract/accept', ['accept_contract' => '1'])
            ->assertRedirect(route('provider.dashboard'));
        $this->assertNotNull($profile->fresh()->contract_accepted_at);
        $this->get('/provider/dashboard')->assertOk();
    }
}
