<?php
namespace App\Modules\Properties\Requests;
use App\Core\Http\Requests\AdminRequest;
use App\Modules\Properties\Enums\CountryCode;
use Illuminate\Validation\Rule;
/** بيانات العقار والشقق؛ لا يُقبل الدور خارج عدد أدوار المبنى. */
class AdminHomeStoreRequest extends AdminRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'city_id'=>['required','integer',Rule::exists('cities','id')->where('country_code',CountryCode::SaudiArabia->value)->where('is_active',true)],
            'name'=>['required','string','min:2','max:120'],'address'=>['required','string','max:255'],
            'property_code'=>['required','alpha_dash','min:5','max:60','unique:homes,property_code'],
            'health_percent'=>['required','integer','between:0,100'],
            'association_balance'=>['required','numeric','min:0','max:9999999999.99','decimal:0,2'],
            'floors_count'=>['required','integer','between:1,200'],
            'units'=>['required','array','min:1','max:500'],
            'units.*.id'=>['nullable','integer','distinct'],
            'units.*.unit_number'=>['required','string','max:40','distinct'],
            'units.*.floor_number'=>['required','integer','min:1','lte:floors_count'],
            'units.*.area_sqm'=>['required','numeric','min:0.01','max:99999999.99','decimal:0,2'],
            'units.*.rooms_count'=>['required','integer','between:1,100'],
        ];
    }
}
