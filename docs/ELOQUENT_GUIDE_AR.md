# دليل Eloquent في Rounder

## أين تعدّل؟
- `app/Models`: تعريف الجداول، الحقول المسموح حفظها، تحويل الأنواع والعلاقات.
- `app/Http/Controllers/Admin`: إجراءات لوحة الإدارة والتحقق من المدخلات.
- `app/Http/Controllers/Api/V1`: نقاط API الخاصة بتطبيق الساكن.
- `database/migrations`: بنية MySQL والمفاتيح الأجنبية؛ تستخدم Schema لأن دورها بناء الجداول.
- `database/seeders`: تهيئة القيم المرجعية باستخدام Eloquent.
- `tests/Feature`: التحقق من صلاحيات الوصول ومسارات العمل على MySQL.

## العلاقات الأساسية
- User → memberships → ResidentHome → home. العضوية تخص مستخدماً ووحدة، وليست مجرد مبنى.
- Booking → resident / residentHome / home / service / provider / status / statusHistory / assignment.
- EmergencyRequest → resident / residentHome / emergencyType / status / assignment.
- ProviderProfile → user / contracts؛ ProviderContract → status.
- ServicePackage → billingCycle / translations / includedServices.service.
- ResidentSubscription → residentHome / servicePackage / status / includedServices.
- ProviderCommissionEntry → providerProfile / providerContract / booking أو emergencyRequest / status.

## قواعد العمل
1. لا تمرر `request()->all()` إلى create أو update. مرر البيانات بعد validate فقط، واستخرج مالك الطلب من الحساب أو عضوية الوحدة.
2. سعر الطلب وprice_snapshot وحصص الاشتراك تُحفظ وقت الإنشاء. تعديل الكتالوج لا يعيد تسعير الطلبات القديمة.
3. commission_percent_snapshot يثبت نسبة العمولة المستخدمة وقت احتساب القيد.
4. أبقينا DB::transaction لتجميع التعديلات المرتبطة؛ المعاملة ليست بديلاً عن Eloquent بل تحمي عمليات النماذج من الحفظ الجزئي.
5. السجلات التاريخية ذات created_at فقط تحدد UPDATED_AT = null.
6. التحميل المسبق عبر with يمنع N+1. تستخدم بعض تقارير الأدمن joins على Eloquent Builder للحفاظ على أسماء الأعمدة المعروضة.
7. الاستعلام الجماعي update/delete لا يطلق أحداث كل نموذج. عند إضافة Observer يعتمد على حدث فردي، استخدم نموذجاً محملاً ثم save/delete.
8. User يخفي password وremember_token ويستخدم hashed cast. لا تُعد كلمة المرور ضمن أي Resource.
9. أسماء الجداول موضحة داخل كل Model، والعلاقات تحدد المفاتيح صراحة لتجنب افتراضات التسمية.
10. اختبارات التجهيز قد تستخدم DB لبناء fixtures مستقلة عن الـModels؛ ذلك يساعد في كشف أخطاء تعيين الحقول.

## مثال
```php
$package = ServicePackage::with('includedServices.service', 'translations')
    ->where('is_active', true)->findOrFail($packageId);

// العضوية لا تُقرأ بمعرف غير مقيد بالمستخدم.
$membership = $request->user()->memberships()->findOrFail($membershipId);
```

## التحقق
`vendor/bin/phpunit` يشغل اختبارات العمليات والصلاحيات.
تعمل CI على MySQL 8.4 بقاعدة مستقلة اسمها rounder_test، وتتحقق من migrations وseed وBlade.
لا توجد migration هدامة ضمن إعادة تنظيم Eloquent، ولا تتضمن ربطاً جديداً للموبايل بالـAPI.

## تشغيل الاختبارات محلياً على MySQL
أنشئ قاعدة منفصلة باسم `rounder_test` وأعط حساب الاختبار صلاحية هذه القاعدة. إعداد PHPUnit يفرض اسم قاعدة الاختبار ولا يستخدم قاعدة `rounder` التشغيلية. اضبط DB_HOST وDB_PORT وDB_USERNAME وDB_PASSWORD لحساب الاختبار، ثم شغّل `vendor/bin/phpunit`. لا توجد حاجة إلى SQLite.
