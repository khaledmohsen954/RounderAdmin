<?php

namespace App\Modules\Support\Services;

use App\Modules\Identity\Models\Admin;
use App\Modules\Support\Enums\Operation;
use App\Modules\Support\Events\SupportTicketsChanged;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminSupportTicketService
{
    /**
     * جلب قائمة تذاكر الدعم مع الفلترة والبحث للوحة الإدارة.
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = SupportTicket::with(['user', 'repliedByAdmin'])->latest('id');

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['q'])) {
            $search = '%' . trim($filters['q']) . '%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference', 'like', $search)
                    ->orWhere('subject', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', $search)
                            ->orWhere('phone', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * إحصائيات سريعة لحالات التذاكر.
     */
    public function counts(): array
    {
        return [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];
    }

    /**
     * جلب تفاصيل تذكرة محددة.
     */
    public function find(int $ticketId): SupportTicket
    {
        return SupportTicket::with(['user', 'repliedByAdmin'])->findOrFail($ticketId);
    }

    /**
     * تحديث حالة التذكرة وإضافة رد الإدارة.
     */
    public function update(int $ticketId, array $validated, Admin $admin): SupportTicket
    {
        return DB::transaction(function () use ($ticketId, $validated, $admin) {
            $ticket = SupportTicket::lockForUpdate()->findOrFail($ticketId);

            $updates = [
                'status' => $validated['status'],
            ];

            if (array_key_exists('admin_reply', $validated) && $validated['admin_reply'] !== null) {
                $updates['admin_reply'] = $validated['admin_reply'];
                $updates['replied_at'] = now();
                $updates['replied_by'] = $admin->id;
            }

            $ticket->update($updates);

            DB::afterCommit(fn () => event(new SupportTicketsChanged(Operation::TicketUpdated, $ticket->id, $admin->id)));

            return $ticket;
        });
    }
}

