<?php
namespace App\Modules\Dashboard\Controllers\Admin;
use App\Http\Controllers\Controller;
/** صفحة تعريفية لا تنفذ استعلامات أو قواعد عمل. */
class AboutController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function __invoke(): \Illuminate\Contracts\View\View
    {
        return view('admin.about');
    }
}
