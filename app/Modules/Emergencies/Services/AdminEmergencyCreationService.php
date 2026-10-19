<?php

namespace App\Modules\Emergencies\Services;
use App\Modules\Emergencies\Enums\EmergencyStatusCode;
use App\Modules\Identity\Enums\RoleCode;

use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use App\Modules\Emergencies\Models\EmergencyRequestUpdate;
use App\Modules\Emergencies\Models\EmergencyType;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\Service;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Emergencies\Events\EmergenciesChanged;
use App\Modules\Emergencies\Enums\Operation;

/** قواعد عمل Emergencies؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminEmergencyCreationService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function emergencyForm(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return $this->form(true);
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeEmergency(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStoreEmergency($input, $actor);
            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::AdminEmergencyCreationStoreEmergency, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStoreEmergency(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;

        
        DB::transaction(function () use ($data, $input, $actor) {
            $membership = $this->membership($data['resident_home_id']);
            $status = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Received->value)->firstOrFail();
            $id = EmergencyRequest::query()->create([
                'resident_id' => $membership->user_id, 'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id, 'emergency_type_id' => $data['emergency_type_id'],
                'status_id' => $status->id, 'reference' => 'EM-'.Str::upper(Str::random(12)),
                'contact_phone' => $data['contact_phone'], 'location_details' => $data['location_details'],
                'description' => $data['description'], 'requested_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ])->getKey();
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $id, 'status_id' => $status->id,
                ...\App\Core\AdminActor::attributes($actor, 'updated_by'), 'note' => 'Created by administrator', 'created_at' => now(),
            ]);
        });
        return __('Emergency request received');
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
                'homes.name as home_name', 'users.phone as contact_phone')->orderBy('users.name')->get();
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
