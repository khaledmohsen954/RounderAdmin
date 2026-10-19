<?php
namespace App\Modules\Properties\Requests;
use App\Core\Http\Requests\ResidentRequest;
/** مدخلات البحث في المدن المتاحة للتكامل. */
class CityIndexRequest extends ResidentRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['search'=>['nullable','string','max:120'], 'page'=>['sometimes','integer','min:1'],
            'per_page'=>['sometimes','integer','between:1,100']];
    }
}
