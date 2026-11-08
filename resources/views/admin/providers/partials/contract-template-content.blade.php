@php
    $companySettings = $companySettings ?? \App\Modules\Settings\Models\CompanySetting::current();
    $providerObj = $provider ?? null;
    $contractObj = $contract ?? null;

    $companyNameVal = old('company_name', $providerObj?->company_name ?? '');
    $tradeNameVal = old('trade_name', $providerObj?->trade_name ?? '');
    $companyDisplay = trim($companyNameVal ?: $tradeNameVal) ?: '........................................';

    $crVal = trim((string) old('commercial_registration', $providerObj?->commercial_registration ?? '')) ?: '........................';
    $repVal = trim((string) old('legal_representative', $providerObj?->legal_representative ?? ($providerObj?->user?->name ?? ''))) ?: '........................';
    $repIdVal = trim((string) old('legal_representative_id', $providerObj?->legal_representative_id ?? '')) ?: '........................';
    $phoneVal = trim((string) old('phone', $providerObj?->user?->phone ?? '')) ?: '........................';
    $emailVal = trim((string) old('email', $providerObj?->user?->email ?? '')) ?: '........................';

    $rawStartsOn = old('starts_on', $contractObj?->starts_on?->format('Y-m-d') ?? now()->toDateString());
    try {
        $carbonDate = \Illuminate\Support\Carbon::parse($rawStartsOn);
        $startsOnFormatted = $carbonDate->format('d/m/Y') . 'م';
        $arabicDays = [
            'Sunday' => 'الأحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];
        $dayName = $arabicDays[$carbonDate->format('l')] ?? 'الأربعاء';
    } catch (\Throwable $e) {
        $startsOnFormatted = now()->format('d/m/Y') . 'م';
        $dayName = 'الأربعاء';
    }

    $contractRef = $contractObj?->reference ?? 'يُصدر تلقائياً عند الحفظ';
    $serviceComm = old('service_commission_percent', $contractObj?->service_commission_percent ?? 15);
    $emergencyComm = old('emergency_commission_percent', $contractObj?->emergency_commission_percent ?? 20);

    $firstPartyTradeName = $companySettings->trade_name ?: 'شركة راوندر لخدمات التشغيل والصيانة';
    $firstPartyCr = $companySettings->cr_number ?: '—';
    $firstPartyTaxOrTm = $companySettings->trademark_registration ?: ($companySettings->tax_number ?: '—');
    $firstPartyTaxOrTmLabel = $companySettings->trademark_registration ? 'تسجيل العلامة التجارية' : 'الرقم الضريبي';
    $firstPartyRep = $companySettings->legal_representative ?: '—';
    $firstPartyTitle = $companySettings->representative_title ?: 'ممثل المنصة';
    $firstPartyStampUrl = $companySettings->stamp_url;

    $secondPartyStampUrl = null;
    if ($providerObj && $providerObj->id && $providerObj->company_seal) {
        $sealExt = strtolower(pathinfo($providerObj->company_seal, PATHINFO_EXTENSION));
        if (in_array($sealExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $secondPartyStampUrl = route('admin.providers.documents.show', [$providerObj->id, 'company_seal']);
        }
    }
@endphp
<div class="rounder-contract-doc" dir="rtl" style="font-family: 'Tajawal', sans-serif; color: #1e293b; line-height: 1.75; background: #ffffff; border: 1px solid #dce6e3; border-radius: 16px; padding: 24px; max-width: 100%;">
    {{-- Header Banner --}}
    <table width="100%" style="background-color: #02333E; color: #ffffff; border-radius: 14px; margin-bottom: 22px; border-collapse: separate; padding: 18px 22px;">
        <tr>
            <td align="right" valign="middle" style="width: 60%; color: #ffffff;">
                <div style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.3px;">
                    <span style="color: #FB822F;">راوندر</span> للصيانة
                </div>
                <div style="font-size: 13px; color: #cbd5e1; margin-top: 2px;">منصة الصيانة الموثوقة</div>
            </td>
            <td align="left" valign="middle" style="width: 40%; text-align: left; color: #ffffff;">
                <div style="font-size: 13px; color: #e2e8f0;">عقد المؤسسات والشركات</div>
                <div style="font-size: 13px; font-weight: 700; color: #FB822F; margin-top: 3px;">النسخة النهائية | مزوّد خدمة</div>
            </td>
        </tr>
    </table>

    {{-- Main Contract Title --}}
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="font-size: 26px; font-weight: 800; color: #02333E; margin: 0 0 8px 0;">عقد تقديم خدمات الصيانة</h2>
        <div>
            <span style="background-color: #fff3eb; color: #d96416; border: 1px solid #fbd3b6; border-radius: 20px; padding: 4px 18px; font-size: 13px; font-weight: 700;">
                &nbsp;خاص بالمؤسسات والشركات&nbsp;
            </span>
        </div>
    </div>

    {{-- Meta Box (City, Contract Ref, Signing Date) --}}
    <table width="100%" style="border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 18px; border-collapse: collapse; background-color: #f8fafc;">
        <tr>
            <td width="33.3%" style="padding: 12px 16px; border-left: 1px solid #e2e8f0; text-align: right;">
                <div style="font-size: 12px; color: #64748b;">المدينة</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">المملكة العربية السعودية</div>
            </td>
            <td width="33.3%" style="padding: 12px 16px; border-left: 1px solid #e2e8f0; text-align: right;">
                <div style="font-size: 12px; color: #64748b;">رقم العقد</div>
                <div style="font-size: 14px; font-weight: 700; color: #02333E; margin-top: 2px;"><span data-contract-field="contract_reference">{{ $contractRef }}</span></div>
            </td>
            <td width="33.3%" style="padding: 12px 16px; text-align: right;">
                <div style="font-size: 12px; color: #64748b;">تاريخ التوقيع</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;"><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></div>
            </td>
        </tr>
    </table>

    {{-- Preamble --}}
    <p style="font-size: 14px; color: #1e293b; text-align: justify; margin: 0 0 18px 0;">
        إنه في يوم <strong><span data-contract-field="day_name">{{ $dayName }}</span></strong> بتاريخ <strong><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></strong> وهما بكامل أهليتهما الشرعية والنظامية، تم إبرام هذا العقد بين الأطراف أدناه، الخاص بتنظيم العلاقة بين <strong>منصة راوندر للصيانة</strong> والمؤسسات والشركات المزوّدة لخدمات الصيانة المنزلية:
    </p>

    {{-- First Party Card (المنصة - الطرف الأول) --}}
    <div style="border: 1px solid #dce6e3; border-radius: 12px; overflow: hidden; margin-bottom: 18px;">
        <div style="background-color: #02333E; color: #ffffff; padding: 10px 16px; font-weight: 700; font-size: 15px;">
            <span>المنصة</span>
            <span style="background-color: #FB822F; color: #ffffff; font-size: 11px; padding: 2px 10px; border-radius: 12px; margin-right: 8px;">&nbsp;الطرف الأول&nbsp;</span>
        </div>
        <table width="100%" style="border-collapse: collapse; background-color: #ffffff; font-size: 13.5px;">
            <tr>
                <td colspan="2" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6;">
                    <span style="color: #64748b;">الاسم التجاري:</span>
                    <strong style="color: #0f172a; margin-right: 6px;">راوندر للصيانة — {{ $firstPartyTradeName }}</strong>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6; border-left: 1px solid #eef2f6;">
                    <span style="color: #64748b;">السجل التجاري:</span>
                    <strong style="color: #0f172a; margin-right: 6px;">{{ $firstPartyCr }}</strong>
                </td>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6;">
                    <span style="color: #64748b;">{{ $firstPartyTaxOrTmLabel }}:</span>
                    <strong style="color: #0f172a; margin-right: 6px;">{{ $firstPartyTaxOrTm }}</strong>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px 16px; border-left: 1px solid #eef2f6;">
                    <span style="color: #64748b;">الممثل القانوني:</span>
                    <strong style="color: #0f172a; margin-right: 6px;">{{ $firstPartyRep }}</strong>
                </td>
                <td width="50%" style="padding: 10px 16px;">
                    <span style="color: #64748b;">الصفة:</span>
                    <strong style="color: #0f172a; margin-right: 6px;">{{ $firstPartyTitle }}</strong>
                </td>
            </tr>
        </table>
    </div>

    {{-- Second Party Card (مزوّد الخدمة - الطرف الثاني) --}}
    <div style="border: 1px solid #dce6e3; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
        <div style="background-color: #02333E; color: #ffffff; padding: 10px 16px; font-weight: 700; font-size: 15px;">
            <span>مزوّد الخدمة (شركة / مؤسسة)</span>
            <span style="background-color: #FB822F; color: #ffffff; font-size: 11px; padding: 2px 10px; border-radius: 12px; margin-right: 8px;">&nbsp;الطرف الثاني&nbsp;</span>
        </div>
        <table width="100%" style="border-collapse: collapse; background-color: #ffffff; font-size: 13.5px;">
            <tr>
                <td colspan="2" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6;">
                    <span style="color: #64748b;">اسم الشركة / المؤسسة:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="company_display">{{ $companyDisplay }}</span></strong>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6; border-left: 1px solid #eef2f6;">
                    <span style="color: #64748b;">السجل التجاري:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="commercial_registration">{{ $crVal }}</span></strong>
                </td>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6;">
                    <span style="color: #64748b;">الممثل القانوني:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="legal_representative">{{ $repVal }}</span></strong>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6; border-left: 1px solid #eef2f6;">
                    <span style="color: #64748b;">رقم هوية الممثل:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="legal_representative_id">{{ $repIdVal }}</span></strong>
                </td>
                <td width="50%" style="padding: 10px 16px; border-bottom: 1px solid #eef2f6;">
                    <span style="color: #64748b;">رقم التواصل:</span>
                    <strong style="color: #0f172a; margin-right: 6px;" dir="ltr"><span data-contract-field="phone">{{ $phoneVal }}</span></strong>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px 16px; border-left: 1px solid #eef2f6;">
                    <span style="color: #64748b;">البريد الإلكتروني:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="email">{{ $emailVal }}</span></strong>
                </td>
                <td width="50%" style="padding: 10px 16px;">
                    <span style="color: #64748b;">تاريخ بدء الاشتراك:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></strong>
                </td>
            </tr>
        </table>
    </div>

    {{-- Section Header: البنود والشروط --}}
    <div style="background-color: #02333E; color: #ffffff; padding: 12px 18px; border-radius: 12px; font-weight: 800; font-size: 16px; margin-bottom: 16px; border-right: 5px solid #FB822F;">
        البنود والشروط
    </div>

    {{-- Clauses 1 to 21 --}}
    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١&nbsp;</span>
            الجودة والمعايير المهنية
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يتعهد الطرف الثاني بتوفير أعلى معايير الجودة في الخدمات المقدمة، وتُعرّف الخدمة المقبولة بأنها: (أ) إتمام العمل وفق المواصفات المتفق عليها. (ب) إبلاغ العميل بالتكلفة الإجمالية قبل البدء. (ج) الالتزام بالوقت المتفق عليه مع العميل داخل التطبيق، وأي تأخّر يتجاوز 30 دقيقة دون إشعار مسبق يُعد مخالفة. (د) الحفاظ على متوسط تقييم لا يقل عن 3 من 5 محسوبًا على آخر 10 طلبات. ويتحمل الطرف الثاني المسؤولية الكاملة عن أداء جميع منسوبيه.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٢&nbsp;</span>
            نسبة المنصة ومدة العقد
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يستحق الطرف الأول نسبة <strong><span data-contract-field="service_commission_percent">{{ $serviceComm }}</span>%</strong> من قيمة كل خدمة منفّذة عبر المنصة خلال أول (6) أشهر من تاريخ تفعيل كل مدينة، ونسبة <strong><span data-contract-field="emergency_commission_percent">{{ $emergencyComm }}</span>%</strong> بعد انقضائها أو لطلبات الطوارئ، وتُحتسب فترة الأشهر الستة لكل مدينة بشكل مستقل. وتُحتسب النسبة على قيمة الخدمة فقط، ولا تشمل قطع الغيار الموثّقة في المنصة. ومدة العقد سنة ميلادية تبدأ من تاريخ توقيعه، وتتجدد تلقائيًا ما لم يُبلّغ أحد الطرفين الآخر برغبته في عدم التجديد قبل انتهائها بـ 30 يومًا، ويُعد الشهر الأول من تاريخ التفعيل فترة تجربة يحق فيها لأي طرف إنهاء العقد بإشعار كتابي دون غرامة. وتُخصم نسبة المنصة والمخالفات مباشرةً من المحفظة الإلكترونية.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٣&nbsp;</span>
            الحد الائتماني (التأمين)
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يتعهد الطرف الثاني بدفع الحد الائتماني المحدد: ........................ ريال قبل الشروع في استقبال الطلبات. ويُحدّد هذا المبلغ يدويًا من قِبل الطرف الأول بناءً على تقييم كل مؤسسة على حدة وحجم نشاطها. ويُستخدم لتغطية الأضرار والمخالفات المثبتة، ولا يُرد إلا عند انتهاء العقد وتسوية كامل الحقوق وغياب أي مخالفات معلقة.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٤&nbsp;</span>
            التزامات الطرف الأول
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يتعهد الطرف الأول بتزويد الطرف الثاني بتسعيرات الخدمات المعتمدة، وفتح حساب مؤسسي على المنصة، وتخصيص مدير حساب، وإيداع مستحقات الطرف الثاني في المحفظة بعد خصم نسبة المنصة كل أسبوعين (14 يومًا)، تبدأ أول دورة منها من تاريخ توقيع هذا العقد، وإشعار الطرف الثاني كتابيًا قبل 30 يومًا من أي تعديل في الأسعار أو المخالفات.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٥&nbsp;</span>
            منظومة المخالفات والتظلم
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            (أ) يُقر الطرف الثاني بأنه اطّلع على منظومة المخالفات الواردة في ملحق (أ) من هذا العقد كاملةً وقَبِلها، ويضمن التزام جميع منسوبيه بها، وتُخصم قيم المخالفات مباشرةً من المحفظة عند ثبوتها. (ب) يُمنح الطرف الثاني حق التظلم خلال 14 يوم عمل من تاريخ الخصم، وتبتّ المنصة في التظلم خلال 7 أيام عمل، ويُعاد المبلغ فورًا في حال ثبوت الخطأ. (ج) ينطبق حق التظلم — 7 أيام قبل تفعيل أي إجراء من إجراءات المصادرة أو إنهاء العقد — في جميع بنود هذا العقد دون استثناء.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٦&nbsp;</span>
            إخلاء مسؤولية الطرف الأول
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يُخلي الطرف الأول مسؤوليته عن أي أضرار تقع على الطرف الثاني أو منسوبيه أثناء تقديم الخدمة. وفي حال إثارة أي مطالبة ضد الطرف الأول نتيجة أفعال الطرف الثاني أو منسوبيه، يتحمل الطرف الثاني كامل التكاليف القانونية والتعويضية.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٧&nbsp;</span>
            مسؤولية الأضرار والضمان
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يتحمل الطرف الثاني كامل المسؤولية القانونية والمالية عن جميع الأضرار الواقعة في منزل أو ممتلكات العميل جرّاء إهمال أي من منسوبيه، دون حد أعلى للتعويض. ويلتزم بتنفيذ الإصلاح مجانًا حتى انتهاء مدة الضمان. وفي حال تعذّر الإصلاح أو إثبات الإهمال، تُقدّر راوندر قيمة الضرر وتُلزم الطرف الثاني بالتعويض، وقرار راوندر نهائي.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٨&nbsp;</span>
            الإخطار المسبق بالتكلفة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يجب على الطرف الثاني وجميع منسوبيه إبلاغ العميل بالأعطال المكتشفة وتكلفتها الإجمالية (شغل اليد وقطع الغيار) قبل الشروع في العمل، وفي حال المخالفة تُعتبر الخدمة كاملةً مجانية للعميل ويُسجّل ذلك مخالفة رسمية على حساب الطرف الثاني.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٩&nbsp;</span>
            حظر تغيير طريقة الدفع المعتمدة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            في حال حدّد العميل طريقة الدفع أونلاين عبر منصة راوندر، يُحظر على الطرف الثاني أو أي من منسوبيه طلب الدفع نقدًا (كاش) بديلًا عن ذلك. وما لم يُرسل العميل تغيير طريقة الدفع رسميًا عبر منصة راوندر، يُعد أي طلب نقدي من الطرف الثاني مخالفة مباشرة يترتب عليها غرامة مالية تحدد إدارة المنصة قيمتها فور ثبوت المخالفة.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٠&nbsp;</span>
            حظر التعامل خارج المنصة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يُحظر على الطرف الثاني وجميع منسوبيه توجيه عملاء المنصة للتعامل خارج التطبيق أو تحويل أي طلبات خارجيًا. وفي حال ثبوت المخالفة، يُمنح الطرف الثاني 7 أيام عمل للتظلم، وبعدها يحق للطرف الأول إنهاء العقد فورًا ومصادرة مبلغ التأمين.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١١&nbsp;</span>
            الضمان بعد الخدمة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يلتزم الطرف الثاني بتقديم ضمان عام على جميع الخدمات المنجزة. وفي حال تعذّر الإصلاح أو إثبات الإهمال، تُقدّر راوندر قيمة الضرر وتُلزم الطرف الثاني بالتعويض. ولا يشمل الضمان الأعطال الناتجة عن إساءة الاستخدام أو حوادث خارجية.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٢&nbsp;</span>
            نطاق التغطية ومراحل التشغيل
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يبدأ تشغيل الطرف الثاني في المدن والمناطق المعتمدة اعتبارًا من تاريخ التفعيل في المنصة الذي يُبلّغ به الطرف الثاني كتابيًا بعد استكمال تسجيل فنييه. وتُضاف المناطق والمدن الأخرى لاحقًا حسب ما يتفق عليه الطرفان كتابيًا، بملحق يحدد المدن والتخصصات وموعد التشغيل، ويُعد جزءًا لا يتجزأ من هذا العقد.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٣&nbsp;</span>
            تعديل الأسعار والشروط
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يحق للطرف الأول تعديل أسعار الخدمات أو شروطها بشرط إشعار الطرف الثاني كتابيًا قبل 30 يومًا. وللطرف الثاني حق إنهاء العقد دون غرامة خلال فترة الإشعار إذا لم يوافق، والاستمرار بعد انتهاء الإشعار يُعد قبولًا ضمنيًا.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٤&nbsp;</span>
            حقوق الملكية الفكرية والبيانات
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            جميع البيانات المولّدة عبر المنصة — بما فيها تقييمات العملاء وسجلات الطلبات والإحصاءات — هي ملك حصري للطرف الأول، ولا يحق للطرف الثاني أو منسوبيه توظيف هذه البيانات لأغراض تجارية خارج نطاق الخدمة.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٥&nbsp;</span>
            السرية وحماية البيانات
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يلتزم الطرف الثاني بالحفاظ على سرية بيانات عملاء المنصة وعدم إفشائها وفق نظام حماية البيانات الشخصية السعودي، وتستمر هذه الالتزامات سارية لمدة 3 سنوات بعد انتهاء العقد.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٦&nbsp;</span>
            القوة القاهرة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            لا يُعد أي من الطرفين مخلًّا بالتزاماته إذا حال دون تنفيذها سبب قاهر خارج عن إرادته. ويُخطر الطرف المتضرر الطرف الآخر فورًا وخطيًا، ويعمل الطرفان على إيجاد حل بديل خلال 30 يومًا، وإلا جاز لكل منهما إنهاء العقد دون غرامة.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٧&nbsp;</span>
            التوثيق والفوترة
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يلتزم الطرف الثاني بإصدار فواتير رسمية عن كل خدمة مكتملة والحفاظ على سجلاته المالية وفق الأنظمة الضريبية السعودية. وفي حال طلب الطرف الأول وثائق مالية لأغراض التدقيق، يلتزم الطرف الثاني بتقديمها خلال 7 أيام عمل.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٨&nbsp;</span>
            آلية التوقيع الإلكتروني
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يُعتمد في توقيع هذا العقد وحفظه نظام التوقيع الإلكتروني المعتمد عبر منصة راوندر، ويُرسل رابط التوقيع للممثل القانوني للطرف الثاني عبر واتساب أو الحساب المسجّل في هذا العقد. ويُعد التوقيع الإلكتروني المنجز عبر منصة راوندر معادلًا للتوقيع الخطي وملزمًا قانونيًا لكلا الطرفين وفق الأنظمة السارية في المملكة العربية السعودية.
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;١٩&nbsp;</span>
            الإفصاح عن التغييرات المؤسسية
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يلتزم الطرف الثاني بإخطار الطرف الأول فورًا وخطيًا خلال 7 أيام عن أي تغيير في: الممثل القانوني، أو ملكية المؤسسة، أو السجل التجاري. ويُعتبر التأخر في الإخطار مخالفة تستوجب الغرامات المحددة في ملحق (أ).
        </p>
    </div>

    <div style="padding: 4px 0; border-bottom: 1px solid #edf2f7; margin-bottom: 12px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٢٠&nbsp;</span>
            إنهاء العقد وتسوية الحقوق
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يحق لأي من الطرفين إنهاء العقد بإشعار كتابي مسبق مدته 30 يومًا. ويُوقف الحساب الإلكتروني فور صدور إشعار الإنهاء، وتُسوّى المستحقات المالية لمزوّد الخدمة بعد مرور شهر كامل و10 أيام عمل من تاريخ إشعار الإنهاء. وفي حال الإنهاء بسبب مخالفة جسيمة، يحق للطرف الأول إنهاء العقد فورًا ومصادرة مبلغ التأمين بعد منح الطرف الثاني 7 أيام عمل للتظلم.
        </p>
    </div>

    <div style="padding: 4px 0; margin-bottom: 24px;">
        <h3 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 6px 0;">
            <span style="background-color: #fff3eb; color: #FB822F; border-radius: 6px; padding: 2px 8px; margin-left: 6px; font-size: 13px;">&nbsp;٢١&nbsp;</span>
            الاختصاص القضائي وحل النزاعات
        </h3>
        <p style="font-size: 13.5px; color: #334155; margin: 0 0 8px 0; text-align: justify;">
            يخضع هذا العقد للأنظمة المعمول بها في المملكة العربية السعودية، ويُحاول حل أي خلاف بالطرق الودية خلال 15 يوم عمل. وفي حال تعذّر ذلك تُحال النزاعات إلى المحاكم السعودية المختصة في مدينة الرياض وتكون أحكامها نهائية وملزمة للطرفين.
        </p>
    </div>

    {{-- Annex A: ملحق (أ) — منظومة المخالفات --}}
    <div style="background-color: #02333E; color: #ffffff; padding: 12px 18px; border-radius: 12px; font-weight: 800; font-size: 16px; margin-bottom: 12px; border-right: 5px solid #FB822F;">
        ملحق (أ) — منظومة المخالفات
    </div>
    <p style="font-size: 13.5px; color: #334155; margin: 0 0 14px 0; text-align: justify;">
        يُقر الطرف الثاني بأنه اطّلع على منظومة المخالفات المعتمدة من منصة راوندر الواردة في الجدول أدناه كاملةً، وأنه يقبلها ويلتزم بها، ويضمن التزام جميع منسوبيه العاملين على المنصة بها. وتُعد هذه المنظومة جزءًا لا يتجزأ من هذا العقد بنفس قوته القانونية.
    </p>

    <table width="100%" style="border-collapse: collapse; border: 1px solid #dce6e3; border-radius: 10px; overflow: hidden; margin-bottom: 16px; font-size: 13px;">
        <thead>
            <tr style="background-color: #02333E; color: #ffffff;">
                <th style="padding: 10px 14px; text-align: right; width: 48%; color: #ffffff;">المخالفة</th>
                <th style="padding: 10px 14px; text-align: right; width: 52%; color: #ffffff;">الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #ffffff;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">العمل دون إبلاغ العميل بالتكلفة مسبقًا</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fff3eb; color: #d96416; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;الخدمة مجانية&nbsp;</span>
                    <span> + خصم من المحفظة</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #f8fafc;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">تحويل العميل للتعامل خارج المنصة</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;مصادرة التأمين&nbsp;</span>
                    <span> + إنهاء فوري بعد 7 أيام تظلّم</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #ffffff;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">استخدام قطع غيار مغشوشة أو غير مطابقة</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;رفع للجهات القضائية&nbsp;</span>
                    <span> + تعويض العميل</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #f8fafc;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">عدم الالتزام بالوقت المتفق عليه (تجاوز 30 دقيقة دون إشعار)</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;إنذار رسمي&nbsp;</span>
                    <span> + خصم من المحفظة</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #ffffff;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">التلاعب بالتقييمات أو الطلبات</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;مصادرة التأمين&nbsp;</span>
                    <span> + إنهاء فوري بعد 7 أيام تظلّم</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #f8fafc;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">إفشاء بيانات العملاء</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;ملاحقة قانونية&nbsp;</span>
                    <span> + إنهاء فوري</span>
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eef2f6; background-color: #ffffff;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">الغياب دون إشعار بعد قبول الطلب</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;إنذار رسمي&nbsp;</span>
                    <span> + خصم من المحفظة</span>
                </td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td style="padding: 10px 14px; font-weight: 700; color: #0f172a;">عدم إخطار الطرف الأول بتغيير الممثل القانوني خلال 7 أيام</td>
                <td style="padding: 10px 14px;">
                    <span style="background-color: #fff3eb; color: #d96416; padding: 2px 8px; border-radius: 6px; font-weight: 700;">&nbsp;تجميد الحساب&nbsp;</span>
                    <span> + غرامة مالية</span>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Annex A Acknowledgment Box --}}
    <div style="border: 1px solid #dce6e3; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; background-color: #f8fafc;">
        <div style="font-weight: 800; color: #02333E; font-size: 14px; margin-bottom: 10px;">توقيع الممثل القانوني للطرف الثاني إقرارًا بالاطلاع والقبول</div>
        <table width="100%" style="border-collapse: collapse; font-size: 13px;">
            <tr>
                <td width="50%" style="padding: 8px; border-bottom: 1px solid #cbd5e1;">
                    <span style="color: #64748b;">التاريخ:</span>
                    <strong style="color: #0f172a; margin-right: 6px;"><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></strong>
                </td>
                <td width="50%" style="padding: 8px; border-bottom: 1px solid #cbd5e1;">
                    <span style="color: #64748b;">التوقيع:</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Required Documents Section --}}
    <div style="background-color: #02333E; color: #ffffff; padding: 10px 18px; border-radius: 12px; font-weight: 800; font-size: 15px; margin-bottom: 12px;">
        المستندات المطلوبة
    </div>
    <table width="100%" style="border-collapse: separate; border-spacing: 8px; margin-bottom: 24px; font-size: 13px;">
        <tr>
            <td width="50%" style="border: 1px solid #dce6e3; border-radius: 10px; padding: 10px 14px; background-color: #ffffff;">
                <strong style="color: #0f172a;">السجل التجاري</strong>
                <span style="background-color: #dcfce7; color: #15803d; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px;">&nbsp;مطلوب&nbsp;</span>
            </td>
            <td width="50%" style="border: 1px solid #dce6e3; border-radius: 10px; padding: 10px 14px; background-color: #ffffff;">
                <strong style="color: #0f172a;">هوية الممثل القانوني</strong>
                <span style="background-color: #dcfce7; color: #15803d; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px;">&nbsp;مطلوب&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td width="50%" style="border: 1px solid #dce6e3; border-radius: 10px; padding: 10px 14px; background-color: #ffffff;">
                <strong style="color: #0f172a;">ملحق (أ) موقّع</strong>
                <span style="background-color: #dcfce7; color: #15803d; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px;">&nbsp;مطلوب&nbsp;</span>
            </td>
            <td width="50%" style="border: 1px solid #dce6e3; border-radius: 10px; padding: 10px 14px; background-color: #ffffff;">
                <strong style="color: #0f172a;">رخصة البلدية</strong>
                <span style="background-color: #f1f5f9; color: #475569; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-right: 8px;">&nbsp;إن توفرت&nbsp;</span>
            </td>
        </tr>
    </table>

    {{-- Signatures Section --}}
    <div style="background-color: #02333E; color: #ffffff; padding: 12px 18px; border-radius: 12px; font-weight: 800; font-size: 16px; margin-bottom: 16px; border-right: 5px solid #FB822F;">
        التوقيعات
    </div>

    {{-- First Party Signature Box --}}
    <div style="border: 1px solid #dce6e3; border-radius: 12px; padding: 16px; margin-bottom: 16px; background-color: #f8fafc;">
        <h4 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 12px 0;">الطرف الأول — المنصة</h4>
        <table width="100%" style="border-collapse: collapse; font-size: 13.5px;">
            <tr>
                <td width="50%" style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 12px;">الممثل القانوني</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $firstPartyRep }}</div>
                </td>
                <td width="50%" style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 12px;">الصفة</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $firstPartyTitle }}</div>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px; vertical-align: middle;">
                    <div style="color: #64748b; font-size: 12px;">التوقيع والختم الرسمي</div>
                    @if($firstPartyStampUrl)
                        <div style="margin-top: 6px;">
                            <img src="{{ $firstPartyStampUrl }}" alt="ختم المنصة" style="max-height: 95px; max-width: 160px;">
                        </div>
                    @else
                        <div style="height: 55px;"></div>
                    @endif
                </td>
                <td width="50%" style="padding: 10px; vertical-align: middle;">
                    <div style="color: #64748b; font-size: 12px;">التاريخ</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 4px;"><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Second Party Signature Box --}}
    <div style="border: 1px solid #dce6e3; border-radius: 12px; padding: 16px; margin-bottom: 20px; background-color: #f8fafc;">
        <h4 style="font-size: 15px; font-weight: 800; color: #02333E; margin: 0 0 12px 0;">الطرف الثاني — الشركة / المؤسسة</h4>
        <table width="100%" style="border-collapse: collapse; font-size: 13.5px;">
            <tr>
                <td width="50%" style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 12px;">اسم الممثل القانوني</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;"><span data-contract-field="legal_representative">{{ $repVal }}</span></div>
                </td>
                <td width="50%" style="padding: 8px 10px; border-bottom: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 12px;">الصفة</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">المفوّض</div>
                </td>
            </tr>
            <tr>
                <td width="50%" style="padding: 10px; vertical-align: middle;">
                    <div style="color: #64748b; font-size: 12px;">التوقيع والختم الرسمي</div>
                    <div data-contract-field="second_party_seal" style="min-height: 55px; margin-top: 6px;">
                        @if($secondPartyStampUrl)
                            <img src="{{ $secondPartyStampUrl }}" alt="ختم الطرف الثاني" style="max-height: 95px; max-width: 160px;">
                        @endif
                    </div>
                </td>
                <td width="50%" style="padding: 10px; vertical-align: middle;">
                    <div style="color: #64748b; font-size: 12px;">التاريخ</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 4px;"><span data-contract-field="starts_on_formatted">{{ $startsOnFormatted }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div style="text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 12px;">
        <div>حُرّر هذا العقد من نسختين أصليتين باللغة العربية، بيد كل طرف نسخة للعمل بموجبها.</div>
        <div style="margin-top: 4px; font-weight: 700; color: #02333E;">{{ $firstPartyTradeName }} | س.ت: {{ $firstPartyCr }}</div>
        <div style="margin-top: 2px;">rounder-sa.com | info@rounder-sa.com | المملكة العربية السعودية</div>
    </div>
</div>
