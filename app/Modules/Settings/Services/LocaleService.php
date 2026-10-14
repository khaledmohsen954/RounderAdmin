<?php
namespace App\Modules\Settings\Services;
use App\Modules\Identity\Models\User;
use App\Modules\Settings\Events\SettingsChanged;
use App\Modules\Settings\Enums\Operation;
use Illuminate\Support\Facades\DB;
/** حفظ تفضيل اللغة في الحساب؛ الكوكي والجلسة مسؤولية HTTP. */
class LocaleService
{
    /**
     * @param array<string, mixed> $input
     * @param User|null $actor
     * @return string
     * @throws \Throwable عند فشل الحفظ.
     */
    public function update(array $input, ?User $actor): string
    {
        return DB::transaction(function () use ($input, $actor) {
            $actor?->update(['locale' => $input['locale']]);
            DB::afterCommit(fn () => event(new SettingsChanged(Operation::UpdateLocale, $actor?->id)));
            return $input['locale'];
        });
    }
}
