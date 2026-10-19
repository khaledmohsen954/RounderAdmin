<?php
namespace App\Modules\Community\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Community\Events\RecordDeleted;
/** الحذف لا يتجاوز العلاقات أو يمحو سجلات مالية مرتبطة. */
final class AdminDeletionService
{
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function announcement(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Community\Models\Announcement::lockForUpdate()->findOrFail($id);

            
            try { $record->delete(); }
            catch (\Illuminate\Database\QueryException $error) {
                if (($error->errorInfo[1] ?? null) !== 1451) throw $error;
                $this->blocked();
            }
            DB::afterCommit(fn () => event(new RecordDeleted($record::class,$id,$adminId)));
        });
    }
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function initiative(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Community\Models\Initiative::lockForUpdate()->findOrFail($id);
            if ($record->initiativeParticipations()->exists()) $this->blocked();
            
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
