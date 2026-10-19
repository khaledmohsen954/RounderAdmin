<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\{User, Role};
use App\Modules\Providers\Models\ProviderProfile;
use App\Services\RichTextSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** تغطية رحلة الإضافة والتعديل وعزل بيانات العقود على MySQL. */
class ProviderFormsTest extends TestCase
{
    use RefreshDatabase;

    /** @return User */
    private function admin(): User
    {
        $this->seed();
        return User::create(['name'=>'Admin', 'email'=>'provider-admin@example.test',
            'password'=>'StrongPassword123!', 'locale'=>'ar',
            'role_id'=>Role::where('code','admin')->value('id')]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['name'=>'Contact person','email'=>'company@example.test','phone'=>'+966511112222',
            'locale'=>'en','password'=>'StrongPassword123!','password_confirmation'=>'StrongPassword123!',
            'company_name'=>'Rounder partner','is_approved'=>1,'service_commission_percent'=>'10.00',
            'emergency_commission_percent'=>'15.00','starts_on'=>'2026-10-01','ends_on'=>null,
            'terms'=>'<h2>Terms</h2><p><strong>Approved</strong></p><script>alert(1)</script><img src=x onerror=alert(1)>'];
    }

    /** @return void */
    public function test_listing_add_and_edit_are_separate_and_save_atomically(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/providers')->assertOk()->assertSee(route('admin.providers.create'))
            ->assertDontSee('name="company_name"', false);
        foreach (['ar','en'] as $locale) {
            $this->withSession(['locale'=>$locale])->get('/admin/providers/create')->assertOk()
                ->assertSee('contenteditable="true"', false)->assertSee('name="terms"', false);
        }
        $data = $this->payload();
        $this->post('/admin/providers', $data)->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();
        $provider = ProviderProfile::where('company_name',$data['company_name'])->firstOrFail();
        $contract = $provider->contracts()->firstOrFail();
        $this->assertSame('<h2>Terms</h2><p><strong>Approved</strong></p>', $contract->terms);
        $this->assertTrue(Hash::check($data['password'], $provider->user->password));
        $this->get('/admin/providers')->assertSee(route('admin.providers.edit',$provider->id));
        $this->get('/admin/providers/'.$provider->id.'/edit')->assertOk()->assertSee($data['company_name']);
        $data['company_name'] = 'Updated company';
        $data['name'] = 'Updated contact';
        $data['is_approved'] = 0;
        // هذه الحقول ليست ضمن قائمة التعديل المسموح بها.
        $data['password'] = 'InjectedPassword123!';
        $data['service_commission_percent'] = 99;
        $this->put('/admin/providers/'.$provider->id,$data)->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('provider_profiles',['id'=>$provider->id,'company_name'=>'Updated company','is_approved'=>0]);
        $this->assertDatabaseHas('users',['id'=>$provider->user_id,'name'=>'Updated contact']);
        $this->assertTrue(Hash::check('StrongPassword123!',$provider->user->fresh()->password));
        $this->assertEquals(10, $contract->fresh()->service_commission_percent);
        $this->assertSame(1,$provider->contracts()->count());
        $this->get('/admin/providers/999999/edit')->assertNotFound();
    }

    /** @return void */
    public function test_validation_and_permissions_prevent_partial_or_unauthorized_changes(): void
    {
        $admin = $this->admin();
        $data = $this->payload();
        $this->actingAs($admin)->post('/admin/providers',$data)->assertSessionHasNoErrors();
        $provider = ProviderProfile::where('company_name',$data['company_name'])->firstOrFail();
        $data['email'] = $admin->email;
        $data['company_name'] = 'Must not save';
        $this->put('/admin/providers/'.$provider->id,$data)->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('provider_profiles',['company_name'=>'Must not save']);
        $data['phone'] = 'invalid';
        $this->from('/admin/providers/create')->post('/admin/providers',$data)
            ->assertRedirect('/admin/providers/create')->assertSessionHasErrors(['email','phone']);
        $resident = User::create(['name'=>'Resident','email'=>'forms-resident@example.test',
            'password'=>'StrongPassword123!','locale'=>'ar','role_id'=>Role::where('code','resident')->value('id')]);
        $this->actingAs($resident)->get('/admin/providers/create')->assertForbidden();
        $this->get('/admin/providers/'.$provider->id.'/edit')->assertForbidden();
        $this->put('/admin/providers/'.$provider->id,$this->payload())->assertForbidden();
    }

    /** @return void */
    public function test_contract_html_retains_formatting_without_active_content(): void
    {
        $cleaner = app(RichTextSanitizer::class);
        $this->assertNull($cleaner->clean(null));
        $this->assertSame('<p>شروط <b>العقد</b></p><ul><li>One</li></ul>',
            $cleaner->clean('<p onclick="bad()">شروط <b style="color:red">العقد</b></p><ul><li>One</li></ul>'));
        $html = $cleaner->clean('<svg><script>bad()</script></svg><iframe srcdoc="x"></iframe><a href="javascript:bad()">Text</a><p onmouseover="bad()">Safe</p>');
        $this->assertSame('Text<p>Safe</p>', $html);
        $this->assertSame('Plain terms', $cleaner->clean('Plain terms'));
    }
}
