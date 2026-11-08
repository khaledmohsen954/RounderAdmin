<?php

namespace App\Modules\Properties\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\CityTranslation;
use App\Modules\Properties\Models\ResidentHome;
use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Properties\Events\PropertiesChanged;
use App\Modules\Properties\Enums\Operation;

/** قواعد عمل Properties؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiResidentHomeService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $homes = $actor->residentHomes()->with('city.translations')->get()->map(function (Home $h) {
            $cityName = $h->city?->translations->firstWhere('locale', app()->getLocale())?->name ?? $h->city?->code ?? '';
            $residenceType = $h->pivot->residence_type ?? 'apartment';
            $ownershipType = $h->pivot->ownership_type ?? 'owner';

            return [
                'id' => $h->id,
                'home_id' => $h->id,
                'property_id' => $h->id,
                'resident_home_id' => $h->pivot->id ?? null,
                'name' => $h->name,
                'property_name' => $h->name,
                'custom_name' => $h->pivot->custom_name ?? null,
                'unit_label' => $h->pivot->unit_label,
                'unit_number' => $h->pivot->unit_label,
                'residence_type' => $residenceType,
                'residence_type_label' => match($residenceType) {
                    'apartment' => 'شقة',
                    'villa' => 'فيلا',
                    'floor' => 'دور',
                    default => $residenceType,
                },
                'floor_number' => $h->pivot->floor_number ?? null,
                'area_sqm' => $h->pivot->area_sqm ? (float) $h->pivot->area_sqm : null,
                'ownership_type' => $ownershipType,
                'ownership_type_label' => $ownershipType === 'tenant' ? 'مستأجر' : 'مالك',
                'is_primary' => (bool) $h->pivot->is_primary,
                'address' => $h->address,
                'district' => $h->district,
                'city' => $cityName,
                'property_code' => $h->property_code,
            ];
        });

        return ['data' => $homes];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function link(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLink($input, $actor);
            // Pivot attach bypasses model observers; record the successful resident/property link explicitly.
            app(\App\Modules\Notifications\Services\AdminAlertService::class)->record(
                \App\Modules\Notifications\Enums\AlertType::ResidentLinked, $result['data']['home_id']);
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiResidentHomeLink, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLink(array $input, ?User $actor): mixed
    {
        $data = $input;

  
  $user=$actor;$home=Home::where('property_code',$data['property_code'])->firstOrFail();
  $unit = $home->units()->where('unit_number',$data['unit_label'])->first();
  if ($home->units()->exists() && !$unit) {
   throw \Illuminate\Validation\ValidationException::withMessages(['unit_label'=>__('Select a valid apartment in this property.')]);
  }
  $already=$user->residentHomes()->where('homes.id',$home->id)->wherePivot('unit_label',$data['unit_label'])->exists();
  DomainViolation::rejectIf($already, FailureReason::Conflict, __('Residence is already linked to this account.'));
  DB::transaction(function()use($user,$home,$data,$unit){
   $user->residentHomes()->newPivotStatement()->where('user_id',$user->id)->update(['is_primary'=>false]);
   $user->residentHomes()->attach($home->id,['unit_label'=>$data['unit_label'],'home_unit_id'=>$unit?->id,'is_primary'=>true]);
  });
  return ['message'=>__('Residence linked successfully.'),'data'=>['home_id'=>$home->id,'name'=>$home->name,'unit_label'=>$data['unit_label']]];
    }

    /**
     * حفظ سكن جديد وإضافة وحدة سكنية للساكن (مطابق لتدفق الفيجما).
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function addUnit(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor) {
            $cityId = $input['city_id'] ?? null;
            if (!$cityId && !empty($input['city_name'])) {
                $cityName = trim($input['city_name']);
                $cityId = CityTranslation::where('name', 'like', "%{$cityName}%")->value('city_id');
            }
            if (!$cityId) {
                $firstCity = City::first();
                $cityId = $firstCity?->id ?? 32068;
            }

            $homeName = $input['custom_name'] ?? $input['name'] ?? $input['property_name'] ?? $input['district'] ?? 'سكن جديد';

            $home = Home::create([
                'city_id' => $cityId,
                'name' => $homeName,
                'address' => $input['address'],
                'district' => $input['district'] ?? null,
                'latitude' => $input['latitude'] ?? null,
                'longitude' => $input['longitude'] ?? null,
                'property_code' => 'PR-' . strtoupper(Str::random(8)),
                'floors_count' => !empty($input['floor_number']) && is_numeric($input['floor_number']) ? (int) $input['floor_number'] : null,
            ]);

            $hasExistingHomes = $actor->residentHomes()->exists();
            $shouldBePrimary = !empty($input['is_primary']) || !$hasExistingHomes;

            if ($shouldBePrimary && $hasExistingHomes) {
                $actor->residentHomes()->newPivotStatement()->where('user_id', $actor->id)->update(['is_primary' => false]);
            }

            $residentHome = ResidentHome::create([
                'user_id' => $actor->id,
                'home_id' => $home->id,
                'unit_label' => $input['unit_number'],
                'residence_type' => $input['residence_type'] ?? 'apartment',
                'custom_name' => $input['custom_name'] ?? null,
                'floor_number' => $input['floor_number'] ?? null,
                'area_sqm' => $input['area_sqm'] ?? null,
                'ownership_type' => $input['ownership_type'] ?? 'owner',
                'is_primary' => $shouldBePrimary,
            ]);

            app(\App\Modules\Notifications\Services\AdminAlertService::class)->record(
                \App\Modules\Notifications\Enums\AlertType::ResidentLinked, $home->id);

            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiAddUnit, $actor?->id)));

            $cityName = $home->city?->translations->firstWhere('locale', app()->getLocale())?->name ?? $home->city?->code ?? '';

            return [
                'status' => true,
                'message' => 'تم حفظ السكن وإضافة الوحدة بنجاح.',
                'data' => [
                    'id' => $residentHome->id,
                    'resident_home_id' => $residentHome->id,
                    'home_id' => $home->id,
                    'property_id' => $home->id,
                    'home_name' => $home->name,
                    'property_name' => $home->name,
                    'name' => $home->name,
                    'property_code' => $home->property_code,
                    'address' => $home->address,
                    'district' => $home->district,
                    'city' => $cityName,
                    'unit_label' => $residentHome->unit_label,
                    'unit_number' => $residentHome->unit_label,
                    'residence_type' => $residentHome->residence_type,
                    'residence_type_label' => match($residentHome->residence_type) {
                        'apartment' => 'شقة',
                        'villa' => 'فيلا',
                        'floor' => 'دور',
                        default => $residentHome->residence_type,
                    },
                    'floor_number' => $residentHome->floor_number,
                    'custom_name' => $residentHome->custom_name,
                    'area_sqm' => $residentHome->area_sqm ? (float) $residentHome->area_sqm : null,
                    'ownership_type' => $residentHome->ownership_type,
                    'ownership_type_label' => $residentHome->ownership_type === 'tenant' ? 'مستأجر' : 'مالك',
                    'is_primary' => (bool) $residentHome->is_primary,
                    'latitude' => $home->latitude,
                    'longitude' => $home->longitude,
                    'created_at' => $residentHome->created_at?->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * حفظ عقار جديد وربطه بالمستخدم تلقائياً، أو ربطه بكود عقار موجود مسبقاً.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function addProperty(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor) {
            // إذا أرسل العميل رمز عقار مسجل مسبقاً، نربطه به مباشرة
            if (!empty($input['property_code'])) {
                $existingHome = Home::where('property_code', $input['property_code'])->first();
                if ($existingHome) {
                    $linkData = [
                        'property_code' => $input['property_code'],
                        'unit_label' => $input['unit_number'] ?? $input['unit_label'] ?? '1',
                    ];
                    return $this->performLink($linkData, $actor);
                }
            }

            $cityId = $input['city_id'] ?? null;
            if (!$cityId && !empty($input['city_name'])) {
                $cityName = trim($input['city_name']);
                $cityId = CityTranslation::where('name', 'like', "%{$cityName}%")->value('city_id');
            }
            if (!$cityId) {
                $firstCity = City::first();
                $cityId = $firstCity?->id ?? 32068;
            }

            $propertyName = $input['name'] ?? $input['property_name'] ?? $input['custom_name'] ?? $input['district'] ?? 'عقار جديد';

            $home = Home::create([
                'city_id' => $cityId,
                'name' => $propertyName,
                'address' => $input['address'],
                'district' => $input['district'] ?? null,
                'latitude' => $input['latitude'] ?? null,
                'longitude' => $input['longitude'] ?? null,
                'property_code' => 'PR-' . strtoupper(Str::random(8)),
                'floors_count' => !empty($input['floor_number']) && is_numeric($input['floor_number']) ? (int) $input['floor_number'] : null,
            ]);

            $hasExistingHomes = $actor->residentHomes()->exists();
            $shouldBePrimary = !empty($input['is_primary']) || !$hasExistingHomes;

            if ($shouldBePrimary && $hasExistingHomes) {
                $actor->residentHomes()->newPivotStatement()->where('user_id', $actor->id)->update(['is_primary' => false]);
            }

            $unitNumber = $input['unit_number'] ?? $input['unit_label'] ?? (($input['residence_type'] ?? '') === 'villa' ? 'الفيلا' : '1');

            $residentHome = ResidentHome::create([
                'user_id' => $actor->id,
                'home_id' => $home->id,
                'unit_label' => $unitNumber,
                'residence_type' => $input['residence_type'] ?? 'apartment',
                'custom_name' => $input['custom_name'] ?? $input['name'] ?? null,
                'floor_number' => $input['floor_number'] ?? null,
                'area_sqm' => $input['area_sqm'] ?? null,
                'ownership_type' => $input['ownership_type'] ?? 'owner',
                'is_primary' => $shouldBePrimary,
            ]);

            app(\App\Modules\Notifications\Services\AdminAlertService::class)->record(
                \App\Modules\Notifications\Enums\AlertType::ResidentLinked, $home->id);

            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiAddProperty, $actor?->id)));

            $cityName = $home->city?->translations->firstWhere('locale', app()->getLocale())?->name ?? $home->city?->code ?? '';

            return [
                'status' => true,
                'message' => 'تم إضافة العقار وربطه بحسابك بنجاح.',
                'data' => [
                    'id' => $residentHome->id,
                    'resident_home_id' => $residentHome->id,
                    'home_id' => $home->id,
                    'property_id' => $home->id,
                    'property_code' => $home->property_code,
                    'name' => $home->name,
                    'property_name' => $home->name,
                    'custom_name' => $residentHome->custom_name,
                    'address' => $home->address,
                    'district' => $home->district,
                    'city' => $cityName,
                    'unit_label' => $residentHome->unit_label,
                    'unit_number' => $residentHome->unit_label,
                    'residence_type' => $residentHome->residence_type,
                    'residence_type_label' => match($residentHome->residence_type) {
                        'apartment' => 'شقة',
                        'villa' => 'فيلا',
                        'floor' => 'دور',
                        default => $residentHome->residence_type,
                    },
                    'floor_number' => $residentHome->floor_number,
                    'area_sqm' => $residentHome->area_sqm ? (float) $residentHome->area_sqm : null,
                    'ownership_type' => $residentHome->ownership_type,
                    'ownership_type_label' => $residentHome->ownership_type === 'tenant' ? 'مستأجر' : 'مالك',
                    'is_primary' => (bool) $residentHome->is_primary,
                    'latitude' => $home->latitude,
                    'longitude' => $home->longitude,
                    'created_at' => $residentHome->created_at?->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * تعيين السكن أو الوحدة كوحدة أساسية نشطة للساكن.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function setPrimary(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor) {
            $homeId = $input['home_id'] ?? null;
            $residentHomeId = $input['resident_home_id'] ?? null;

            $query = ResidentHome::where('user_id', $actor->id);
            if ($residentHomeId) {
                $query->where('id', $residentHomeId);
            } else {
                $query->where('home_id', $homeId);
            }
            $target = $query->firstOrFail();

            ResidentHome::where('user_id', $actor->id)->update(['is_primary' => false]);
            $target->update(['is_primary' => true]);

            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiSetPrimaryHome, $actor?->id)));

            return [
                'status' => true,
                'message' => 'تم تعيين السكن كوحدة أساسية بنجاح.',
                'data' => [
                    'home_id' => $target->home_id,
                    'resident_home_id' => $target->id,
                    'is_primary' => true,
                ]
            ];
        });
    }

    /**
     * حذف السكن أو إلغاء ربط الوحدة بحساب الساكن.
     * @param array $input
     * @param ?User $actor
     * @return array
     * @throws \Throwable
     */
    public function deleteUnit(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor) {
            $residentHomeId = $input['resident_home_id'] ?? null;
            $homeId = $input['home_id'] ?? $input['property_id'] ?? $input['id'] ?? null;

            $query = ResidentHome::where('user_id', $actor->id);
            if ($residentHomeId) {
                $query->where('id', $residentHomeId);
            } elseif ($homeId) {
                $query->where(function ($q) use ($homeId) {
                    $q->where('id', $homeId)->orWhere('home_id', $homeId);
                });
            } else {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'home_id' => 'معرف السكن أو الوحدة مطلوب للحذف.',
                ]);
            }

            $residentHome = $query->first();

            if (!$residentHome) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'home_id' => 'السكن أو الوحدة غير موجودة أو غير مرتبطة بحسابك.',
                ]);
            }

            // التحقق من وجود طلبات خدمة نشطة مرتبطة بالسكن
            $hasActiveBookings = $residentHome->bookings()
                ->whereHas('status', fn ($q) => $q->whereIn('code', ['pending', 'in_progress', 'accepted']))
                ->exists();
            if ($hasActiveBookings) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'home_id' => 'لا يمكن حذف السكن لوجود طلبات خدمة نشطة مرتبطة به حالياً.',
                ]);
            }

            // التحقق من وجود بلاغات طوارئ جارية
            $hasActiveEmergencies = $residentHome->emergencyRequests()
                ->whereHas('status', fn ($q) => $q->whereNotIn('code', ['resolved', 'cancelled']))
                ->exists();
            if ($hasActiveEmergencies) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'home_id' => 'لا يمكن حذف السكن لوجود بلاغات طوارئ جارية مرتبطة به.',
                ]);
            }

            // التحقق من وجود اشتراك ساري
            $hasActiveSubscriptions = $residentHome->residentSubscriptions()
                ->where('ends_at', '>', now())
                ->whereHas('status', fn ($q) => $q->where('code', 'active'))
                ->exists();
            if ($hasActiveSubscriptions) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'home_id' => 'لا يمكن حذف السكن لوجود اشتراك باقة ساري مرتبط به.',
                ]);
            }

            // فك ارتباط الطلبات السابقة حتى لا تتعارض مع قيد المفتاح الأجنبي RESTRICT
            $residentHome->bookings()->update(['resident_home_id' => null]);

            $wasPrimary = (bool) $residentHome->is_primary;
            $deletedHomeId = $residentHome->home_id;
            $deletedResidentHomeId = $residentHome->id;

            $residentHome->delete();

            // إذا كان السكن المحذوف هو الأساسي، نعين سكناً آخر كأساسي إن وجد
            if ($wasPrimary) {
                $nextPrimary = ResidentHome::where('user_id', $actor->id)->latest()->first();
                if ($nextPrimary) {
                    $nextPrimary->update(['is_primary' => true]);
                }
            }

            // إذا لم يعد هناك أي ساكن مرتبط بهذا السكن، ولا توجد طلبات مرتبطة به، نحذف السكن الفردي المؤقت
            $otherResidents = ResidentHome::where('home_id', $deletedHomeId)->exists();
            if (!$otherResidents) {
                $hasBookings = \App\Modules\Bookings\Models\Booking::where('home_id', $deletedHomeId)->exists();
                if (!$hasBookings) {
                    Home::where('id', $deletedHomeId)->delete();
                }
            }

            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiDeleteUnit, $actor?->id)));

            return [
                'status' => true,
                'message' => 'تم حذف السكن بنجاح.',
                'data' => [
                    'deleted_id' => $deletedResidentHomeId,
                    'home_id' => $deletedHomeId,
                    'resident_home_id' => $deletedResidentHomeId,
                ],
            ];
        });
    }
}
