<?php

namespace App\Modules\Bookings\Services;

use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingRating;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiBookingRatingService
{
    /**
     * ترجمة الأوصاف السريعة (Tags) بحسب لغة الطلب.
     * @param string $tag
     * @param string $locale
     * @return string
     */
    public function formatTag(string $tag, string $locale): string
    {
        $map = [
            'حسن التعامل' => ['ar' => 'حسن التعامل', 'en' => 'Good Communication', 'ur' => 'اچھا رویہ'],
            'good communication' => ['ar' => 'حسن التعامل', 'en' => 'Good Communication', 'ur' => 'اچھا رویہ'],
            'دقة الموعد' => ['ar' => 'دقة الموعد', 'en' => 'Punctuality', 'ur' => 'وقت کی پابندی'],
            'punctuality' => ['ar' => 'دقة الموعد', 'en' => 'Punctuality', 'ur' => 'وقت کی پابندی'],
            'جودة التنفيذ' => ['ar' => 'جودة التنفيذ', 'en' => 'Quality of Work', 'ur' => 'کام کا معیار'],
            'quality of work' => ['ar' => 'جودة التنفيذ', 'en' => 'Quality of Work', 'ur' => 'کام کا معیار'],
        ];

        $key = mb_strtolower(trim($tag));
        return $map[$key][$locale] ?? $tag;
    }

    /**
     * ترجمة مصفوفة الأوصاف السريعة (Tags).
     * @param ?array $tags
     * @param string $locale
     * @return array<string>
     */
    public function formatTags(?array $tags, string $locale): array
    {
        if (empty($tags)) {
            return [];
        }
        return array_values(array_map(fn($t) => $this->formatTag($t, $locale), $tags));
    }

    /**
     * جلب رسالة النجاح بحسب لغة الطلب (عربي / إنجليزي / أردو).
     * @param string $locale
     * @return string
     */
    public function getRatingSuccessMessage(string $locale): string
    {
        $messages = [
            'ar' => 'تم استلام تقييمك بنجاح، شكراً لمشاركتنا رأيك!',
            'en' => 'Your rating has been submitted successfully. Thank you for your feedback!',
            'ur' => 'آپ کی درجہ بندی کامیابی کے ساتھ موصول ہو گئی ہے، آپ کے تاثرات کا شکریہ!',
        ];

        return $messages[$locale] ?? $messages['ar'];
    }

    /**
     * جلب تفاصيل شاشة التقييم للطلب (اسم المزود، الخدمة، والتقييم السابق إن وُجد).
     * @param int $bookingId
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function show(int $bookingId, ?User $actor): array
    {
        $booking = Booking::with(['service.translations', 'subService.translations', 'serviceProvider', 'rating', 'status'])
            ->where('id', $bookingId)
            ->where('resident_id', $actor->id)
            ->first();

        DomainViolation::unless($booking, FailureReason::NotFound, __('Booking not found.'));

        $locale = app()->getLocale();
        $providerName = $booking->serviceProvider?->company_name ?? ($locale === 'en' ? 'Care Maintenance' : 'عناية للصيانة');
        $serviceName = $booking->service?->translations->firstWhere('locale', $locale)?->name ?? $booking->service?->code;
        $subServiceName = $booking->subService?->translations->firstWhere('locale', $locale)?->name ?? $booking->subService?->code;
        $existingRating = $booking->rating;

        return [
            'data' => [
                'booking_id' => $booking->id,
                'provider_name' => $providerName,
                'service_name' => $serviceName,
                'sub_service_name' => $subServiceName,
                'is_rated' => $existingRating !== null,
                'can_rate' => $existingRating === null,
                'rating' => $existingRating ? [
                    'id' => $existingRating->id,
                    'score' => $existingRating->score,
                    'comment' => $existingRating->comment,
                    'tags' => $this->formatTags($existingRating->tags ?? [], $locale),
                    'created_at' => $existingRating->created_at?->toIso8601String(),
                ] : null,
            ],
        ];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rateBooking(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performRateBooking($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingRatingRateBooking, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performRateBooking(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        $booking = Booking::with('serviceProvider')->where('id', $bookingId)
            ->where('resident_id', $actor->id)->first();
        DomainViolation::unless($booking, FailureReason::NotFound, __('Booking not found.'));

        $locale = app()->getLocale();
        $message = $this->getRatingSuccessMessage($locale);

        $existing = BookingRating::where('booking_id', $bookingId)->first();
        if ($existing) {
            $existing->update([
                'score' => $data['score'],
                'comment' => $data['comment'] ?? null,
                'tags' => $data['tags'] ?? null,
            ]);
            $rating = $existing->fresh();
        } else {
            $rating = BookingRating::query()->create([
                'booking_id' => $bookingId,
                'resident_id' => $actor->id,
                'score' => $data['score'],
                'comment' => $data['comment'] ?? null,
                'tags' => $data['tags'] ?? null,
            ]);
        }

        return [
            'data' => [
                'id' => $rating->id,
                'booking_id' => $rating->booking_id,
                'resident_id' => $rating->resident_id,
                'score' => $rating->score,
                'comment' => $rating->comment,
                'tags' => $this->formatTags($rating->tags ?? [], $locale),
                'provider_name' => $booking->serviceProvider?->company_name ?? ($locale === 'en' ? 'Care Maintenance' : 'عناية للصيانة'),
                'created_at' => $rating->created_at?->toIso8601String(),
                'updated_at' => $rating->updated_at?->toIso8601String(),
            ],
            'message' => $message,
        ];
    }
}
