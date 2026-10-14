<?php

namespace App\Modules\Properties\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Properties\Models\ResidentHome;

use App\Modules\Properties\Models\ResidentVisit;

use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Properties\Events\PropertiesChanged;
use App\Modules\Properties\Enums\Operation;

/** قواعد عمل Properties؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiVisitService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function visits(array $input, ?User $actor): mixed
    {
        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        return ['data' => ResidentVisit::query()
            ->where('resident_home_id', $membership->id)
            ->where('created_by', $actor->id)
            ->orderByDesc('starts_at')->paginate(20)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createVisit(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performCreateVisit($input, $actor);
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiVisitCreateVisit, $actor?->id)));
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
    private function performCreateVisit(array $input, ?User $actor): mixed
    {
        $data = $input;

        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        
        $id = ResidentVisit::query()->create([
            ...$data,
            'resident_home_id' => $membership->id,
            'created_by' => $actor->id,
            'access_code' => Str::upper(Str::random(8)),
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return ['data' => ResidentVisit::query()->find($id)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $visitId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancelVisit(array $input, ?User $actor, int $visitId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $visitId) {
            $result = $this->performCancelVisit($input, $actor, $visitId);
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiVisitCancelVisit, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $visitId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCancelVisit(array $input, ?User $actor, int $visitId): mixed
    {
        $deleted = ResidentVisit::query()
            ->where('id', $visitId)
            ->where('created_by', $actor->id)
            ->whereNull('checked_in_at')
            ->where('starts_at', '>', now())
            ->delete();
        DomainViolation::unless($deleted, FailureReason::NotFound, 'Visit cannot be found or cancelled.');

        return ['message' => 'Visit cancelled.'];
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function membership(array $input, ?User $actor): mixed
    {
        $query = ResidentHome::query()->where('user_id', $actor->id);
        if ((isset($input['resident_home_id']) && $input['resident_home_id'] !== '')) {
            $query->where('id', (int) ($input['resident_home_id'] ?? 0));
        } else {
            $query->orderByDesc('is_primary')->orderBy('id');
        }

        return $query->first();
    }

}
