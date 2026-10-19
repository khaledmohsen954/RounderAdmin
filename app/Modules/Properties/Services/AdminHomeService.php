<?php

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Home;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Properties\Events\PropertiesChanged;
use App\Modules\Properties\Enums\Operation;

/** قواعد عمل Properties؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminHomeService
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
        $homes=Home::with(['city.translations','residents'])->latest()->paginate(15);$cities=City::available()->with('translations')->get()->sortBy(fn ($city) => $city->translations->firstWhere('locale', app()->getLocale())?->name ?? $city->code);return compact('homes','cities');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new PropertiesChanged(Operation::AdminHomeStore, $actor?->id)));
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
    private function performStore(array $input, ?User $actor): mixed
    {
        $data = $input;

  
  Home::create($data);return __('Home created successfully.');
    }
}
