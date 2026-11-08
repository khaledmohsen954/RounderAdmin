<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#00343e">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="راوندر — خدمات المنزل والأعمال. استكشف الخدمات وجرّب رحلة الحجز.">
    <title>راوندر — كل خدمات بيتك، في مكان واحد</title>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="assets/fonts/tajawal-bold.ttf" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/vendor/select2/select2.min.css?v=4.1.0">
    <link rel="stylesheet" href="assets/vendor/select2/select2-bootstrap-5-theme.rtl.min.css?v=1.3.0">
    <link rel="stylesheet" href="assets/css/style.css?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/select2/select2.min.js"></script>
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script>
      window.ROUNDER_CONFIG = {!! json_encode($clientConfig ?? [], JSON_UNESCAPED_UNICODE) !!};
    </script>
    <script src="assets/js/app.js?v={{ filemtime(public_path('assets/js/app.js')) }}" defer></script>
  </head>
  <body>
    <a class="visually-hidden-focusable skip-link" href="#main">تخطّ إلى المحتوى</a>
    <!-- Local SVG symbols: no external icon dependency. -->
    <svg class="sprite" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21v-8h6v8"/></symbol>
  <symbol id="i-building" viewBox="0 0 24 24"><path d="M4 21V3h11v18M15 9h5v12M2 21h20M8 7h3M8 11h3M8 15h3M8 21v-3h3v3"/></symbol>
  <symbol id="i-tool" viewBox="0 0 24 24"><path d="M14.5 6.5 18 3a6 6 0 0 0-7.8 7.8l-6.6 6.6a2.3 2.3 0 0 0 3.3 3.3l6.6-6.6A6 6 0 0 0 21 6l-3.5 3.5z"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-3-5"/></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M20 12H4m6-6-6 6 6 6"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
  <symbol id="i-ac" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="9" rx="2"/><path d="M5 9h14M18 6h1M6 15v5M12 15v6M18 15v5"/></symbol>
  <symbol id="i-drop" viewBox="0 0 24 24"><path d="M12 2S4 10.5 4 15a8 8 0 0 0 16 0c0-4.5-8-13-8-13Z"/><path d="M8 15a4 4 0 0 0 4 4"/></symbol>
  <symbol id="i-bolt" viewBox="0 0 24 24"><path d="m13 2-9 12h7l-1 8 10-13h-7z"/></symbol>
  <symbol id="i-washer" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M4 7h16M7 4.5h1M11 4.5h1"/><circle cx="12" cy="14.5" r="4.5"/><path d="M8 14c3-2 5 3 8 0"/></symbol>
  <symbol id="i-paint" viewBox="0 0 24 24"><rect x="3" y="3" width="15" height="6" rx="2"/><path d="M18 6h3v7H11v3"/><rect x="9" y="16" width="4" height="6" rx="1"/></symbol>
  <symbol id="i-spark" viewBox="0 0 24 24"><path d="m12 3 2.4 6.6L21 12l-6.6 2.4L12 21l-2.4-6.6L3 12l6.6-2.4zM20 2v4M18 4h4"/></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
  <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2 3 6v6c0 6 9 10 9 10s9-4 9-10V6zM8 12l3 3 5-6"/></symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 2v6M17 2v6M3 11h18M8 15h1M12 15h1M16 15h1"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
  <symbol id="i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
  <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
  <symbol id="i-doc" viewBox="0 0 24 24"><path d="M14 2H5v20h14V7zM14 2v5h5M8 12h8M8 16h6"/></symbol>
</svg>

    <!-- Bootstrap navbar / responsive collapse -->
    <header class="site-header sticky-top">
      <nav class="navbar navbar-expand-lg" aria-label="القائمة الرئيسية" data-bs-theme="dark">
        <div class="container">
          <a class="navbar-brand" href="#" aria-label="راوندر — الرئيسية">
            <img class="wordmark" src="assets/images/rounder-wordmark.png" width="2172" height="724" alt="rounder — راوندر">
          </a>
          <div class="site-actions d-flex align-items-center gap-2 gap-lg-3 order-lg-3">
            @auth
              <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.dashboard') }}">
                <svg class="icon" aria-hidden="true" style="width: 14px; height: 14px;"><use href="#i-grid"></use></svg>
                <span>لوحة التحكم</span>
              </a>
            @else
              <a class="btn btn-sm d-none d-md-inline-flex align-items-center gap-1 admin-portal-btn" href="{{ route('admin.login') }}">
                <svg class="icon" aria-hidden="true" style="width: 14px; height: 14px;"><use href="#i-shield"></use></svg>
                <span>دخول الإدارة</span>
              </a>
            @endauth
            <a class="btn btn-link header-partner d-none d-xl-inline-flex" href="{{ route('provider.register') }}">
              انضم كمزود خدمة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
            </a>
            <button class="btn btn-primary btn-sm" type="button" data-modal="booking">
              اطلب خدمة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg>
            </button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav"
              aria-controls="main-nav" aria-expanded="false" aria-label="فتح القائمة">
              <svg class="icon" aria-hidden="true"><use href="#i-menu"></use></svg>
            </button>
          </div>
          <div class="collapse navbar-collapse order-lg-2" id="main-nav">
            <ul class="navbar-nav mx-lg-auto gap-lg-3">
              <li class="nav-item"><a class="nav-link active" href="#services">خدماتنا</a></li>
              <li class="nav-item"><a class="nav-link" href="#business">للأعمال</a></li>
              <li class="nav-item"><a class="nav-link" href="#how">كيف نعمل</a></li>
              <li class="nav-item"><a class="nav-link" href="#faq">الأسئلة الشائعة</a></li>
              @auth
                <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
              @else
                <li class="nav-item d-md-none"><a class="nav-link text-warning fw-bold" href="{{ route('admin.login') }}">دخول الإدارة</a></li>
              @endauth
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main id="main">
      <section class="hero-section" aria-label="راوندر لخدمات المنزل والأعمال">
        <div class="container">
          <div class="hero-top d-flex justify-content-between align-items-center">
            <span class="eyebrow"><span class="brand-dot"></span> عناية تبدأ من بيتك</span>
            <span class="concept-label">راوندر · خدمات المنزل والأعمال</span>
          </div>
          <!-- Bootstrap tabs; each target has an independent accessible panel. -->
          <ul class="nav nav-tabs sector-tabs" id="sector-tabs" role="tablist" aria-label="اختر قطاعك">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="tab-home" data-bs-toggle="tab" data-bs-target="#panel-home"
                type="button" role="tab" aria-controls="panel-home" aria-selected="true">
                <svg class="icon" aria-hidden="true"><use href="#i-home"></use></svg> للأفراد <span class="tab-number">01</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-business" data-bs-toggle="tab" data-bs-target="#panel-business"
                type="button" role="tab" aria-controls="panel-business" aria-selected="false">
                <svg class="icon" aria-hidden="true"><use href="#i-building"></use></svg> للأعمال <span class="tab-number">02</span>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="tab-provider" data-bs-toggle="tab" data-bs-target="#panel-provider"
                type="button" role="tab" aria-controls="panel-provider" aria-selected="false">
                <svg class="icon" aria-hidden="true"><use href="#i-tool"></use></svg> لمزودي الخدمات <span class="tab-number">03</span>
              </button>
            </li>
          </ul>

          <div class="row gy-4 gx-lg-5 align-items-center">
            <div class="col-lg-6">
              <div class="tab-content hero-panels">
                <div class="tab-pane fade show active" id="panel-home" role="tabpanel" aria-labelledby="tab-home" tabindex="0">
                  <p class="mini-label">خدمات منزلية، بطريقة أريح</p>
                  <h1 class="hero-title">بيتك يستاهل<br><span>راحة البال.</span><span class="title-spark" aria-hidden="true">✳</span></h1>
                  <p class="hero-description">من المكيّف إلى أصغر تفصيلة في بيتك.<br>اختر خدمتك، حدّد وقتك، وخلّ الباقي علينا.</p>
                  <div class="hero-actions d-flex flex-wrap align-items-center gap-3">
                    <button class="btn btn-primary" type="button" data-modal="booking">اختر خدمتك <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
                    <a class="btn btn-link" href="#how">كيف تعمل راوندر؟ <span class="play-icon" aria-hidden="true">◁</span></a>
                  </div>
                </div>
                <div class="tab-pane fade" id="panel-business" role="tabpanel" aria-labelledby="tab-business" tabindex="0">
                  <p class="mini-label">حلول صيانة لأعمالك</p>
                  <h2 class="hero-title">أعمالك تستاهل<br><span>عناية مختلفة.</span></h2>
                  <p class="hero-description">للمكاتب والفروع والعقارات.<br>رتّب احتياجات الصيانة، وتابعها من مكان واحد.</p>
                  <div class="hero-actions d-flex flex-wrap align-items-center gap-3">
                    <button class="btn btn-primary" type="button" data-modal="business">جهّز خطة لأعمالك <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
                    <a class="btn btn-link" href="#business">استكشف حلول الأعمال <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></a>
                  </div>
                </div>
                <div class="tab-pane fade" id="panel-provider" role="tabpanel" aria-labelledby="tab-provider" tabindex="0">
                  <p class="mini-label">فرصة تكبر مع خبرتك</p>
                  <h2 class="hero-title">مهارتك تصنع<br><span>فرقًا في كل بيت.</span></h2>
                  <p class="hero-description">أنت تقدّم الخبرة، وراوندر ترتّب التجربة.<br>استكشف طريقة الانضمام كمزود خدمة.</p>
                  <div class="hero-actions d-flex flex-wrap align-items-center gap-3">
                    <a class="btn btn-primary" href="{{ route('provider.register') }}">انضم لراوندر <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></a>
                    <a class="btn btn-link" href="#providers">تعرّف على الفرصة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></a>
                  </div>
                </div>
              </div>
              <div class="hero-benefits d-flex flex-wrap gap-3" aria-label="مزايا التجربة">
                <span><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> تفاصيل واضحة</span>
                <span><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> وقت يناسبك</span>
                <span><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> متابعة لكل خطوة</span>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-art">
                <img class="hero-image img-fluid" src="assets/images/home-hero.png" width="1254" height="1254"
                  alt="مجسّم غرفة معيشة عصرية بأريكة بترولية وتكييف ونباتات" fetchpriority="high">
                <div class="art-top"><span>تفاصيل صغيرة. راحة كبيرة.</span><svg class="icon" aria-hidden="true"><use href="#i-spark"></use></svg></div>
                <button class="hotspot ac-spot" type="button" data-service="ac" aria-label="استكشف خدمة التكييف"><svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg></button>
                <button class="hotspot clean-spot" type="button" data-service="clean" aria-label="استكشف خدمة التنظيف"><svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg></button>
                <div class="floating-status">
                  <span class="status-icon"><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg></span>
                  <div><strong>كل شيء، تحت السيطرة</strong><span>من الطلب إلى اكتمال الخدمة</span></div>
                  <span class="status-point"></span>
                </div>
                <div class="art-bottom"><span>مساحة للراحة، والباقي على راوندر</span><span lang="en" dir="ltr">HOME, TAKEN CARE OF.</span></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="value-strip">
        <div class="container">
          <div class="row row-cols-2 row-cols-lg-4 g-3 g-lg-0">
            <div class="col"><div class="value-item"><svg class="icon" aria-hidden="true"><use href="#i-grid"></use></svg><span>خدماتك في مكان واحد</span></div></div>
            <div class="col"><div class="value-item"><svg class="icon" aria-hidden="true"><use href="#i-calendar"></use></svg><span>حجز على وقتك</span></div></div>
            <div class="col"><div class="value-item"><svg class="icon" aria-hidden="true"><use href="#i-doc"></use></svg><span>تفاصيل الخدمة بوضوح</span></div></div>
            <div class="col"><div class="value-item"><svg class="icon" aria-hidden="true"><use href="#i-shield"></use></svg><span>عناية بكل تفصيلة</span></div></div>
          </div>
        </div>
      </div>

      <section class="section-space" id="services" aria-labelledby="services-title">
        <div class="container">
          <div class="section-heading d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div><span class="eyebrow">خدمات راوندر</span><h2 id="services-title">إيش يحتاج بيتك اليوم؟</h2><p>كل احتياجات المنزل، بخطوة بسيطة.</p></div>
            <div class="nav nav-pills service-filters" role="group" aria-label="تصفية الخدمات">
              <button class="nav-link active" type="button" data-filter="all" aria-pressed="true">كل الخدمات</button>
              @if(!empty($categories))
                @foreach($categories as $category)
                  <button class="nav-link" type="button" data-filter="{{ $category->code }}" aria-pressed="false">
                    {{ $category->translations->firstWhere('locale', app()->getLocale())?->name ?? $category->code }}
                  </button>
                @endforeach
              @else
                <button class="nav-link" type="button" data-filter="maintenance" aria-pressed="false">صيانة</button>
                <button class="nav-link" type="button" data-filter="cleaning" aria-pressed="false">نظافة</button>
              @endif
            </div>
          </div>
          <div class="row row-cols-2 row-cols-lg-3 g-3 g-lg-4" id="service-grid">
            @php
              $tones = ['tone-teal', 'tone-mint', 'tone-orange', 'tone-sage'];
              $footerPhrases = [
                  'خلّ جوّك أريح',
                  'حلول من الأساس',
                  'نوّر بيتك براحة',
                  'لروتين يومي أسهل',
                  'جدّد تفاصيل مكانك',
                  'راحة تبدأ بالنظافة',
                  'إتقان يدوم معك',
                  'خدمة سريعة ومضمونة',
              ];
            @endphp
            @forelse($services as $service)
              @php
                $catCode = $service->category?->code ?? 'maintenance';
                $serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code;
                $serviceDesc = $service->translations->firstWhere('locale', app()->getLocale())?->description;
                if (!$serviceDesc) {
                    $firstSubs = $service->subServices->take(3)->map(
                        fn($s) => $s->translations->firstWhere('locale', app()->getLocale())?->name ?? $s->code
                    )->filter()->implode('، ');
                    $serviceDesc = $firstSubs ? 'يشمل: ' . $firstSubs : 'صيانة وخدمات احترافية لبيتك';
                }
                $icon = !empty($service->icon) ? $service->icon : 'home';
                $tone = $tones[$loop->index % count($tones)];
                $phrase = $footerPhrases[$loop->index % count($footerPhrases)];
              @endphp
              <div class="col" data-service-item data-category="{{ $catCode }}">
                <button type="button" class="card service-card h-100 w-100" data-service="{{ $service->code }}">
                  <span class="service-icon {{ $tone }}"><svg class="icon" aria-hidden="true"><use href="#i-{{ $icon }}"></use></svg></span>
                  <span class="service-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <h3>{{ $serviceName }}</h3>
                  <p>{{ $serviceDesc }}</p>
                  <span class="service-card-footer">{{ $phrase }} <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></span>
                </button>
              </div>
            @empty
              <div class="col" data-service-item data-category="maintenance">
                <button type="button" class="card service-card h-100 w-100" data-service="ac">
                  <span class="service-icon tone-teal"><svg class="icon" aria-hidden="true"><use href="#i-ac"></use></svg></span>
                  <span class="service-number">01</span>
                  <h3>التكييف</h3>
                  <p>صيانة، تنظيف وتركيب المكيّفات</p>
                  <span class="service-card-footer">خلّ جوّك أريح <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></span>
                </button>
              </div>
            @endforelse
          </div>
          <p class="visually-hidden" id="filter-status" role="status" aria-live="polite"></p>
          <div class="service-help d-flex flex-column flex-sm-row justify-content-center align-items-center gap-1 gap-sm-3">
            <span>مو متأكد من الخدمة المناسبة؟</span>
            <button class="btn btn-link" type="button" data-modal="help">خلّنا نساعدك تختار <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
          </div>
        </div>
      </section>

      <section class="how-section" id="how" aria-labelledby="how-title">
        <div class="container">
          <div class="section-heading d-flex flex-column flex-md-row justify-content-between gap-3">
            <div><span class="eyebrow">من الطلب إلى الإنجاز</span><h2 id="how-title">ثلاث خطوات.<br>وأمورك مرتّبة.</h2></div>
            <p class="section-summary align-self-md-center">تجربة واضحة من البداية؛ تعرف إيش طلبت،<br>ومتى موعدك، وإيش الخطوة الجاية.</p>
          </div>
          <div class="row g-4 g-lg-5 steps-row">
            <article class="col-md-4"><div class="step"><span class="step-number">01</span><span class="step-icon"><svg class="icon" aria-hidden="true"><use href="#i-grid"></use></svg></span><h3>اختر الخدمة</h3><p>حدّد احتياجك، واستعرض تفاصيل الخدمة المناسبة لبيتك.</p></div></article>
            <article class="col-md-4"><div class="step"><span class="step-number">02</span><span class="step-icon"><svg class="icon" aria-hidden="true"><use href="#i-calendar"></use></svg></span><h3>رتّب الموعد</h3><p>اختر مدينتك واليوم والوقت اللي يناسب جدولك.</p></div></article>
            <article class="col-md-4"><div class="step"><span class="step-number">03</span><span class="step-icon"><svg class="icon" aria-hidden="true"><use href="#i-home"></use></svg></span><h3>خلّ الباقي علينا</h3><p>راجع تفاصيل طلبك، وتابع رحلته من مكان واحد.</p></div></article>
          </div>
          <button class="btn btn-outline-primary mt-4" type="button" data-modal="booking">جرّب رحلة الحجز <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
        </div>
      </section>

      <section class="section-space" id="business" aria-labelledby="business-title">
        <div class="container">
          <div class="business-panel">
            <div class="row g-5 align-items-center">
              <div class="col-lg-6 business-copy">
                <span class="eyebrow">راوندر للأعمال</span>
                <h2 id="business-title">أنت ركّز على شغلك.<br><span>وصيانة منشأتك علينا.</span></h2>
                <p>تصوّر موحّد لإدارة صيانة المكاتب والفروع والعقارات،<br>من الطلب الأول إلى تقرير الإنجاز.</p>
                <ul class="list-unstyled business-features">
                  <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> متابعة المواقع والفروع</li>
                  <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> صيانة دورية وطلبات طارئة</li>
                  <li><svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg> تقارير وملخصات واضحة</li>
                </ul>
                <button class="btn btn-primary" type="button" data-modal="business">صمّم خطة لأعمالك <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
              </div>
              <div class="col-lg-6">
                <div class="dashboard-preview" aria-label="مثال توضيحي للوحة متابعة الأعمال">
                  <div class="dash-header d-flex justify-content-between align-items-center">
                    <span class="dash-brand"><img src="assets/images/rounder-wordmark.png" alt="rounder" width="2172" height="724"><small>للأعمال</small></span>
                    <span class="badge rounded-pill demo-badge">بيانات توضيحية</span>
                  </div>
                  <div class="dash-title d-flex justify-content-between align-items-center"><strong>نظرة على منشآتك</strong><span>هذا الأسبوع</span></div>
                  <div class="row g-2 dash-stats">
                    <div class="col-4"><div><span>الطلبات</span><strong>12</strong></div></div>
                    <div class="col-4"><div><span>مكتملة</span><strong>09 <small>↗</small></strong></div></div>
                    <div class="col-4"><div><span>قيد التنفيذ</span><strong>03</strong></div></div>
                  </div>
                  <div class="table-responsive">
                    <table class="table align-middle dash-table mb-0">
                      <caption class="visually-hidden">طلبات تجريبية لتوضيح شكل المتابعة</caption>
                      <thead><tr><th scope="col">آخر الطلبات</th><th scope="col">الحالة</th></tr></thead>
                      <tbody>
                        <tr><td><span class="dash-icon"><svg class="icon" aria-hidden="true"><use href="#i-ac"></use></svg></span> صيانة التكييف <small>المكتب الرئيسي</small></td><td><span class="badge status-complete">مكتمل</span></td></tr>
                        <tr><td><span class="dash-icon"><svg class="icon" aria-hidden="true"><use href="#i-bolt"></use></svg></span> فحص الإضاءة <small>الفرع الثاني</small></td><td><span class="badge status-progress">قيد التنفيذ</span></td></tr>
                        <tr><td><span class="dash-icon"><svg class="icon" aria-hidden="true"><use href="#i-drop"></use></svg></span> فحص السباكة <small>المكتب الرئيسي</small></td><td><span class="badge status-complete">مكتمل</span></td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <p class="dash-caption"><svg class="icon" aria-hidden="true"><use href="#i-building"></use></svg> فروع متعددة. رؤية واحدة.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="providers" class="provider-section" aria-labelledby="provider-title">
        <div class="container">
          <div class="row align-items-center gy-3 provider-inner">
            <div class="col-auto"><div class="provider-icon"><svg class="icon" aria-hidden="true"><use href="#i-tool"></use></svg></div></div>
            <div class="col"><span class="eyebrow">كبر شغلك مع راوندر</span><h2 id="provider-title">عندك الخبرة؟ نحتاج مهارتك.</h2><p>مساحة لمزودي الخدمات والفنيين لعرض تخصصاتهم وتنظيم طلباتهم.</p></div>
            <div class="col-12 col-lg-auto"><a class="btn btn-outline-primary" href="{{ route('provider.register') }}">انضم كمزود خدمة <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></a></div>
          </div>
        </div>
      </section>

      <section id="faq" class="section-space" aria-labelledby="faq-title">
        <div class="container">
          <div class="row g-4 g-lg-5">
            <div class="col-lg-5 faq-intro"><span class="eyebrow">قبل ما تبدأ</span><h2 id="faq-title">في بالك سؤال؟</h2><p>هنا أهم التفاصيل عن تجربة راوندر.</p><div class="faq-note d-none d-lg-flex"><svg class="icon" aria-hidden="true"><use href="#i-spark"></use></svg><span>الراحة تبدأ<br>من وضوح التفاصيل.</span></div></div>
            <div class="col-lg-7">
              <div class="accordion accordion-flush" id="faq-accordion">
                
              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq-1"
                    aria-expanded="true" aria-controls="faq-1">
                    كيف أطلب خدمة من راوندر؟
                  </button>
                </h3>
                <div id="faq-1" class="accordion-collapse collapse show"
                  data-bs-parent="#faq-accordion">
                  <div class="accordion-body">اختر الخدمة التي تحتاجها، ثم حدّد المدينة والموعد والتفاصيل. يمكنك الآن تجربة الخطوات واستعراض ملخص طلبك في هذا النموذج.</div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq-2"
                    aria-expanded="false" aria-controls="faq-2">
                    هل يمكن تجربة حجز حقيقي هنا؟
                  </button>
                </h3>
                <div id="faq-2" class="accordion-collapse collapse"
                  data-bs-parent="#faq-accordion">
                  <div class="accordion-body">هذه نسخة تصميم تجريبية. الطلبات لا تُرسل إلى فنيين، ولا توجد مدفوعات أو حجوزات فعلية. الهدف هو معاينة شكل الموقع وتجربة الاستخدام.</div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq-3"
                    aria-expanded="false" aria-controls="faq-3">
                    كيف يتم تحديد تكلفة الخدمة؟
                  </button>
                </h3>
                <div id="faq-3" class="accordion-collapse collapse"
                  data-bs-parent="#faq-accordion">
                  <div class="accordion-body">يقترح التصميم توضيح نطاق العمل والتكلفة قبل تأكيد أي طلب. الأسعار الفعلية وسياسة المعاينة تُضاف عند تجهيز المنصة للتشغيل.</div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq-4"
                    aria-expanded="false" aria-controls="faq-4">
                    هل توجد خدمات للشركات والعقارات؟
                  </button>
                </h3>
                <div id="faq-4" class="accordion-collapse collapse"
                  data-bs-parent="#faq-accordion">
                  <div class="accordion-body">نعم، يضم التصوّر قسمًا للأعمال يوضّح إدارة صيانة المواقع والفروع. جرّب نموذج الأعمال لتحديد نوع المنشأة والخدمات المطلوبة.</div>
                </div>
              </div>

              <div class="accordion-item">
                <h3 class="accordion-header">
                  <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq-5"
                    aria-expanded="false" aria-controls="faq-5">
                    كيف أسجل كمزود خدمة؟
                  </button>
                </h3>
                <div id="faq-5" class="accordion-collapse collapse"
                  data-bs-parent="#faq-accordion">
                  <div class="accordion-body">أرسل بيانات شركتك والخدمات التي تقدمها. تراجع الإدارة الطلب والمرفقات، وبعد الموافقة يظهر لك العقد في حسابك؛ اقبله للدخول إلى لوحة مزود الخدمة.</div>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="final-section" aria-labelledby="final-title">
        <div class="container">
          <div class="final-cta d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4">
            <div><span class="eyebrow">بيتك، على راحتك</span><h2 id="final-title">كل تفصيلة تستاهل عناية.</h2><p>ابدأ بخدمة واحدة، واكتشف تجربة أبسط.</p></div>
            <button class="btn btn-primary align-self-start align-self-md-center" type="button" data-modal="booking">خلّنا نهتم ببيتك <svg class="icon" aria-hidden="true"><use href="#i-arrow"></use></svg></button>
            <div class="cta-decoration" aria-hidden="true">✳</div>
          </div>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <div class="row gy-4 footer-top">
          <div class="col-12 col-lg-3 footer-brand">
            <a href="#" aria-label="راوندر — الرئيسية"><img class="wordmark" src="assets/images/rounder-wordmark.png" alt="rounder — راوندر" width="2172" height="724" loading="lazy"></a>
            <p>عناية للمنزل. راحة لك.</p>
          </div>
          <div class="col-6 col-lg-3"><h3>اكتشف راوندر</h3><ul class="list-unstyled footer-links"><li><a href="#services">الخدمات المنزلية</a></li><li><a href="#business">حلول الأعمال</a></li><li><a href="{{ route('provider.register') }}">التسجيل كمزود خدمة</a></li><li><a href="{{ route('provider.login') }}">دخول مزود الخدمة</a></li>@auth<li><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>@else<li><a href="{{ route('admin.login') }}">دخول الإدارة</a></li>@endauth</ul></div>
          <div class="col-6 col-lg-3"><h3>كيف نقدر نساعدك؟</h3><ul class="list-unstyled footer-links"><li><a href="#how">طريقة الحجز</a></li><li><a href="#faq">الأسئلة الشائعة</a></li><li><button type="button" data-modal="help">اختيار الخدمة المناسبة</button></li></ul></div>
          <div class="col-12 col-lg-3 footer-note"><span class="concept-label">نسخة تصميم تجريبية</span><p>واجهة راوندر للخدمات المنزلية.<br>المحتوى والطلبات لأغراض المعاينة.</p></div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between gap-2"><span>راوندر © 2026 — تصوّر لمنصة الخدمات المنزلية</span><span>صُمّم ليكون أقرب لك <span class="brand-heart" aria-hidden="true">♡</span></span></div>
      </div>
    </footer>

    <!-- One Bootstrap modal shared by the local demo journeys. -->
    <div class="modal fade" id="request-modal" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <img class="modal-wordmark" src="assets/images/rounder-wordmark.png" width="2172" height="724" alt="rounder — راوندر">
            <button class="btn icon-button" type="button" data-bs-dismiss="modal" aria-label="إغلاق النموذج"><svg class="icon" aria-hidden="true"><use href="#i-close"></use></svg></button>
          </div>
          <div class="modal-body" id="modal-content"></div>
        </div>
      </div>
    </div>
    <noscript><div class="alert alert-warning text-center mb-0 rounded-0">فعّل JavaScript لتجربة تبويبات القطاعات ونماذج الحجز.</div></noscript>
  </body>
</html>
