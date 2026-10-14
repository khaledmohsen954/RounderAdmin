<?php
namespace App\Modules\Identity\Requests;
use App\Core\Http\Requests\AdminRequest;
/** إنهاء جلسة مدير مصادق عليه. */
class AdminSessionRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array { return []; }
}
