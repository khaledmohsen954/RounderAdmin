<?php
namespace App\Modules\Settings\Requests;
use App\Core\Http\Requests\PublicRequest;
use App\Core\Enums\SupportedLocale;
use Illuminate\Validation\Rule;
/** اللغة متاحة قبل الدخول، لكن تُحصر في لغات لوحة الإدارة. */
class UpdateLocaleRequest extends PublicRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['locale' => ['required', Rule::in([SupportedLocale::Arabic->value, SupportedLocale::English->value])]];
    }
}
