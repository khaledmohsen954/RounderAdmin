<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\SubService;
use Carbon\Carbon;

/** التحقق من مدخلات BookingController::store قبل تشغيل الخدمة. */
class ApiBookingStoreRequest extends ResidentRequest
{
    /**
     * تحويل تاريخ الموعد ISO 8601 وتجهيز معرف مزود الخدمة.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        $scheduledAt = $this->input('scheduled_at');
        if ($scheduledAt) {
            try {
                $merge['scheduled_at'] = Carbon::parse($scheduledAt)->format('Y-m-d H:i:s');
            } catch (\Throwable) {}
        }

        // إذا أرسل العميل resident_home_id ولم يرسل home_id
        if (!$this->has('home_id') && $this->has('resident_home_id')) {
            $homeId = ResidentHome::where('id', $this->input('resident_home_id'))
                ->where('user_id', $this->user()->id)
                ->value('home_id');
            if ($homeId) {
                $merge['home_id'] = $homeId;
            }
        }

        // توحيد معرف مزود الخدمة
        $rawProvider = $this->input('service_provider_id') ?? $this->input('provider_id');
        if ($rawProvider !== null && $rawProvider !== '') {
            $providerId = (int) $rawProvider;
            if (!ProviderProfile::where('id', $providerId)->exists()) {
                $profileId = ProviderProfile::where('user_id', $providerId)->value('id');
                if ($profileId) {
                    $merge['service_provider_id'] = $profileId;
                }
            } else {
                $merge['service_provider_id'] = $providerId;
            }
        }

        // توحيد معرف الخدمة الفرعية sub_service_id / sub_services_id
        $rawSubService = $this->input('sub_service_id') ?? $this->input('sub_services_id');
        if ($rawSubService !== null && $rawSubService !== '') {
            $merge['sub_service_id'] = (int) $rawSubService;
            // إذا أرسل العميل الخدمة الفرعية دون service_id، يتم استنتاج الخدمة الأم تلقائياً
            if (!$this->has('service_id')) {
                $serviceId = SubService::where('id', $rawSubService)->value('service_id');
                if ($serviceId) {
                    $merge['service_id'] = $serviceId;
                }
            }
        }

        // توحيد معرف التسعير price_id / sub_service_price_id
        $rawPriceId = $this->input('price_id') ?? $this->input('sub_service_price_id');
        if ($rawPriceId !== null && $rawPriceId !== '') {
            $merge['sub_service_price_id'] = (int) $rawPriceId;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'home_id' => ['required', 'integer', 'exists:homes,id',
                Rule::exists('resident_homes', 'home_id')->where('user_id', $this->user()->id)],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'sub_service_id' => ['sometimes', 'nullable', 'integer', Rule::exists('sub_services', 'id')->where('is_active', true)],
            'sub_services_id' => ['sometimes', 'nullable', 'integer', Rule::exists('sub_services', 'id')->where('is_active', true)],
            'price_id' => ['sometimes', 'nullable', 'integer', Rule::exists('sub_service_prices', 'id')->where('is_active', true)->where('approval_status', 'approved')],
            'sub_service_price_id' => ['sometimes', 'nullable', 'integer', Rule::exists('sub_service_prices', 'id')->where('is_active', true)->where('approval_status', 'approved')],
            'service_provider_id' => ['sometimes', 'nullable', 'integer', Rule::exists('provider_profiles', 'id')->where('is_approved', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'images' => ['sometimes', 'nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    /**
     * رسائل التحقق المخصصة.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_provider_id.exists' => 'مزود الخدمة المختار غير صالح أو غير معتمد.',
            'service_provider_id.integer' => 'معرف مزود الخدمة غير صالح.',
            'sub_service_id.exists' => 'الخدمة الفرعية المختارة غير صالحة أو غير نشطة.',
            'sub_services_id.exists' => 'الخدمة الفرعية المختارة غير صالحة أو غير نشطة.',
            'images.array' => 'الصور يجب أن تكون في صيغة قائمة.',
            'images.max' => 'الحد الأقصى لعدد الصور المرفقة هو 5 صور.',
            'images.*.image' => 'يجب أن يكون الملف المرفق صورة صالحة.',
            'images.*.mimes' => 'أنواع الصور المقبولة هي: jpeg, png, jpg, webp.',
            'images.*.max' => 'حجم الصورة لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
