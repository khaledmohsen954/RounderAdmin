<?php
namespace App\Core\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
/** عمليات الحساب المشتركة بين الأدوار بعد المصادقة. */
abstract class AuthenticatedRequest extends FormRequest
{
    /** @return bool */
    public function authorize(): bool { return $this->user() !== null; }
}
