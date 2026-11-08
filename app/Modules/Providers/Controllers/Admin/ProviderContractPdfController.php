<?php

namespace App\Modules\Providers\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Settings\Models\CompanySetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

/**
 * تصدير وتحميل عقد مزود الخدمة بصيغة PDF بتصميم وهوية راوندر.
 */
class ProviderContractPdfController extends Controller
{
    public function download(ProviderProfile $provider): Response
    {
        $provider->loadMissing(['user', 'contracts']);
        $contract = $provider->contracts()->latest('id')->first();
        $companySettings = CompanySetting::current();

        $stampLocalPath = null;
        if ($companySettings->stamp_path && Storage::disk('public')->exists($companySettings->stamp_path)) {
            $stampLocalPath = Storage::disk('public')->path($companySettings->stamp_path);
        }

        $providerSealLocalPath = null;
        if ($provider->company_seal && Storage::disk('local')->exists($provider->company_seal)) {
            $ext = strtolower(pathinfo($provider->company_seal, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $providerSealLocalPath = Storage::disk('local')->path($provider->company_seal);
            }
        }

        $bodyHtml = view('admin.providers.partials.contract-template-content', [
            'provider' => $provider,
            'contract' => $contract,
            'companySettings' => $companySettings,
        ])->render();

        // إذا كان العقد المحفوظ يحتوي على تعديلات مخصصة داخل قالب راوندر نستخدمه مع تحديث مسار الختم المحلي لـ mPDF
        if ($contract && !empty($contract->terms) && str_contains($contract->terms, 'rounder-contract-doc')) {
            $bodyHtml = $contract->terms;
            if ($contract->reference) {
                $bodyHtml = str_replace('يُصدر تلقائياً عند الحفظ', e($contract->reference), $bodyHtml);
            }
        }

        if ($stampLocalPath && $companySettings->stamp_url) {
            $bodyHtml = str_replace($companySettings->stamp_url, $stampLocalPath, $bodyHtml);
        }

        if ($providerSealLocalPath) {
            $sealRouteUrl = route('admin.providers.documents.show', [$provider->id, 'company_seal']);
            $bodyHtml = str_replace($sealRouteUrl, $providerSealLocalPath, $bodyHtml);

            // إذا حُفظ العقد عند الإنشاء قبل توليد مسار الختم، نُدرج صورة ختم الطرف الثاني داخل الخانة المخصصة لها
            if (!str_contains($bodyHtml, $providerSealLocalPath)) {
                $sealImgTag = '<img src="' . e($providerSealLocalPath) . '" alt="ختم الطرف الثاني" style="max-height: 95px; max-width: 160px;">';
                if (str_contains($bodyHtml, 'data-contract-field="second_party_seal"')) {
                    $bodyHtml = preg_replace(
                        '#(<div[^>]*data-contract-field="second_party_seal"[^>]*>)\s*(</div>)#u',
                        '$1' . $sealImgTag . '$2',
                        $bodyHtml,
                        1
                    ) ?? $bodyHtml;
                }
            }
        }

        $fullHtml = view('admin.providers.contract-pdf', [
            'provider' => $provider,
            'contract' => $contract,
            'companySettings' => $companySettings,
            'bodyHtml' => $bodyHtml,
            'providerSealLocalPath' => $providerSealLocalPath,
        ])->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $customFontDir = public_path('assets/fonts');
        if (is_dir($customFontDir)) {
            $fontDirs[] = $customFontDir;
            $fontData['tajawal'] = [
                'R' => 'tajawal-regular.ttf',
                'B' => 'tajawal-bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        }

        $tempDir = storage_path('framework/cache/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 16,
            'margin_footer' => 6,
            'tempDir' => $tempDir,
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
            'default_font' => isset($fontData['tajawal']) ? 'tajawal' : 'xbriyaz',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle('عقد تقديم خدمات الصيانة - ' . ($provider->company_name ?: 'راوندر'));
        $mpdf->SetAuthor($companySettings->trade_name ?: 'Rounder');
        $mpdf->SetHTMLFooter('<div style="text-align: center; font-family: tajawal, sans-serif; font-size: 10px; color: #64748b;">صفحة {PAGENO} من {nbpg}</div>');

        $mpdf->WriteHTML($fullHtml, HTMLParserMode::DEFAULT_MODE);

        $refSlug = $contract?->reference ?: ('provider-' . $provider->id);
        $filename = 'rounder-contract-' . $refSlug . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

