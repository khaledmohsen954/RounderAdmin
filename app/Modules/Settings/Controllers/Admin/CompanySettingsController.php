<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Models\CompanySetting;
use App\Modules\Settings\Requests\CompanySettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * متحكم إدارة إعدادات وبيانات المنشأة الرسمية في لوحة الإدارة.
 */
class CompanySettingsController extends Controller
{
    /**
     * عرض نموذج تعديل بيانات المنشأة.
     *
     * @return View
     */
    public function edit(): View
    {
        $settings = CompanySetting::current();

        return view('admin.settings.company', compact('settings'));
    }

    /**
     * حفظ وتحديث بيانات المنشأة ورفع صورة الختم الرسمي.
     *
     * @param CompanySettingsRequest $request
     * @return RedirectResponse
     */
    public function update(CompanySettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $settings = CompanySetting::query()->first();

            if (!$settings) {
                $settings = new CompanySetting();
            }

            $settings->trade_name = $validated['trade_name'];
            $settings->cr_number = $validated['cr_number'];
            $settings->tax_number = $validated['tax_number'] ?? null;
            $settings->trademark_registration = $validated['trademark_registration'] ?? null;
            $settings->legal_representative = $validated['legal_representative'];
            $settings->representative_title = $validated['representative_title'];
            $settings->support_phone = $validated['support_phone'] ?? '920000488';
            $settings->support_email = $validated['support_email'] ?? 'support@rounder.sa';
            $settings->support_working_hours = $validated['support_working_hours'] ?? 'الأحد إلى الخميس، 9 ص - 6 م';
            $settings->support_chat_enabled = $request->boolean('support_chat_enabled');

            // حذف الختم الحالي إن طُلب ذلك
            if ($request->boolean('remove_stamp') && $settings->stamp_path) {
                if (Storage::disk('public')->exists($settings->stamp_path)) {
                    Storage::disk('public')->delete($settings->stamp_path);
                }
                $settings->stamp_path = null;
            }

            // رفع صورة الختم الجديد
            if ($request->hasFile('stamp_image')) {
                if ($settings->stamp_path && Storage::disk('public')->exists($settings->stamp_path)) {
                    Storage::disk('public')->delete($settings->stamp_path);
                }

                $path = $request->file('stamp_image')->store('company', 'public');
                $settings->stamp_path = $path;
            }

            $settings->save();
        });

        return redirect()->route('admin.company-settings.edit')
            ->with('status', __('Company settings updated successfully.'));
    }
}

