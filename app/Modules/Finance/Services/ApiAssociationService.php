<?php

namespace App\Modules\Finance\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Finance\Models\AssociationAccount;
use App\Modules\Finance\Models\AssociationDue;
use App\Modules\Finance\Models\AssociationLedgerEntry;

use App\Modules\Properties\Models\ResidentHome;

use App\Modules\Identity\Models\User;

/** قواعد عمل Finance؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiAssociationService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function association(array $input, ?User $actor): mixed
    {
        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        $account = AssociationAccount::query()->where('home_id', $membership->home_id)->first();
        if (!$account) {
            return ['data' => ['account' => null, 'dues' => [], 'ledger' => []]];
        }

        return ['data' => [
            'account' => $account,
            'dues' => AssociationDue::query()->where('resident_home_id', $membership->id)->orderByDesc('due_at')->paginate(20),
            'ledger' => AssociationLedgerEntry::query()->where('association_account_id', $account->id)
                ->where(function ($query) use ($membership, $input, $actor) {
                    $query->where('resident_home_id', $membership->id)->orWhere(function ($q) use ($input, $actor) {
                        $q->whereNull('resident_home_id')->where('user_id', $actor->id);
                    });
                })->orderByDesc('posted_at')->paginate(20),
        ]];
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
