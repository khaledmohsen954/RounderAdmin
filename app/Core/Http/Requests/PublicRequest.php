<?php

namespace App\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Identity\Enums\RoleCode;

/** قاعدة تفويض مشتركة؛ تبقى قواعد بيانات كل طلب داخل مجاله. */
abstract class PublicRequest extends FormRequest
{
    /**
     * يسمح بطلبات الدخول واللغة العامة؛ middleware يطبق القيود الإضافية.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
