<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
/** عرض الصفحة التعريفية للمشروع داخل لوحة الإدارة. */
class AboutController extends Controller { public function __invoke(){return view('admin.about');} }
