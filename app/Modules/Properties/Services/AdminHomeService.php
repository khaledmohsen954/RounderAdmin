<?php
namespace App\Modules\Properties\Services;
use App\Modules\Properties\Models\{City, Home};
use App\Modules\Identity\Models\Admin;
use App\Modules\Properties\Events\PropertiesChanged;
use App\Modules\Properties\Enums\Operation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
/** حفظ المبنى والشقق في معاملة واحدة مع حماية الشقق المرتبطة بالسكان. */
class AdminHomeService
{
    /** @param array<string,mixed> $input @param Admin|null $actor @return array<string,mixed> */
    public function index(array $input, ?Admin $actor): array
    {
        return ['homes'=>Home::with('city.translations')->withCount(['residents','units'])->latest()->paginate(15)];
    }
    /** @param int|null $id @return array<string,mixed> @throws \Throwable */
    public function form(?int $id = null): array
    {
        return ['home'=>$id ? Home::with('units')->findOrFail($id) : new Home,
            'cities'=>City::available()->with('translations')->get()->sortBy(fn ($c) =>
                $c->translations->firstWhere('locale',app()->getLocale())?->name ?? $c->code)];
    }
    /** @param array<string,mixed> $input @param Admin $actor @return string @throws \Throwable */
    public function store(array $input, Admin $actor): string
    {
        $this->save($input,$actor);
        return __('Home created successfully.');
    }
    /** @param int $id @param array<string,mixed> $input @param Admin $actor @return void @throws \Throwable */
    public function update(int $id, array $input, Admin $actor): void { $this->save($input,$actor,$id); }
    /** @param array<string,mixed> $input @param Admin $actor @param int|null $id @return void @throws \Throwable */
    private function save(array $input, Admin $actor, ?int $id = null): void
    {
        DB::transaction(function () use ($input,$actor,$id) {
            $home = $id ? Home::lockForUpdate()->findOrFail($id) : new Home;
            $units = $input['units']; unset($input['units']);
            $home->fill($input)->save();
            $kept = [];
            foreach ($units as $row) {
                $unit = !empty($row['id']) ? $home->units()->lockForUpdate()->findOrFail($row['id']) : $home->units()->make();
                unset($row['id']);
                if ($unit->exists && $unit->unit_number !== $row['unit_number'] &&
                    $home->residentHomes()->where('unit_label',$unit->unit_number)->exists()) {
                    throw ValidationException::withMessages(['units'=>__('An occupied unit number cannot be changed.')]);
                }
                $unit->fill($row)->save(); $kept[] = $unit->id;
                // ربط العضويات القديمة المطابقة يحول التعريف النصي إلى مفتاح فعلي دون تخمين.
                $home->residentHomes()->whereNull('home_unit_id')->where('unit_label',$unit->unit_number)->update(['home_unit_id'=>$unit->id]);
            }
            foreach ($home->units()->whereNotIn('id',$kept)->lockForUpdate()->get() as $removed) {
                if ($removed->memberships()->exists() || $home->residentHomes()->where('unit_label',$removed->unit_number)->exists()) {
                    throw ValidationException::withMessages(['units'=>__('This record is in use and cannot be deleted.')]);
                }
                $removed->delete();
            }
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::AdminHomeStore,$actor->id)));
        });
    }
    /** @param int $id @return void @throws \Throwable */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $home = Home::lockForUpdate()->findOrFail($id);
            foreach (['residentHomes','bookings','associationAccounts','facilityReports','announcements','communityPosts','initiatives','emergencyRequests'] as $relation) {
                if ($home->$relation()->exists()) throw ValidationException::withMessages(['delete'=>__('This record is in use and cannot be deleted.')]);
            }
            $home->delete();
        });
    }
}
