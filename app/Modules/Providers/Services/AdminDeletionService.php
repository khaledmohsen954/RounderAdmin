<?php
namespace App\Modules\Providers\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Providers\Events\RecordDeleted;
/** الحذف لا يتجاوز العلاقات أو يمحو سجلات مالية مرتبطة. */
final class AdminDeletionService
{
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function provider(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Providers\Models\ProviderProfile::lockForUpdate()->findOrFail($id);
            if ($record->providerCommissionEntries()->exists()) $this->blocked();
            if ($record->emergencyProviderAssignments()->exists()) $this->blocked();
            if ($record->bookingProviderAssignments()->exists()) $this->blocked();
            if ($record->contracts()->whereHas('status', fn ($q)=>$q->where('code',\App\Modules\Providers\Enums\ContractStatusCode::Active->value))->exists()) $this->blocked();
            $record->contracts()->delete();
            try { $record->delete(); }
            catch (\Illuminate\Database\QueryException $error) {
                if (($error->errorInfo[1] ?? null) !== 1451) throw $error;
                $this->blocked();
            }
            DB::afterCommit(fn () => event(new RecordDeleted($record::class,$id,$adminId)));
        });
    }
    /** @return never @throws ValidationException */
    private function blocked(): never
    {
        throw ValidationException::withMessages(['delete'=>__('This record is in use and cannot be deleted.')]);
    }
}
