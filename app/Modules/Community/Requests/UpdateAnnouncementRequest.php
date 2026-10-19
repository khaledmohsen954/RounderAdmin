<?php
namespace App\Modules\Community\Requests;
/** يسمح تعديل الإعلان المنتهي مع الاحتفاظ بتاريخ انتهائه. */
class UpdateAnnouncementRequest extends AdminResidentContentStoreAnnouncementRequest
{
    /** @return array<string,mixed> */
    public function rules(): array { return array_replace(parent::rules(), ['expires_at'=>['nullable','date']]); }
}
