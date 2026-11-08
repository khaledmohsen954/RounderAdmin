<?php

namespace App\Modules\Support\Services;

use App\Modules\Identity\Models\User;
use App\Modules\Support\Enums\Operation;
use App\Modules\Support\Enums\SupportCategory;
use App\Modules\Support\Enums\SupportStatus;
use App\Modules\Support\Events\SupportTicketsChanged;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportService
{
    /**
     * إرجاع قنوات ومعلومات التواصل المعتمدة والتصنيفات المتاحة.
     */
    public function getSupportInfo(): array
    {
        $settings = \App\Modules\Settings\Models\CompanySetting::current();

        return [
            'phone' => $settings->support_phone ?: '920000488',
            'email' => $settings->support_email ?: 'support@rounder.sa',
            'working_hours' => $settings->support_working_hours ?: 'الأحد إلى الخميس، 10 ص - 8 م',
            'chat_available' => (bool) $settings->support_chat_enabled,
        ];
    }

    /**
     * إنشاء وحفظ تذكرة/رسالة دعم جديدة مع المرفق إن وجد.
     */
    public function createTicket(User $user, array $validated, ?UploadedFile $attachment = null): SupportTicket
    {
        return DB::transaction(function () use ($user, $validated, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('support/attachments', 'public');
            }

            $reference = 'TKT-' . strtoupper(Str::random(8));

            $ticket = SupportTicket::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'attachment_path' => $attachmentPath,
                'status' => SupportStatus::Open->value,
            ]);

            DB::afterCommit(fn () => event(new SupportTicketsChanged(Operation::TicketCreated, $ticket->id, $user->id)));

            return $ticket;
        });
    }

    /**
     * جلب قائمة تذاكر ورسائل الساكن الحالي.
     */
    public function residentTickets(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return SupportTicket::where('user_id', $user->id)
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * جلب تفاصيل تذكرة محددة للمستخدم الحالي.
     */
    public function findForResident(User $user, int $ticketId): ?SupportTicket
    {
        return SupportTicket::where('user_id', $user->id)
            ->where('id', $ticketId)
            ->first();
    }
}

