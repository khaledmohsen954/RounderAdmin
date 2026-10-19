<?php
namespace App\Modules\Community\Services;
use App\Modules\Community\Models\{Announcement,Initiative};
use App\Modules\Properties\Models\Home;
use App\Modules\Community\Events\ContentUpdated;
use Illuminate\Support\Facades\DB;
/** تعديل المحتوى والترجمات مع الحفاظ على المعرّف وحقوق المشاركين. */
final class AdminContentEditingService
{
    /** @param int $id @return array<string,mixed> */
    public function announcement(int $id): array
    {
        return ['record'=>Announcement::with('translations')->findOrFail($id),'initiative'=>false,'homes'=>Home::orderBy('name')->get()];
    }
    /** @param int $id @return array<string,mixed> */
    public function initiative(int $id): array
    {
        return ['record'=>Initiative::with('translations')->findOrFail($id),'initiative'=>true,'homes'=>Home::orderBy('name')->get()];
    }
    /** @param int $id @param array<string,mixed> $input @param int $adminId @return void @throws \Throwable */
    public function updateAnnouncement(int $id, array $input, int $adminId): void
    {
        DB::transaction(function () use ($id,$input,$adminId) {
            $record=Announcement::lockForUpdate()->findOrFail($id);
            $record->update(['home_id'=>$input['home_id'] ?? null,'is_published'=>$input['published'],
                'published_at'=>$input['published'] ? ($record->published_at ?? now()) : null,
                'expires_at'=>$input['expires_at'] ?? null]);
            foreach(['ar','en'] as $locale) $record->translations()->updateOrCreate(['locale'=>$locale],
                ['title'=>$input['title_'.$locale],'body'=>$input['body_'.$locale]]);
            DB::afterCommit(fn()=>event(new ContentUpdated(Announcement::class,$id,$adminId)));
        });
    }
    /** @param int $id @param array<string,mixed> $input @param int $adminId @return void @throws \Throwable */
    public function updateInitiative(int $id, array $input, int $adminId): void
    {
        DB::transaction(function () use ($id,$input,$adminId) {
            $record=Initiative::lockForUpdate()->findOrFail($id);
            if ($record->home_id !== (int)$input['home_id'] && $record->initiativeParticipations()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['home_id'=>__('This record is in use and cannot be deleted.')]);
            }
            $record->update(['home_id'=>$input['home_id'],'title'=>$input['title_ar'],'description'=>$input['description_ar'],
                'target_amount'=>$input['target_amount'] ?? null,'starts_at'=>$input['starts_at'],
                'ends_at'=>$input['ends_at'],'is_published'=>$input['is_published']]);
            foreach(['ar','en'] as $locale) $record->translations()->updateOrCreate(['locale'=>$locale],
                ['title'=>$input['title_'.$locale],'description'=>$input['description_'.$locale]]);
            DB::afterCommit(fn()=>event(new ContentUpdated(Initiative::class,$id,$adminId)));
        });
    }
}
