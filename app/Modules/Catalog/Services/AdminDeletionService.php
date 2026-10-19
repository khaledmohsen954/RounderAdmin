<?php
namespace App\Modules\Catalog\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Catalog\Events\RecordDeleted;
/** الحذف لا يتجاوز العلاقات أو يمحو سجلات مالية مرتبطة. */
final class AdminDeletionService
{
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function service(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Catalog\Models\Service::lockForUpdate()->findOrFail($id);
            if ($record->bookings()->exists()) $this->blocked();
            if ($record->servicePackageServices()->exists()) $this->blocked();
            if ($record->residentSubscriptionServices()->exists()) $this->blocked();
            
            try { $record->delete(); }
            catch (\Illuminate\Database\QueryException $error) {
                if (($error->errorInfo[1] ?? null) !== 1451) throw $error;
                $this->blocked();
            }
            DB::afterCommit(fn () => event(new RecordDeleted($record::class,$id,$adminId)));
        });
    }
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function package(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Catalog\Models\ServicePackage::lockForUpdate()->findOrFail($id);
            if ($record->residentSubscriptions()->exists()) $this->blocked();
            
            try { $record->delete(); }
            catch (\Illuminate\Database\QueryException $error) {
                if (($error->errorInfo[1] ?? null) !== 1451) throw $error;
                $this->blocked();
            }
            DB::afterCommit(fn () => event(new RecordDeleted($record::class,$id,$adminId)));
        });
    }
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function feature(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Catalog\Models\PackageFeature::lockForUpdate()->findOrFail($id);
            if ($record->values()->exists()) $this->blocked();
            
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
