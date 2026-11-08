<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Page;
use App\Modules\Settings\Models\PageTranslation;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'terms' => [
                'ar' => [
                    'title' => 'الشروط والأحكام',
                    'content' => "أهلاً بك في منصة راوندر. يرجى قراءة هذه الشروط والأحكام بعناية قبل استخدام التطبيق والخدمات.\n\n1. قبول الشروط: باستخدامك لخدمات راوندر، فإنك تقر وتوافق على الالتزام بهذه الشروط والسياسات.\n2. الحسابات والتسجيل: يتحمل المستخدم مسؤولية الحفاظ على سرية بيانات حسابه وكلمة المرور الخاصة به.\n3. طلب الخدمات: تخضع جميع طلبات الخدمات وتوفرها لتأكيد مزودي الخدمة المعتمدين.\n4. الإلغاء والاسترجاع: يتم تطبيق سياسات الإلغاء المحددة وفق لوائح المنصة.\n5. التعديلات: تحتفظ إدارة راوندر بحق تعديل أو تحديث هذه الشروط في أي وقت، وسيتم إشعاركم بأي تحديثات جوهرية.",
                ],
                'en' => [
                    'title' => 'Terms & Conditions',
                    'content' => "Welcome to Rounder. Please read these terms and conditions carefully before using our application and services.\n\n1. Acceptance of Terms: By accessing and using Rounder services, you agree to comply with these terms and policies.\n2. Accounts & Registration: Users are responsible for maintaining the confidentiality of their account credentials.\n3. Service Bookings: All service bookings are subject to availability and confirmation by approved service providers.\n4. Cancellations & Refunds: Cancellation policies apply according to platform regulations.\n5. Amendments: Rounder reserves the right to modify these terms at any time.",
                ],
            ],
            'privacy' => [
                'ar' => [
                    'title' => 'سياسة الخصوصية',
                    'content' => "نحن في منصة راوندر نولي أهمية قصوى لخصوصية وسرية بياناتكم الشخصية، ونلتزم بحمايتها وفقاً للأنظمة المعمول بها في المملكة العربية السعودية.\n\n1. البيانات التي نجمعها: نجمع البيانات اللازمة لتقديم الخدمات مثل الاسم، رقم الهاتف، والبيانات المتعلقة بالعقار وموقع تقديم الخدمة.\n2. استخدام البيانات: تُستخدم البيانات لإدارة الحسابات، تنفيذ وتتبع طلبات الصيانة والخدمات، وتحسين تجربة المستخدم.\n3. مشاركة البيانات: لا نقوم ببيع أو مشاركة بياناتكم مع أي أطراف ثالثة إلا مع مزودي الخدمة المعتمدين بالقدر اللازم لإتمام طلباتكم.\n4. أمان البيانات: نطبق أعلى معايير الحماية والأمان التقني لحماية بياناتكم من الوصول غير المصرح به.",
                ],
                'en' => [
                    'title' => 'Privacy Policy',
                    'content' => "At Rounder, we prioritize the privacy and confidentiality of your personal data, and we are committed to protecting it in compliance with applicable regulations in Saudi Arabia.\n\n1. Information We Collect: We collect information necessary to provide our services, such as name, phone number, property details, and service location.\n2. How We Use Information: Information is used to manage accounts, fulfill and track service requests, and enhance user experience.\n3. Information Sharing: We do not sell or share your data with third parties except with approved providers as required to complete your requests.\n4. Data Security: We implement strict technical and security measures to protect your information against unauthorized access.",
                ],
            ],
        ];

        foreach ($pages as $slug => $translations) {
            $page = Page::firstOrCreate(['slug' => $slug], ['is_active' => true]);
            foreach ($translations as $locale => $data) {
                PageTranslation::updateOrCreate(
                    ['page_id' => $page->id, 'locale' => $locale],
                    ['title' => $data['title'], 'content' => $data['content']]
                );
            }
        }
    }
}

