<?php

namespace App\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Identity\Enums\RoleCode;

/** قاعدة تفويض مشتركة؛ تبقى قواعد بيانات كل طلب داخل مجاله. */
abstract class AdminRequest extends FormRequest
{
    /**
     * منع تجاوز صلاحيات المجال حتى عند استدعاء المسار مباشرة.
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() instanceof \App\Modules\Identity\Models\Admin && $this->user()->is_active;
    }
}
