<?php

namespace App\Modules\Properties\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Properties\Models\Home;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Properties\Events\PropertiesChanged;
use App\Modules\Properties\Enums\Operation;

/** قواعد عمل Properties؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiResidentHomeService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $homes=$actor->residentHomes()->with('city.translations')->get()->map(fn(Home $h)=>['id'=>$h->id,'name'=>$h->name,'unit_label'=>$h->pivot->unit_label,'is_primary'=>(bool)$h->pivot->is_primary,'address'=>$h->address,'property_code'=>$h->property_code]);
  return ['data'=>$homes];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function link(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLink($input, $actor);
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::ApiResidentHomeLink, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLink(array $input, ?User $actor): mixed
    {
        $data = $input;

  
  $user=$actor;$home=Home::where('property_code',$data['property_code'])->firstOrFail();
  $already=$user->residentHomes()->where('homes.id',$home->id)->wherePivot('unit_label',$data['unit_label'])->exists();
  DomainViolation::rejectIf($already, FailureReason::Conflict, __('Residence is already linked to this account.'));
  DB::transaction(function()use($user,$home,$data){
   $user->residentHomes()->newPivotStatement()->where('user_id',$user->id)->update(['is_primary'=>false]);
   $user->residentHomes()->attach($home->id,['unit_label'=>$data['unit_label'],'is_primary'=>true]);
  });
  return ['message'=>__('Residence linked successfully.'),'data'=>['home_id'=>$home->id,'name'=>$home->name,'unit_label'=>$data['unit_label']]];
    }
}
