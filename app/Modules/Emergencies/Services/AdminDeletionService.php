<?php
namespace App\Modules\Emergencies\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Emergencies\Events\RecordDeleted;
/** الحذف لا يتجاوز العلاقات أو يمحو سجلات مالية مرتبطة. */
final class AdminDeletionService
{
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function emergency(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Emergencies\Models\EmergencyRequest::lockForUpdate()->findOrFail($id);
            if ($record->providerCommissionEntries()->exists()) $this->blocked();
            if ($record->emergencyProviderAssignments()->exists()) $this->blocked();
            if (!in_array($record->status->code, [\App\Modules\Emergencies\Enums\EmergencyStatusCode::Received->value, \App\Modules\Emergencies\Enums\EmergencyStatusCode::Cancelled->value], true)) $this->blocked();
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
