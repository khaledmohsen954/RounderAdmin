<?php

namespace App\Modules\Support\Models;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use App\Modules\Support\Enums\SupportCategory;
use App\Modules\Support\Enums\SupportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'reference',
        'user_id',
        'category',
        'subject',
        'message',
        'attachment_path',
        'status',
        'admin_reply',
        'replied_at',
        'replied_by',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'replied_by' => 'integer',
            'replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repliedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'replied_by');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
    }

    public function getCategoryLabelAttribute(): string
    {
        return SupportCategory::tryFrom($this->category)?->label() ?? $this->category;
    }

    public function getCategoryNameAttribute(): string
    {
        return SupportCategory::tryFrom($this->category)?->label() ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return SupportStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusNameAttribute(): string
    {
        return SupportStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return SupportStatus::tryFrom($this->status)?->badgeClass() ?? 'bg-light text-dark';
    }
}

