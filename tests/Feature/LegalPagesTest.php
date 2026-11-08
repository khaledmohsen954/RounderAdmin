<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Settings\Models\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);

        $adminRole = Role::where('code', 'admin')->firstOrFail();
        $this->admin = Admin::create([
            'role_id' => $adminRole->id,
            'name' => 'Super Admin',
            'email' => 'admin_test@rounder.sa',
            'phone' => '0509998877',
            'password' => 'Password123!',
            'is_super_admin' => true,
            'is_active' => true,
            'locale' => 'ar',
        ]);
    }

    public function test_public_can_fetch_terms_page(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/terms');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'terms')
            ->assertJsonPath('data.title', 'الشروط والأحكام');
    }

    public function test_public_can_fetch_privacy_page(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/privacy');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'privacy')
            ->assertJsonPath('data.title', 'سياسة الخصوصية');
    }

    public function test_public_can_fetch_page_in_english(): void
    {
        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/terms');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'terms')
            ->assertJsonPath('data.title', 'Terms & Conditions');
    }

    public function test_resident_aliases_work_identically(): void
    {
        $this->getJson('/api/v1/resident/terms')
            ->assertOk()
            ->assertJsonPath('data.slug', 'terms');

        $this->getJson('/api/v1/resident/privacy')
            ->assertOk()
            ->assertJsonPath('data.slug', 'privacy');
    }

    public function test_inactive_page_returns_not_found(): void
    {
        Page::where('slug', 'terms')->update(['is_active' => false]);

        $this->getJson('/api/v1/terms')
            ->assertNotFound();
    }

    public function test_admin_can_view_pages_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pages');

        $response->assertOk()
            ->assertSee('terms')
            ->assertSee('privacy');
    }

    public function test_admin_can_view_edit_page(): void
    {
        $page = Page::where('slug', 'terms')->firstOrFail();

        $response = $this->actingAs($this->admin)->get('/admin/pages/' . $page->id . '/edit');

        $response->assertOk()
            ->assertSee('الشروط والأحكام');
    }

    public function test_admin_can_update_page_content(): void
    {
        $page = Page::where('slug', 'terms')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('admin.pages.update', $page->id), [
            'title_ar' => 'الشروط والأحكام المحدثة',
            'content_ar' => 'محتوى تجريبي للشروط والأحكام المحدثة.',
            'title_en' => 'Updated Terms & Conditions',
            'content_en' => 'Updated testing content.',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/pages')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'ar',
            'title' => 'الشروط والأحكام المحدثة',
        ]);

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Updated Terms & Conditions',
        ]);

        $apiResponse = $this->withHeader('Accept-Language', 'ar')->getJson('/api/v1/terms');
        $apiResponse->assertOk()
            ->assertJsonPath('data.title', 'الشروط والأحكام المحدثة')
            ->assertJsonPath('data.content', 'محتوى تجريبي للشروط والأحكام المحدثة.');
    }
}

