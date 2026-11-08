<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\AdminRequest;
use App\Modules\Catalog\Models\SubService;
use Illuminate\Validation\Rule;

/** التحقق من مدخلات OrderCreationController::storeService قبل تشغيل الخدمة. */
class AdminOrderCreationStoreServiceRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'resident_home_id' => ['required', 'integer', 'exists:resident_homes,id'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'sub_service_id' => ['nullable', 'integer', Rule::exists('sub_services', 'id')->where('is_active', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'images' => ['sometimes', 'nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty() || !$this->filled('service_id')) {
                return;
            }

            $hasSubServices = SubService::query()
                ->where('service_id', $this->input('service_id'))
                ->where('is_active', true)
                ->exists();
            if ($hasSubServices && !$this->filled('sub_service_id')) {
                $validator->errors()->add('sub_service_id', __('Choose a sub service.'));
                return;
            }

            if ($this->filled('sub_service_id') && !SubService::query()
                ->whereKey($this->input('sub_service_id'))
                ->where('service_id', $this->input('service_id'))
                ->where('is_active', true)
                ->exists()) {
                $validator->errors()->add('sub_service_id', __('Choose a sub service for the selected service.'));
            }
        });
    }
}
