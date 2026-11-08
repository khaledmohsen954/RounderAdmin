<?php

namespace App\Modules\Catalog\Requests;

use App\Modules\Catalog\Models\SubServicePrice;
use Illuminate\Foundation\Http\FormRequest;

/**
 * يتحقق من جميع عمليات أسعار الخدمات الفرعية في لوحة الإدارة (عرض، إنشاء متعدد، تعديل).
 */
class AdminPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * تجهيز ومعالجة البيانات قبل التحقق منها؛ لضمان عدم التأثر بحد max_input_vars في PHP.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('city_ids_json') && !empty($this->input('city_ids_json'))) {
            $decoded = json_decode((string) $this->input('city_ids_json'), true);
            if (is_array($decoded)) {
                $this->merge(['city_ids' => $decoded]);
            }
        } elseif (is_string($this->input('city_ids'))) {
            $decoded = json_decode($this->input('city_ids'), true);
            if (is_array($decoded)) {
                $this->merge(['city_ids' => $decoded]);
            }
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [
                'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
                'service_id' => ['nullable', 'integer', 'exists:services,id'],
                'sub_service_id' => ['nullable', 'integer', 'exists:sub_services,id'],
                'region_id' => ['nullable', 'integer', 'exists:regions,id'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
                'status' => ['nullable', 'string', 'in:all,active,inactive'],
                'approval_status' => ['nullable', 'string', 'in:all,pending,approved,rejected'],
                'search' => ['nullable', 'string', 'max:100'],
            ];
        }

        if ($this->isMethod('POST')) {
            return [
                'provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
                'service_id' => ['required', 'integer', 'exists:services,id'],
                'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
                'region_id' => ['required', 'integer', 'exists:regions,id'],
                'city_ids' => ['required', 'array', 'min:1'],
                'city_ids.*' => ['required', 'integer', 'exists:cities,id'],
                'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
                'is_active' => ['nullable', 'boolean'],
            ];
        }

        // PUT / PATCH
        return [
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['nullable', 'boolean'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['required', 'integer', 'exists:cities,id'],
        ];
    }

    /**
     * التحقق من عدم وجود تعارض في التسعير لنفس المزود والخدمة والمدن المحددة.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (!$this->isMethod('POST') && !$this->isMethod('PUT') && !$this->isMethod('PATCH')) {
                return;
            }

            $cityIds = $this->input('city_ids', []);
            if (!is_array($cityIds) || empty($cityIds)) {
                return;
            }

            if ($this->isMethod('POST')) {
                $providerId = (int) $this->input('provider_profile_id');
                $subServiceId = (int) $this->input('sub_service_id');
                $priceIdToExclude = null;
            } else {
                $priceIdToExclude = (int) $this->route('priceId');
                $currentPrice = SubServicePrice::find($priceIdToExclude);
                if (!$currentPrice) {
                    return;
                }
                $providerId = (int) $currentPrice->provider_profile_id;
                $subServiceId = (int) $currentPrice->sub_service_id;
            }

            if (!$providerId || !$subServiceId) {
                return;
            }

            $regionId = (int) $this->input('region_id');
            if ($regionId && \App\Modules\Properties\Models\City::query()
                ->whereIn('id', $cityIds)
                ->where(fn ($query) => $query->whereNull('region_id')->orWhere('region_id', '!=', $regionId))
                ->exists()) {
                $validator->errors()->add('city_ids', __('Each selected city must belong to the selected region.'));
                return;
            }

            $conflictingPrice = SubServicePrice::query()
                ->when($priceIdToExclude, fn ($q) => $q->where('id', '!=', $priceIdToExclude))
                ->where('provider_profile_id', $providerId)
                ->where('sub_service_id', $subServiceId)
                ->whereHas('cities', fn ($q) => $q->whereIn('cities.id', $cityIds))
                ->with(['region.translations'])
                ->first();

            if ($conflictingPrice) {
                $regionName = $conflictingPrice->region?->translations->firstWhere('locale', app()->getLocale())?->name
                    ?? $conflictingPrice->region?->translations->firstWhere('locale', 'ar')?->name
                    ?? $conflictingPrice->region?->code
                    ?? '';

                $validator->errors()->add(
                    'city_ids',
                    __('A pricing already exists for this provider and sub-service covering the selected cities (Price #:id:region). Please edit the existing price instead of creating a conflict.', [
                        'id' => $conflictingPrice->id,
                        'region' => $regionName ? " - {$regionName}" : '',
                    ])
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'provider_profile_id' => __('Provider'),
            'service_id' => __('Service'),
            'sub_service_id' => __('Sub service'),
            'region_id' => __('Region'),
            'city_ids' => __('Cities'),
            'price' => __('Price'),
            'is_active' => __('Status'),
        ];
    }
}
