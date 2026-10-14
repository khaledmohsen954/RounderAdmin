<?php

namespace App\Modules\Community\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات CommunityController::comment قبل تشغيل الخدمة. */
class ApiCommunityCommentRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:3000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('community_comments', 'id')->where('community_post_id', $this->route('postId'))],
        ];
    }
}
