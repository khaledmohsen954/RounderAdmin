<?php

namespace App\Modules\Bookings\Services;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingMedia;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * خدمة مساعدة لإدارة تخزين وسائط الحجوزات وربطها بصاحب العملية.
 */
class BookingMediaStorage
{
    /**
     * حفظ ملف وسائط للحجز في القرص العام وإنشاء سجل booking_media.
     *
     * @param Booking $booking
     * @param UploadedFile $file
     * @param User|Admin|null $actor
     * @param string $kind
     * @return BookingMedia
     */
    public function store(Booking $booking, UploadedFile $file, User|Admin|null $actor, string $kind = 'photo'): BookingMedia
    {
        $path = $file->store('bookings', 'public');
        if (!$path) {
            throw new RuntimeException('Unable to store booking media file.');
        }

        return $booking->bookingMedia()->create([
            'uploaded_by' => $actor instanceof User ? $actor->id : null,
            'admin_uploaded_by' => $actor instanceof Admin ? $actor->id : null,
            'kind' => $kind,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getClientMimeType() ?: ($file->getMimeType() ?: 'image/jpeg'),
            'size_bytes' => $file->getSize() ?: 0,
        ]);
    }

    /**
     * حذف الملف من التخزين وسجله من قاعدة البيانات.
     *
     * @param BookingMedia $media
     * @return void
     */
    public function delete(BookingMedia $media): void
    {
        if ($media->path) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
        }
        $media->delete();
    }
}
