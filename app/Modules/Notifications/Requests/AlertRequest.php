<?php
namespace App\Modules\Notifications\Requests;
use App\Core\Http\Requests\AdminRequest;
/** Authentication plus service-level event visibility protect all read operations. */
class AlertRequest extends AdminRequest {
    /** @return array<string,mixed> */
    public function rules(): array { return []; }
}
