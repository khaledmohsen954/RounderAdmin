<?php

namespace App\Modules\Bookings\Services;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Identity\Enums\RoleCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Bookings\Models\BookingStatusHistory;

use App\Modules\Emergencies\Models\EmergencyType;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\Service;

use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminOrderCreationService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function serviceForm(array $input, ?User $actor): mixed
    {
        return $this->form(false);
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeService(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStoreService($input, $actor);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::AdminOrderCreationStoreService, $actor?->id)));
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
    private function performStoreService(array $input, ?User $actor): mixed
    {
        $data = $input;

        
        DB::transaction(function () use ($data, $input, $actor) {
            $membership = $this->membership($data['resident_home_id']);
            $service = Service::query()->where('id', $data['service_id'])->where('is_active', true)->lockForUpdate()->first();
            if (!$service) {
                throw ValidationException::withMessages(['service_id' => __('Choose an active service.')]);
            }
            $status = BookingStatus::query()->where('code', BookingStatusCode::Pending->value)->firstOrFail();
            // Prices and resident IDs are always taken from trusted database records.
            $id = Booking::query()->create([
                'resident_id' => $membership->user_id, 'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id, 'service_id' => $service->id,
                'booking_status_id' => $status->id, 'scheduled_at' => $data['scheduled_at'],
                'total_amount' => $service->base_price, 'notes' => $data['notes'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ])->getKey();
            BookingStatusHistory::query()->create([
                'booking_id' => $id, 'booking_status_id' => $status->id,
                'changed_by' => $actor->id, 'note' => 'Created by administrator', 'created_at' => now(),
            ]);
        });
        return __('Service order created successfully.');
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param bool $emergency
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function form(bool $emergency): mixed
    {
        $memberships = ResidentHome::query()
            ->join('users', 'resident_homes.user_id', '=', 'users.id')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('homes', 'resident_homes.home_id', '=', 'homes.id')
            ->where('roles.code', RoleCode::Resident->value)
            ->select('resident_homes.id', 'resident_homes.unit_label', 'users.name as resident_name',
                'homes.name as home_name')->orderBy('users.name')->get();
        $table = $emergency ? 'emergency_types' : 'services';
        $translations = $emergency ? 'emergency_type_translations' : 'service_translations';
        $foreign = $emergency ? 'emergency_type_id' : 'service_id';
        $options = ($emergency ? EmergencyType::query() : Service::query())->leftJoin($translations, function ($join) use ($table, $translations, $foreign) {
            $join->on("$table.id", '=', "$translations.$foreign")->where("$translations.locale", app()->getLocale());
        })->where("$table.is_active", true)
            ->select("$table.id", "$table.code", "$translations.name")->orderBy("$table.code")->get();
        return compact('emergency', 'memberships', 'options');
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param int $id
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function membership(int $id): mixed
    {
        $membership = ResidentHome::query()->whereKey($id)
            ->whereHas('user.role', fn ($query) => $query->where('code', RoleCode::Resident->value))->lockForUpdate()->first();
        if (!$membership) {
            throw ValidationException::withMessages(['resident_home_id' => __('Select a valid resident unit.')]);
        }
        return $membership;
    }
}
