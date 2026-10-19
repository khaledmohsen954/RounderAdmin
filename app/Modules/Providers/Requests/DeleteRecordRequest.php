<?php
namespace App\Modules\Providers\Requests;
use App\Core\Http\Requests\AdminRequest;
/** تفويض حذف السجل؛ المعرّف العددي مقيد في المسار. */
class DeleteRecordRequest extends AdminRequest
{
    /** @return array<string,mixed> */
    public function rules(): array { return []; }
}
