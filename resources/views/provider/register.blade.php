<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="سجل كمزود خدمة لدى راوندر وقدم خدماتك للعملاء.">
    <title>تسجيل مزود خدمة · راوندر</title>
    <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('assets/fonts/tajawal-bold.ttf') }}" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        body.provider-register-page { background: #f5f8f7; }
        .provider-register-main { padding-block: 48px 64px; }
        .provider-register-card {
            max-width: 760px;
            margin-inline: auto;
            padding: clamp(22px, 5vw, 42px);
            border: 1px solid #e0eae7;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 12px 36px rgba(0, 52, 62, .07);
        }
        .provider-register-heading { margin-bottom: 27px; }
        .provider-register-brand { color: var(--rounder-petrol); font-size: 1.1rem; font-weight: 800; }
        .provider-register-brand span { color: var(--rounder-orange); }
        .provider-register-heading h1 { margin: 8px 0 5px; font-size: clamp(1.35rem, 3vw, 1.7rem); font-weight: 800; }
        .provider-register-heading p { color: var(--rounder-muted); margin: 0; font-size: .9rem; }
        .provider-register-form .form-label { margin-bottom: 6px; color: #35565a; font-size: .82rem; font-weight: 700; }
        .provider-register-form .form-control,
        .provider-register-form .form-select { min-height: 45px; background-color: #fff; }
        .provider-service-heading { margin-bottom: 10px; }
        .provider-service-heading .form-label { margin-bottom: 3px; }
        .provider-service-hint { color: var(--rounder-muted); font-size: .75rem; }
        .provider-service-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 9px; }
        .provider-service-option:last-child:nth-child(3n + 1) { grid-column: 2; }
        .provider-service-option {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 48px;
            margin: 0;
            padding: 10px 12px;
            border: 1px solid #e0eae7;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease;
        }
        .provider-service-option:hover { border-color: #a6c4bd; background: #fbfdfc; }
        .provider-service-option:has(input:checked) {
            border-color: var(--rounder-teal);
            background: #f0f7f5;
            box-shadow: inset 0 0 0 1px var(--rounder-teal);
        }
        .provider-service-option .form-check-input { float: none; flex: 0 0 auto; margin: 0; }
        .provider-service-option .form-check-label { flex: 1; color: var(--rounder-ink); font-size: .79rem; }
        .provider-documents {
            padding: 18px;
            border: 1px solid #e0eae7;
            border-radius: 12px;
            background: #fbfdfc;
        }
        .provider-documents-heading { margin-bottom: 14px; }
        .provider-documents-heading h2 { margin: 0 0 4px; color: var(--rounder-ink); font-size: .95rem; font-weight: 800; }
        .provider-documents-heading p { margin: 0; color: var(--rounder-muted); font-size: .78rem; }
        .provider-register-form input[type="file"] { padding: 7px 10px; font-size: .76rem; }
        .provider-register-actions { margin-top: 25px; padding-top: 19px; border-top: 1px solid #edf2f0; }
        .provider-register-actions .btn { min-height: 45px; padding-inline: 20px; font-size: .84rem; }
        @media (max-width: 575.98px) {
            .provider-register-main { padding-block: 28px 40px; }
            .provider-register-card { border-radius: 16px; }
            .provider-register-heading { margin-bottom: 21px; }
            .provider-service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
            .provider-service-option:last-child:nth-child(3n + 1) { grid-column: auto; }
            .provider-service-option { min-height: 46px; gap: 7px; padding: 9px; }
            .provider-service-option .form-check-label { font-size: .75rem; }
            .provider-documents { padding: 14px; }
            .provider-register-actions { flex-direction: column; }
            .provider-register-actions .btn { width: 100%; }
        }
    </style>
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}" defer></script>
</head>
<body class="provider-register-page">
<a class="visually-hidden-focusable skip-link" href="#main">تخطّ إلى المحتوى</a>
<svg class="sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2 3 6v6c0 6 9 10 9 10s9-4 9-10V6zM8 12l3 3 5-6"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M20 12H4m6-6-6 6 6 6"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
</svg>

<header class="site-header sticky-top">
    <nav class="navbar navbar-expand-lg" aria-label="القائمة الرئيسية" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}" aria-label="راوندر — الرئيسية">
                <img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" width="2172" height="724" alt="rounder — راوندر">
            </a>
            <div class="site-actions d-flex align-items-center gap-2 gap-lg-3 order-lg-3">
                @auth
                    <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.dashboard') }}">
                        <svg class="icon" aria-hidden="true" style="width:14px;height:14px"><use href="#i-grid"></use></svg>
                        <span>لوحة التحكم</span>
                    </a>
                @else
                    <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.login') }}">
                        <svg class="icon" aria-hidden="true" style="width:14px;height:14px"><use href="#i-shield"></use></svg>
                        <span>دخول الإدارة</span>
                    </a>
                @endauth
                <a class="btn btn-link header-partner d-none d-xl-inline-flex" href="{{ route('provider.register') }}">
                    انضم كمزود خدمة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
                </a>
                <a class="btn btn-primary btn-sm" href="{{ route('provider.login') }}">دخول مزود الخدمة</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"
                    aria-controls="main-nav" aria-expanded="false" aria-label="فتح القائمة">
                    <svg class="icon" aria-hidden="true"><use href="#i-menu"></use></svg>
                </button>
            </div>
            <div class="collapse navbar-collapse order-lg-2" id="main-nav">
                <ul class="navbar-nav mx-lg-auto gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#services">خدماتنا</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#business">للأعمال</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#how">كيف نعمل</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#faq">الأسئلة الشائعة</a></li>
                    <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>
                    @guest
                        <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('admin.login') }}">دخول الإدارة</a></li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="main" class="provider-register-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="provider-register-card">
                    <div class="provider-register-heading">
                        <div class="provider-register-brand">rounder <span>مزود الخدمة</span></div>
                        <h1>طلب الانضمام كمزود خدمة</h1>
                        <p class="text-secondary mb-0">يراجع فريق الإدارة الطلب قبل تفعيل الحساب.</p>
                    </div>
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    <form class="provider-register-form" method="post" action="{{ route('provider.register.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="company_name">اسم الشركة</label>
                                <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required maxlength="160">
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="trade_name">الاسم التجاري</label>
                                <input class="form-control @error('trade_name') is-invalid @enderror" id="trade_name" name="trade_name" value="{{ old('trade_name') }}" required maxlength="160">
                                @error('trade_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="provider_type">نوع مزود الخدمة</label>
                                <select class="form-select @error('provider_type') is-invalid @enderror" id="provider_type" name="provider_type" required>
                                    <option value="">اختر نوع المزود</option>
                                    @foreach(['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'] as $type)
                                        <option value="{{ $type }}" @selected(old('provider_type') === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('provider_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="years_of_experience">سنوات الخبرة</label>
                                <input class="form-control @error('years_of_experience') is-invalid @enderror" type="number" id="years_of_experience" name="years_of_experience" min="0" max="100" value="{{ old('years_of_experience') }}" required>
                                @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="provider-service-heading">
                                    <label class="form-label">التخصصات والخدمات</label>
                                    <div class="provider-service-hint">يمكنك اختيار أكثر من خدمة</div>
                                </div>
                                <div class="provider-service-grid">
                                    @foreach($services as $service)
                                        @php($serviceName = $service->translations->firstWhere('locale', 'ar')?->name ?? $service->code)
                                        <label class="provider-service-option">
                                            <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('service_ids', [])))>
                                            <span class="form-check-label">{{ $serviceName }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('service_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('service_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="phone">رقم الهاتف</label>
                                <input class="form-control @error('phone') is-invalid @enderror" dir="ltr" type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="05XXXXXXXX" pattern="05[0-9]{8}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="password">كلمة المرور</label>
                                <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="password_confirmation">تأكيد كلمة المرور</label>
                                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                            </div>
                            <div class="col-12">
                                <section class="provider-documents" aria-labelledby="provider-documents-heading">
                                    <div class="provider-documents-heading">
                                        <h2 id="provider-documents-heading">المرفقات والمستندات</h2>
                                        <p>يمكنك إرفاقها الآن أو استكمالها لاحقًا قبل اعتماد الحساب. الملفات المقبولة PDF أو صورة، بحد أقصى 10 ميجابايت لكل ملف.</p>
                                    </div>
                                    <div class="row g-3">
                                        @foreach([
                                            'commercial_registration_document' => 'السجل التجاري',
                                            'legal_representative_id_document' => 'هوية الممثل القانوني',
                                            'municipal_license' => 'الرخصة البلدية',
                                            'signed_annex_a' => 'الملحق (أ) الموقّع',
                                            'company_seal' => 'الختم أو الشعار',
                                        ] as $field => $label)
                                            <div class="col-md-6">
                                                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                                <input class="form-control @error($field) is-invalid @enderror" type="file" id="{{ $field }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf">
                                                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            </div>
                        </div>
                        <div class="provider-register-actions d-flex flex-wrap gap-2">
                            <button class="btn btn-primary px-4" type="submit">إرسال طلب التسجيل</button>
                            <a class="btn btn-outline-primary" href="{{ route('provider.login') }}">لديك حساب؟ تسجيل الدخول</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row gy-4 footer-top">
            <div class="col-12 col-lg-3 footer-brand">
                <a href="{{ route('home') }}" aria-label="راوندر — الرئيسية"><img class="wordmark" src="{{ asset('assets/images/rounder-wordmark.png') }}" alt="rounder — راوندر" width="2172" height="724" loading="lazy"></a>
                <p>عناية للمنزل. راحة لك.</p>
            </div>
            <div class="col-6 col-lg-3">
                <h3>اكتشف راوندر</h3>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('home') }}#services">الخدمات المنزلية</a></li>
                    <li><a href="{{ route('home') }}#business">حلول الأعمال</a></li>
                    <li><a href="{{ route('provider.register') }}">التسجيل كمزود خدمة</a></li>
                    <li><a href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>
                    @auth<li><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>@else<li><a href="{{ route('admin.login') }}">دخول الإدارة</a></li>@endauth
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h3>كيف نقدر نساعدك؟</h3>
                <ul class="list-unstyled footer-links">
                    <li><a href="{{ route('home') }}#how">طريقة الحجز</a></li>
                    <li><a href="{{ route('home') }}#faq">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('provider.login') }}">متابعة طلب مزود الخدمة</a></li>
                </ul>
            </div>
            <div class="col-12 col-lg-3 footer-note">
                <span class="concept-label">نسخة تصميم تجريبية</span>
                <p>واجهة راوندر للخدمات المنزلية.<br>المحتوى والطلبات لأغراض المعاينة.</p>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between gap-2">
            <span>راوندر © 2026 — تصوّر لمنصة الخدمات المنزلية</span>
            <span>صُمّم ليكون أقرب لك <span class="brand-heart" aria-hidden="true">♡</span></span>
        </div>
    </div>
</footer>
</body>
</html>
