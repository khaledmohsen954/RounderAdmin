<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportTicketApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $resident;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $residentRole = Role::firstOrCreate(['code' => 'resident']);
        $this->resident = User::create([
            'role_id' => $residentRole->id,
            'name' => 'ساكن تجريبي',
            'email' => 'resident_test_' . uniqid() . '@example.com',
            'phone' => '05' . mt_rand(10000000, 99999999),
            'password' => 'Secret123!',
            'locale' => 'ar',
            'is_active' => true,
        ]);

        $adminRole = Role::firstOrCreate(['code' => 'admin']);
        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin_support@rounder.sa'],
            [
                'name' => 'مدير النظام',
                'password' => 'Secret123!',
                'is_active' => true,
                'is_super_admin' => true,
            ]
        );
    }

    public function test_can_get_support_info(): void
    {
        $response = $this->getJson('/api/v1/support/info');
        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.phone', '920000488')
            ->assertJsonPath('data.email', 'support@rounder.sa')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'phone',
                    'email',
                    'working_hours',
                    'chat_available',
                ],
            ]);
    }

    public function test_resident_can_create_ticket_with_attachment(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->resident);

        $file = UploadedFile::fake()->image('problem.jpg', 600, 400);

        $response = $this->postJson('/api/v1/resident/support/tickets', [
            'category' => 'order_issue',
            'subject' => 'تأخر في وصول الفني',
            'message' => 'الفني لم يحضر في الموعد المحدد للطلب وأرجو المساعدة.',
            'attachment' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.category', 'order_issue')
            ->assertJsonPath('data.subject', 'تأخر في وصول الفني')
            ->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->resident->id,
            'category' => 'order_issue',
            'subject' => 'تأخر في وصول الفني',
            'status' => 'open',
        ]);
    }

    public function test_resident_can_list_and_view_own_tickets(): void
    {
        Sanctum::actingAs($this->resident);

        $ticket = SupportTicket::create([
            'reference' => 'TKT-TEST01',
            'user_id' => $this->resident->id,
            'category' => 'payment',
            'subject' => 'استفسار عن الفاتورة',
            'message' => 'تم خصم المبلغ مرتين.',
            'status' => 'open',
        ]);

        $listResponse = $this->getJson('/api/v1/resident/support/tickets');
        $listResponse->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.reference', 'TKT-TEST01');

        $showResponse = $this->getJson('/api/v1/resident/support/tickets/' . $ticket->id);
        $showResponse->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.reference', 'TKT-TEST01')
            ->assertJsonPath('data.subject', 'استفسار عن الفاتورة');
    }

    public function test_admin_can_view_and_update_ticket(): void
    {
        $ticket = SupportTicket::create([
            'reference' => 'TKT-ADM01',
            'user_id' => $this->resident->id,
            'category' => 'housing',
            'subject' => 'عطل في مدخل المبنى',
            'message' => 'البوابة الرئيسية لا تفتح بالكارت.',
            'status' => 'open',
        ]);

        $this->actingAs($this->admin);

        $indexResponse = $this->get(route('admin.support-tickets.index'));
        $indexResponse->assertStatus(200)
            ->assertSee('TKT-ADM01')
            ->assertSee('عطل في مدخل المبنى');

        $showResponse = $this->get(route('admin.support-tickets.show', $ticket->id));
        $showResponse->assertStatus(200)
            ->assertSee('TKT-ADM01')
            ->assertSee('البوابة الرئيسية لا تفتح بالكارت.');

        $updateResponse = $this->put(route('admin.support-tickets.update', $ticket->id), [
            'status' => 'resolved',
            'admin_reply' => 'تم إعادة برمجة القفل الذكي للبوابة وتعمل الآن بنجاح.',
        ]);

        $updateResponse->assertRedirect(route('admin.support-tickets.show', $ticket->id));

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'resolved',
            'admin_reply' => 'تم إعادة برمجة القفل الذكي للبوابة وتعمل الآن بنجاح.',
            'replied_by' => $this->admin->id,
        ]);
    }
}
