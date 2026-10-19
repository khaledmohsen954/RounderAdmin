<?php
namespace App\Modules\Bookings\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Bookings\Events\RecordDeleted;
/** الحذف لا يتجاوز العلاقات أو يمحو سجلات مالية مرتبطة. */
final class AdminDeletionService
{
    /** @param int $id @param int $adminId @return void @throws \Throwable */
    public function booking(int $id, int $adminId): void
    {
        DB::transaction(function () use ($id,$adminId) {
            $record = \App\Modules\Bookings\Models\Booking::lockForUpdate()->findOrFail($id);
            if ($record->payments()->exists()) $this->blocked();
            if ($record->bookingMedia()->exists()) $this->blocked();
            if ($record->bookingAdditionalWork()->exists()) $this->blocked();
            if ($record->bookingRatings()->exists()) $this->blocked();
            if ($record->bookingWarranties()->exists()) $this->blocked();
            if ($record->providerCommissionEntries()->exists()) $this->blocked();
            if ($record->bookingProviderAssignments()->exists()) $this->blocked();
            if (!in_array($record->status->code, [\App\Modules\Bookings\Enums\BookingStatusCode::Pending->value, \App\Modules\Bookings\Enums\BookingStatusCode::Cancelled->value], true)) $this->blocked();
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
