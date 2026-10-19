<?php

namespace App\Modules\Providers\Requests;

use App\Core\Enums\SupportedLocale;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;

/** تعديل بيانات الشركة والحساب؛ تغيير كلمة المرور له مسار إدارة الحسابات المخصص. */
class AdminProviderUpdateRequest extends AdminRequest
{
    /**
     * يتحدد استثناء التفرد من السجل المرتبط بالمسار، وليس من user_id مرسل من العميل.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('provider')->user_id;
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'regex:/^\+[1-9][0-9]{7,14}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'locale' => ['required', Rule::enum(SupportedLocale::class)],
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'is_approved' => ['required', 'boolean'],
            'contract_id'=>['nullable','integer'],
            'service_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'emergency_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'starts_on'=>['required_with:contract_id','date'],
            'ends_on'=>['nullable','date','after_or_equal:starts_on'],
            'terms'=>['nullable','string','max:10000'],
        ];
    }
}
