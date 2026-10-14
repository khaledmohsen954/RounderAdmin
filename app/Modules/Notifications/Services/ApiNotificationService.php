<?php

namespace App\Modules\Notifications\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Notifications\Models\ResidentNotification;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Notifications\Events\NotificationsChanged;
use App\Modules\Notifications\Enums\Operation;

/** قواعد عمل Notifications؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiNotificationService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function notifications(array $input, ?User $actor): mixed
    {
        return ['data' => ResidentNotification::query()
            ->where('user_id', $actor->id)->orderByDesc('created_at')->paginate(30)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $notificationId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function markNotificationRead(array $input, ?User $actor, int $notificationId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $notificationId) {
            $result = $this->performMarkNotificationRead($input, $actor, $notificationId);
            DB::afterCommit(fn () => event(new NotificationsChanged(Operation::ApiNotificationMarkNotificationRead, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $notificationId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performMarkNotificationRead(array $input, ?User $actor, int $notificationId): mixed
    {
        $updated = ResidentNotification::query()->where('id', $notificationId)
            ->where('user_id', $actor->id)->update(['read_at' => now(), 'updated_at' => now()]);
        DomainViolation::unless($updated, FailureReason::NotFound);

        return ['message' => 'Notification marked as read.'];
    }

}
