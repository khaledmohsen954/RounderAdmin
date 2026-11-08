<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title',__('Rounder admin'))</title>
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=3">
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=3">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=3">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale()==='ar'?'.rtl':'' }}.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
<style>
:root{
  --petrol:#02333E;
  --petrol-dark:#01242C;
  --petrol-light:#025968;
  --orange:#FB822F;
  --orange-hover:#e67120;
  --mint:#DDF3EE;
  --sand:#F8FAFB;
}
html,body{
  overflow-x:hidden;
  max-width:100vw;
  -webkit-text-size-adjust:100%;
}
body{
  background:#F8FAFB;
  color:#17343a;
  font-family:"Tajawal",system-ui,sans-serif;
}
.brand{font-weight:800;letter-spacing:-.02em}
.text-rounder-orange{color:var(--orange)!important}
.brand-text{font-size:1.65rem;font-weight:800;letter-spacing:-.02em;color:var(--orange)!important}
.brand-badge-mint{background:rgba(221,243,238,0.15);color:var(--mint);font-size:.72rem;font-weight:600;padding:.25rem .6rem;border:1px solid rgba(221,243,238,0.2)}

/* Admin Sidebar - Full Height to Top */
.admin-sidebar{
  --bs-offcanvas-width:260px;
  background:linear-gradient(180deg,#02333E 0%,#023945 50%,#012831 100%)!important;
  color:white;
  border-radius:0!important;
  border:none!important;
}
.admin-sidebar::-webkit-scrollbar{width:5px}
.admin-sidebar::-webkit-scrollbar-track{background:transparent}
.admin-sidebar::-webkit-scrollbar-thumb{background:rgba(221,243,238,.2);border-radius:10px}
.admin-sidebar::-webkit-scrollbar-thumb:hover{background:rgba(221,243,238,.4)}

.sidebar-brand-header{
  height:68px;
  min-height:68px;
  border-bottom:1px solid rgba(255,255,255,.08);
  flex-shrink:0;
}

.sidebar-accordion{display:flex;flex-direction:column;gap:.45rem}
.sidebar-group{margin-bottom:0}
.sidebar-group-btn{width:100%;display:flex;align-items:center;justify-content:space-between;padding:.68rem .92rem;background:transparent;border:none;border-radius:10px;color:#c0ded8;font-size:.92rem;font-weight:600;text-align:inherit;cursor:pointer;transition:all .18s ease}
.sidebar-group-btn:hover{background:rgba(255,255,255,.08);color:#fff}
.sidebar-group-btn .group-icon{width:1.35rem;text-align:center;margin-inline-end:.55rem;font-size:.95rem;opacity:.85}
.sidebar-group-btn .chevron-icon{font-size:.72rem;transition:transform .2s ease;opacity:.65}
.sidebar-group-btn:not(.collapsed){color:#fff;background:rgba(255,255,255,.05)}
.sidebar-group-btn:not(.collapsed) .chevron-icon{transform:rotate(180deg);opacity:1;color:var(--orange)}
.sidebar-group-btn.has-active-child{color:#fff;font-weight:700}
.sidebar-group-btn.has-active-child .group-icon{color:var(--orange);opacity:1}

.sidebar-sublinks{margin-inline-start:1rem;padding-inline-start:.85rem;border-inline-start:1.5px solid rgba(221,243,238,.18);margin-top:.3rem;margin-bottom:.4rem;display:flex;flex-direction:column;gap:.25rem}
.sidebar-sublinks a{display:flex;align-items:center;padding:.48rem .85rem;border-radius:8px;text-decoration:none;color:#bde2db;font-size:.86rem;font-weight:500;transition:all .15s ease}
.sidebar-sublinks a i{width:1.25rem;text-align:center;margin-inline-end:.5rem;font-size:.85rem;opacity:.8}
.sidebar-sublinks a:hover{background:rgba(255,255,255,.09);color:#fff}
.sidebar-sublinks a:hover i{opacity:1}
.sidebar-sublinks a.active{background:var(--orange);color:#fff!important;font-weight:700;box-shadow:0 3px 12px rgba(251,130,47,.35)}
.sidebar-sublinks a.active i{color:#fff;opacity:1}

.dashboard-link{display:flex;align-items:center;padding:.68rem .92rem;border-radius:10px;text-decoration:none;color:var(--mint)!important;font-weight:600;font-size:.92rem;transition:all .15s ease}
.dashboard-link i{width:1.35rem;text-align:center;margin-inline-end:.55rem;font-size:.95rem}
.dashboard-link:hover{background:rgba(255,255,255,.08);color:#fff!important}
.dashboard-link.active{background:var(--orange);color:#fff!important;font-weight:700;box-shadow:0 3px 12px rgba(251,130,47,.35)}
.dashboard-link.active i{color:#fff}

.panel,.metric{background:#fff;border:1px solid #e2ece9;border-radius:18px;box-shadow:0 3px 16px rgba(2,51,62,0.035)}
.metric{padding:1.15rem;height:100%}
.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}
.metric .mark{color:var(--orange)}
.content{padding:1.5rem}

/* Modern Form Controls */
.form-control,.form-select{border-radius:10px;border:1px solid #dce6e3;padding:.58rem .88rem;font-size:.88rem;color:#17343a;background-color:#fff;transition:border-color .15s ease,box-shadow .15s ease}
.form-control:focus,.form-select:focus{border-color:var(--petrol-light)!important;box-shadow:0 0 0 3.5px rgba(2,51,62,.12)!important;outline:none!important}
.form-control-sm,.form-select-sm{border-radius:8px;padding:.38rem .68rem;font-size:.82rem}
.form-label{font-weight:600;font-size:.86rem;color:#233d43;margin-bottom:.35rem}
.form-check-input{border-radius:6px;border-color:#cedbd7;cursor:pointer}
.form-check-input:checked{background-color:var(--orange);border-color:var(--orange)}
.form-check-input:focus{box-shadow:0 0 0 3px rgba(251,130,47,.2);border-color:var(--orange)}

/* Service Checkbox Cards */
.service-checkbox-card{background:#fff;border:1.5px solid #e2ece9;cursor:pointer;transition:all .18s cubic-bezier(0.16,1,0.3,1);border-radius:14px;padding:.85rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.service-checkbox-card:hover{background:#fafdfc;border-color:#ffd4b8;transform:translateY(-2px);box-shadow:0 4px 14px rgba(2,51,62,0.06)}
.service-checkbox-card.is-selected,.service-checkbox-card:has(.form-check-input:checked){background:#fff9f4!important;border-color:var(--orange)!important;box-shadow:0 4px 14px rgba(251,130,47,0.15)}
.service-checkbox-card .service-icon{width:34px;height:34px;border-radius:9px;background:#f1f6f4;color:var(--petrol);display:inline-grid;place-items:center;font-size:.85rem;transition:all .18s ease}
.service-checkbox-card.is-selected .service-icon,.service-checkbox-card:has(.form-check-input:checked) .service-icon{background:#fff0e6;color:var(--orange)}
.service-checkbox-card .form-check-input{width:20px;height:20px;border-radius:6px;margin:0;cursor:pointer}
.service-checkbox-card .form-check-input:checked{background-color:var(--orange);border-color:var(--orange)}

/* Modern Luxury Buttons */
.btn{border-radius:10px;font-weight:600;font-size:.88rem;padding:.52rem 1.05rem;transition:all .18s cubic-bezier(0.16,1,0.3,1);display:inline-flex;align-items:center;justify-content:center;gap:.4rem}
.btn-sm{border-radius:8px;font-size:.82rem;padding:.36rem .75rem;gap:.3rem}
.btn-primary{background:linear-gradient(135deg,#02333E 0%,#034553 100%)!important;border-color:#02333E!important;color:#fff!important;box-shadow:0 2px 6px rgba(2,51,62,.18)}
.btn-primary:hover,.btn-primary:focus{background:#025968!important;border-color:#025968!important;box-shadow:0 4px 14px rgba(2,51,62,.28);transform:translateY(-1px);color:#fff!important}
.btn-orange,.btn-brand{background:var(--orange)!important;border-color:var(--orange)!important;color:#fff!important;box-shadow:0 2px 8px rgba(251,130,47,.25)}
.btn-orange:hover,.btn-orange:focus,.btn-brand:hover,.btn-brand:focus{background:var(--orange-hover)!important;border-color:var(--orange-hover)!important;box-shadow:0 4px 14px rgba(251,130,47,.38);transform:translateY(-1px);color:#fff!important}
.btn-outline-primary{color:var(--petrol)!important;border:1.5px solid var(--petrol)!important;background:transparent!important}
.btn-outline-primary:hover{background:var(--petrol)!important;color:#fff!important;box-shadow:0 2px 8px rgba(2,51,62,.2)}
.btn-outline-secondary{color:#456066!important;border:1px solid #d4e0dd!important;background:#fff!important}
.btn-outline-secondary:hover{background:#f1f7f5!important;border-color:#b5d2cb!important;color:var(--petrol)!important}
.btn-outline-danger{color:#c53030!important;border:1px solid #f2c7c7!important;background:#fff!important}
.btn-outline-danger:hover{background:#fdf2f2!important;border-color:#c53030!important;color:#c53030!important}

/* Modern Tables */
.table-responsive{border-radius:14px;background:#fff;-webkit-overflow-scrolling:touch;overflow-x:auto}
.table-responsive > .table{min-width:650px}
.table{margin-bottom:0;--bs-table-bg:transparent}
.table>thead{background:#f4f8f7;border-bottom:1.5px solid #dce7e4}
.table>thead th{font-size:.82rem;font-weight:700;color:var(--petrol);padding:.95rem 1rem;border-bottom:1.5px solid #dce7e4;white-space:nowrap;letter-spacing:.01em}
.table>tbody td{padding:.92rem 1rem;vertical-align:middle;border-bottom:1px solid #edf4f1;font-size:.88rem;color:#243f45;white-space:nowrap}
.table>tbody tr{transition:background-color .15s ease}
.table>tbody tr:hover{background-color:#f8fcfa}
.table>tbody tr:last-child td{border-bottom:none}

/* Badges & Pills */
.pill{display:inline-flex;align-items:center;gap:.35rem;background:#edf5f3;color:var(--petrol);border:1px solid #d8e7e3;border-radius:50rem;padding:.28rem .72rem;font-size:.82rem;font-weight:600;line-height:1.4}
.badge{border-radius:8px;font-weight:600;letter-spacing:.01em}

/* Nav Pills / Segmented Controls */
.nav-pills{gap:.45rem}
.nav-pills .nav-link{border-radius:10px;padding:.5rem 1rem;font-weight:600;font-size:.88rem;color:#4a6369;border:1px solid #dbe6e2;background:#fff;transition:all .18s ease}
.nav-pills .nav-link:hover{background:#f1f7f5;border-color:#b5d5cc;color:var(--petrol)}
.nav-pills .nav-link.active{background:var(--petrol)!important;color:#fff!important;border-color:var(--petrol)!important;box-shadow:0 3px 10px rgba(2,51,62,.22)}

/* Alerts */
.alert{border-radius:14px;font-size:.9rem;padding:.85rem 1.15rem;border:1px solid transparent}
.alert-success{background:#eef8f4;border-color:#bde4d5;color:#0e6c51}
.alert-danger{background:#fdf2f2;border-color:#f4c7c7;color:#b32a2a}
.alert-warning{background:#fff9ee;border-color:#f7e0b2;color:#a3610a}
.alert-info{background:#edf6f8;border-color:#c0dfe6;color:#025968}

/* Pagination */
.pagination{margin:1.25rem 0 .5rem;gap:4px;flex-wrap:wrap}
.pagination .page-link{border-radius:8px!important;border:1px solid #dce7e4;color:var(--petrol);font-size:.86rem;font-weight:600;padding:.42rem .82rem;transition:all .15s ease}
.pagination .page-link:hover{background:#eff6f4;border-color:#b5dad0;color:var(--petrol)}
.pagination .page-item.active .page-link{background:var(--petrol)!important;border-color:var(--petrol)!important;color:#fff!important;box-shadow:0 2px 8px rgba(2,51,62,.22)}
.pagination .page-item.disabled .page-link{background:transparent;border-color:#ebf0ee;color:#a0b2ae}

/* Breadcrumbs */
.breadcrumb{font-size:.86rem}
.breadcrumb-item a{color:#5d757b;text-decoration:none;transition:color .15s ease}
.breadcrumb-item a:hover{color:var(--orange)}
.breadcrumb-item.active{color:var(--petrol);font-weight:700}

/* Modals */
.modal-content{border-radius:18px;border:1px solid #e3ede9;box-shadow:0 20px 48px rgba(2,51,62,.16)}
.modal-header{border-bottom:1px solid #edf4f1;padding:1.2rem 1.5rem}
.modal-body{padding:1.4rem 1.5rem;font-size:.95rem;color:#2b454b}
.modal-footer{border-top:1px solid #edf4f1;padding:1rem 1.5rem}

@media(min-width:992px){
  .admin-sidebar{
    position:fixed;
    top:0;
    bottom:0;
    inset-inline-start:0;
    width:260px;
    height:100vh;
    z-index:1045;
    overflow-y:auto;
  }
  .admin-sidebar .offcanvas-body{
    display:flex!important;
    flex-direction:column!important;
    padding:1rem!important;
  }
  .topbar.topbar-with-sidebar{
    margin-inline-start:260px;
    width:calc(100% - 260px);
  }
  .admin-main{
    margin-inline-start:260px;
  }
}

@media print {
  .topbar, .admin-sidebar, #admin-menu, .modal, .d-print-none {
    display: none !important;
  }
  .admin-main {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
  }
  body {
    background: #fff !important;
  }
}

@media(max-width:991.98px){
  .admin-sidebar{min-height:auto;--bs-offcanvas-width:280px}
  .admin-main{margin:0!important;width:100%!important;overflow-x:hidden}
  .admin-main .container,.admin-main .container-fluid{padding-left:.85rem!important;padding-right:.85rem!important;padding-top:1rem!important}
  .content{padding:0!important}
  .panel{padding:1rem .85rem!important;border-radius:14px!important}
  .table-responsive{width:100%;-webkit-overflow-scrolling:touch}
}

@media(max-width:767.98px){
  .nav-pills{display:flex!important;flex-wrap:nowrap!important;overflow-x:auto!important;-webkit-overflow-scrolling:touch;gap:.45rem!important;padding-bottom:6px;margin-bottom:1rem!important;scrollbar-width:none}
  .nav-pills::-webkit-scrollbar{display:none}
  .nav-pills .nav-item{flex-shrink:0!important}
  .nav-pills .nav-link{white-space:nowrap!important;padding:.42rem .82rem!important;font-size:.84rem!important}
  .table>thead th{padding:.65rem .65rem!important;font-size:.78rem!important;white-space:nowrap}
  .table>tbody td{padding:.65rem .65rem!important;font-size:.82rem!important}
  .table .btn-sm{padding:.28rem .55rem!important;font-size:.76rem!important}
  .form-control,.form-select{font-size:16px!important;border-radius:10px}
  .form-control-sm,.form-select-sm{font-size:14px!important}
}

@media(max-width:575.98px){
  .topbar{height:58px!important;min-height:58px!important;padding-left:.75rem!important;padding-right:.75rem!important}
  .brand-text{font-size:1.35rem!important}
  .topbar-circle-btn{width:38px!important;height:38px!important;font-size:.9rem}
  .topbar-pill-btn{height:38px!important;padding:0 .65rem!important;font-size:.82rem!important;gap:.35rem!important}
  .topbar-user-pill{height:38px!important;padding:0!important;background:transparent!important;border:none!important}
  .topbar-user-avatar{width:36px!important;height:36px!important;font-size:.88rem;border-radius:11px!important}
  .topbar-divider{display:none!important}
  .topbar .notification-dropdown{position:fixed!important;top:62px!important;inset-inline-end:10px!important;inset-inline-start:auto!important;transform:none!important;width:calc(100vw - 20px)!important}
  .admin-main h1{font-size:1.3rem!important}
  .pagination{justify-content:center!important;gap:3px!important}
  .pagination .page-link{padding:.35rem .65rem!important;font-size:.78rem!important}
  .modal-dialog{margin:.75rem}
  .modal-header,.modal-body,.modal-footer{padding:1rem!important}
}

.topbar-actions-group{display:flex;align-items:center;gap:12px;flex-shrink:0}
@media(max-width:575.98px){.topbar-actions-group{gap:10px!important}}

body,button,input,select,textarea{font-family:"Tajawal",system-ui,sans-serif}
.kpi-card{height:100%;min-height:154px;padding:1.35rem 1.3rem;background:#fff;border:1px solid #e2ece9;border-radius:18px;box-shadow:0 3px 16px rgba(2,51,62,0.035);display:flex;flex-direction:column;justify-content:space-between;gap:1.15rem;text-decoration:none;color:inherit;position:relative;overflow:hidden;transition:transform .2s cubic-bezier(0.16,1,0.3,1),box-shadow .2s ease,border-color .2s ease}
a.kpi-card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(251,130,47,0.12);border-color:#ffd4b8;color:inherit}
.kpi-card .kpi-label{font-size:.94rem;font-weight:600;color:#4f686e;line-height:1.5}
.kpi-icon{flex-shrink:0;width:44px;height:44px;border-radius:13px;display:grid;place-items:center;color:var(--orange);background:#fff3eb;font-size:1.18rem;transition:background-color .18s ease,transform .18s ease,color .18s ease}
a.kpi-card:hover .kpi-icon{background:var(--orange);color:#fff;transform:scale(1.06)}
.kpi-value{font-size:2.1rem;font-weight:800;line-height:1.15;letter-spacing:.01em;color:var(--petrol);font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.kpi-currency{font-size:.82rem;font-weight:600;color:#71878d}
.dashboard-intro{padding:1.25rem 0}.dashboard-intro h1{font-weight:800}
@media(max-width:575.98px){.kpi-card{min-height:132px}.kpi-value{font-size:1.85rem}}

.topbar{position:sticky;top:0;z-index:1030;height:64px;min-height:64px;background:#fff;border-bottom:1px solid #eef2f1;box-shadow:0 1px 3px rgba(2,51,62,0.03);display:flex!important;flex-wrap:nowrap!important;align-items:center!important;justify-content:space-between!important}

/* Topbar Buttons & Pills */
.topbar-date-pill{height:40px;padding:0 .9rem;border-radius:11px;background:#f6f9f8;border:1px solid #dce7e4;color:var(--petrol);font-size:.84rem;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;white-space:nowrap;transition:all .18s ease}
.topbar-date-pill:hover{background:#edf6f3;border-color:#b4dad0}
.topbar-circle-btn{width:40px;height:40px;padding:0;display:inline-grid;place-items:center;border-radius:11px;background:#f6f9f8;border:1px solid #dce7e4;color:var(--petrol);font-size:.9rem;transition:all .18s ease;cursor:pointer;text-decoration:none}
.topbar-circle-btn:hover,.topbar-circle-btn:focus{background:#edf6f3;border-color:#b4dad0;color:var(--petrol);transform:translateY(-1px)}
.topbar-pill-btn{height:40px;padding:0 .85rem;border-radius:11px;background:#f6f9f8;border:1px solid #dce7e4;color:var(--petrol);font-size:.84rem;font-weight:600;display:inline-flex;align-items:center;gap:.45rem;transition:all .18s ease;cursor:pointer}
.topbar-pill-btn:hover,.topbar-pill-btn:focus{background:#edf6f3;border-color:#b4dad0;color:var(--petrol);transform:translateY(-1px)}

/* User Profile Pill */
.topbar-user-pill{height:42px;padding:0;padding-inline-start:.3rem;padding-inline-end:.75rem;border-radius:12px;background:#f6f9f8;border:1px solid #dce7e4;display:inline-flex;align-items:center;gap:.55rem;transition:all .18s ease;cursor:pointer;text-decoration:none}
.topbar-user-pill:hover,.topbar-user-pill:focus{background:#edf6f3;border-color:#b4dad0;box-shadow:0 2px 8px rgba(2,51,62,0.05);transform:translateY(-1px)}
.topbar-user-avatar{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--petrol),#035163);color:#fff;font-weight:800;font-size:.88rem;display:grid;place-items:center;text-transform:uppercase;box-shadow:0 2px 5px rgba(2,51,62,0.18)}
.topbar-user-name{font-size:.82rem;font-weight:700;color:var(--petrol);line-height:1.2;white-space:nowrap}
.topbar-user-role{font-size:.66rem;font-weight:500;color:#78949a;line-height:1.1;white-space:nowrap}
.topbar-user-chevron{font-size:.66rem;color:#8ea3a8;transition:transform .18s ease}
.topbar-divider{width:1px;height:22px;background:#e2ece9;margin:0 .2rem}

/* Notifications Badge Styling */
#notification-count{background:var(--orange)!important;color:#fff!important;font-size:.65rem;font-weight:800;padding:.22em .5em;border-radius:50rem;box-shadow:0 2px 6px rgba(251,130,47,0.4);border:2px solid #fff}
.topbar-user-dropdown{min-width:230px;border-radius:14px;box-shadow:0 14px 34px rgba(2,51,62,0.12)!important}
.topbar-user-dropdown .dropdown-item{font-size:.88rem;border-radius:8px;margin:2px 6px;width:calc(100% - 12px);transition:all .15s ease}
.topbar-user-dropdown .dropdown-item:hover{background:#f0f7f5;color:var(--petrol)}
.topbar-user-dropdown .dropdown-item.text-danger:hover{background:#fff0f0;color:#dc3545!important}
.lang-dropdown-menu{min-width:165px;border-radius:14px;padding:6px;box-shadow:0 12px 32px rgba(2,51,62,0.12)!important;border:1px solid #e3ece9!important}
.lang-dropdown-item{border-radius:9px;padding:.55rem .85rem;font-size:.86rem;color:var(--petrol);transition:all .15s ease;display:flex;align-items:center;justify-content:space-between;width:100%;border:none;background:transparent;text-decoration:none;font-weight:500;cursor:pointer}
.lang-dropdown-item:hover{background:#f0f6f4;color:var(--petrol)}
.lang-dropdown-item.is-active{background:#e8f4f0;color:var(--petrol);font-weight:700}
.lang-dropdown-item.is-active .lang-check{color:var(--orange);font-size:.8rem}

.notification-dropdown{width:min(360px,calc(100vw - 24px))}
#notification-list{max-height:380px;overflow-y:auto;overflow-wrap:anywhere}
@media(max-width:575.98px){.topbar .notification-dropdown{position:fixed!important;top:70px!important;inset-inline-end:12px!important;inset-inline-start:auto!important;transform:none!important}}
</style>
@stack('styles')
</head>
<body>
@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
@php
    $rawTitle = View::yieldContent('title', __('Rounder admin'));
    $cleanTitle = trim(str_replace(['· Rounder', '· Rounder', 'Rounder -', '· راوندر', '- راوندر', 'Rounder'], '', $rawTitle));
    if (empty($cleanTitle)) {
        $cleanTitle = __('Dashboard');
    }
@endphp
<nav class="navbar topbar px-3 px-lg-4 topbar-with-sidebar d-print-none">
<div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
<button class="topbar-circle-btn d-lg-none me-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-menu" aria-controls="admin-menu" aria-label="{{ __('Open menu') }}">
<i class="fa-solid fa-bars-staggered"></i>
</button>
<a class="navbar-brand brand d-lg-none d-flex align-items-center gap-2 m-0 text-decoration-none" href="{{ route('admin.dashboard') }}">
<span class="brand-text text-rounder-orange">rounder</span>
</a>
<div class="d-none d-lg-flex align-items-center gap-2">
<span class="d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;border-radius:8px;background:rgba(2,51,62,0.06);color:var(--petrol);">
<i class="fa-solid fa-layer-group" style="font-size:0.8rem;"></i>
</span>
<span class="fw-bold text-dark fs-6">{{ $cleanTitle }}</span>
</div>
</div>

{{-- Left Side: Date Pill, Divider, Notifications, Language Pill, Divider, User Pill --}}
<div class="topbar-actions-group">
<div class="topbar-date-pill d-none d-md-inline-flex">
<i class="fa-regular fa-calendar-days text-rounder-orange"></i>
<span>{{ \Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('d F Y') }}</span>
</div>

<div class="topbar-divider d-none d-md-block"></div>

@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
@include('admin.partials.notifications')
@endif

<div class="dropdown">
<button class="topbar-pill-btn px-2 px-sm-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Language') }}" title="{{ __('Language') }}">
<i class="fa-solid fa-globe opacity-75"></i>
<span class="d-none d-sm-inline">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
<span class="d-sm-none fw-bold small">{{ strtoupper(app()->getLocale()) }}</span>
<i class="fa-solid fa-chevron-down small opacity-50 d-none d-sm-inline" style="font-size:0.7rem;"></i>
</button>
<ul class="dropdown-menu dropdown-menu-end shadow border-0 p-1.5 lang-dropdown-menu">
<li>
<form method="post" action="{{ route('locale.update') }}">
@csrf
<input type="hidden" name="locale" value="ar">
<button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'ar' ? 'is-active' : '' }}">
<span class="d-flex align-items-center gap-2"><span>🇸🇦</span> <span>العربية</span></span>
@if(app()->getLocale() === 'ar')<i class="fa-solid fa-check lang-check"></i>@endif
</button>
</form>
</li>
<li>
<form method="post" action="{{ route('locale.update') }}">
@csrf
<input type="hidden" name="locale" value="en">
<button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
<span class="d-flex align-items-center gap-2"><span>🇬🇧</span> <span>English</span></span>
@if(app()->getLocale() === 'en')<i class="fa-solid fa-check lang-check"></i>@endif
</button>
</form>
</li>
</ul>
</div>

@if(auth()->check())
@php
    $currentUserTop = auth()->user();
    $initialLetter = mb_substr($currentUserTop->name, 0, 1);
@endphp
<div class="topbar-divider d-none d-sm-block"></div>

<div class="dropdown">
<button class="topbar-user-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ $currentUserTop->name }}" title="{{ $currentUserTop->name }}">
<div class="topbar-user-avatar">{{ $initialLetter }}</div>
<div class="d-none d-md-block" style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};">
<div class="topbar-user-name">{{ $currentUserTop->name }}</div>
<div class="topbar-user-role">{{ __('Administrator') }}</div>
</div>
<i class="fa-solid fa-chevron-down topbar-user-chevron ms-1 d-none d-md-inline"></i>
</button>
<ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 topbar-user-dropdown">
<li class="px-3 py-2 border-bottom mb-1">
<div class="fw-bold text-dark">{{ $currentUserTop->name }}</div>
<div class="small text-secondary">{{ $currentUserTop->email ?? '' }}</div>
<span class="badge mt-1" style="background: rgba(2,51,62,0.08); color: var(--petrol); font-size: 0.72rem; font-weight: 600;">{{ __('Administrator') }}</span>
</li>
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute($currentUserTop, 'admin.permissions.index', 'GET'))
<li>
<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.permissions.index') }}">
<i class="fa-solid fa-shield-halved text-secondary" style="width:1.2rem;"></i>
<span>{{ __('Permissions') }}</span>
</a>
</li>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute($currentUserTop, 'admin.about', 'GET'))
<li>
<a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.about') }}">
<i class="fa-solid fa-circle-info text-secondary" style="width:1.2rem;"></i>
<span>{{ __('About Rounder') }}</span>
</a>
</li>
@endif
<li><hr class="dropdown-divider my-1"></li>
<li>
<form method="post" action="{{ route('admin.logout') }}" class="m-0">
@csrf
<button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
<i class="fa-solid fa-arrow-right-from-bracket" style="width:1.2rem;"></i>
<span>{{ __('Sign out') }}</span>
</button>
</form>
</li>
</ul>
</div>
@endif
</div>
</nav>
@endif
@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
@php
    $permService = app(\App\Modules\Access\Services\AdminPermissionService::class);
    $currentUser = auth()->user();

    $isServicesActive = request()->routeIs('admin.service-categories.*') || request()->routeIs('admin.services.*') || request()->routeIs('admin.sub-services.*') || request()->routeIs('admin.prices.*');
    $isOrdersActive = request()->routeIs('admin.service-orders.*') || request()->routeIs('admin.emergencies.*') || request()->routeIs('admin.bookings.*') || request()->routeIs('admin.order-statuses.*');
    $isPackagesActive = request()->routeIs('admin.packages.*') || request()->routeIs('admin.package-features.*') || request()->routeIs('admin.subscriptions.*');
    $isPropertiesActive = request()->routeIs('admin.realestate.*') || request()->routeIs('admin.regions.*') || request()->routeIs('admin.cities.*');
    $isUsersActive = request()->routeIs('admin.providers.*') || request()->routeIs('admin.residents.*') || request()->routeIs('admin.support-tickets.*');
    $isSettingsActive = request()->routeIs('admin.permissions.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.pages.*') || request()->routeIs('admin.content.*') || request()->routeIs('admin.company-settings.*');
@endphp
<aside class="offcanvas-lg offcanvas-start admin-sidebar d-print-none" tabindex="-1" id="admin-menu" aria-labelledby="admin-menu-title">
<div class="sidebar-brand-header d-flex align-items-center justify-content-between px-3 px-lg-4">
    <a class="navbar-brand brand d-flex align-items-center gap-2 m-0 text-decoration-none" href="{{ route('admin.dashboard') }}">
        <span class="brand-text text-rounder-orange">rounder</span>
        <span class="badge rounded-pill brand-badge-mint">{{ __('Rounder admin') }}</span>
    </a>
    <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#admin-menu" aria-label="{{ __('Close menu') }}"></button>
</div>
<div class="offcanvas-body d-flex flex-column p-3"><nav aria-label="{{ __('Administration navigation') }}" class="w-100 flex-grow-1 d-flex flex-column">

{{-- 1. لوحة التحكم --}}
@if($permService->allowsRoute($currentUser, 'admin.dashboard', 'GET'))
<a class="dashboard-link mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
    <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
    <span>{{ __('Dashboard') }}</span>
</a>
@endif

<div class="accordion sidebar-accordion" id="sidebarMenuAccordion">

{{-- 2. الخدمات والتسعير --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.service-categories.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.service-categories.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.services.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.services.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.sub-services.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.sub-services.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.prices.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.prices.create', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isServicesActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-services" aria-expanded="{{ $isServicesActive ? 'true' : 'false' }}" aria-controls="menu-services">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-layer-group group-icon"></i>
            <span>{{ __('Services and pricing') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-services" class="collapse {{ $isServicesActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.service-categories.index', 'GET'))
                <a class="{{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}"><i class="fa-solid fa-folder-tree me-2" aria-hidden="true"></i>{{ __('Service categories') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.service-categories.create', 'GET'))
                <a class="{{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.create') }}"><i class="fa-solid fa-folder-tree me-2" aria-hidden="true"></i>{{ __('Service categories') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.services.index', 'GET'))
                <a class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><i class="fa-solid fa-screwdriver-wrench me-2" aria-hidden="true"></i>{{ __('Available services') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.services.create', 'GET'))
                <a class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.create') }}"><i class="fa-solid fa-screwdriver-wrench me-2" aria-hidden="true"></i>{{ __('Available services') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.sub-services.index', 'GET'))
                <a class="{{ request()->routeIs('admin.sub-services.*') ? 'active' : '' }}" href="{{ route('admin.sub-services.index') }}"><i class="fa-solid fa-list-check me-2" aria-hidden="true"></i>{{ __('Sub services') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.sub-services.create', 'GET'))
                <a class="{{ request()->routeIs('admin.sub-services.*') ? 'active' : '' }}" href="{{ route('admin.sub-services.create') }}"><i class="fa-solid fa-list-check me-2" aria-hidden="true"></i>{{ __('Sub services') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.prices.index', 'GET'))
                <a class="{{ request()->routeIs('admin.prices.*') ? 'active' : '' }}" href="{{ route('admin.prices.index') }}"><i class="fa-solid fa-tags me-2" aria-hidden="true"></i>{{ __('Prices') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.prices.create', 'GET'))
                <a class="{{ request()->routeIs('admin.prices.*') ? 'active' : '' }}" href="{{ route('admin.prices.create') }}"><i class="fa-solid fa-tags me-2" aria-hidden="true"></i>{{ __('Prices') }}</a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- 3. الطلبات والحالات --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.service-orders.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.service-orders.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.emergencies.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.emergencies.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.order-statuses.index', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isOrdersActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-orders" aria-expanded="{{ $isOrdersActive ? 'true' : 'false' }}" aria-controls="menu-orders">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-bell group-icon"></i>
            <span>{{ __('Orders and emergencies') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-orders" class="collapse {{ $isOrdersActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.service-orders.index', 'GET'))
                <a class="{{ request()->routeIs('admin.service-orders.*') || request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.service-orders.index') }}"><i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>{{ __('Service orders') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.service-orders.create', 'GET'))
                <a class="{{ request()->routeIs('admin.service-orders.*') || request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.service-orders.create') }}"><i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>{{ __('Service orders') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.emergencies.index', 'GET'))
                <a class="{{ request()->routeIs('admin.emergencies.*') ? 'active' : '' }}" href="{{ route('admin.emergencies.index') }}"><i class="fa-solid fa-truck-medical me-2" aria-hidden="true"></i>{{ __('Emergency requests') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.emergencies.create', 'GET'))
                <a class="{{ request()->routeIs('admin.emergencies.*') ? 'active' : '' }}" href="{{ route('admin.emergencies.create') }}"><i class="fa-solid fa-truck-medical me-2" aria-hidden="true"></i>{{ __('Emergency requests') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.order-statuses.index', 'GET'))
                <a class="{{ request()->routeIs('admin.order-statuses.*') ? 'active' : '' }}" href="{{ route('admin.order-statuses.index') }}"><i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>حالات الطلبات والطوارئ</a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- 4. الباقات والاشتراكات --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.packages.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.packages.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.package-features.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.package-features.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.subscriptions.index', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isPackagesActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-packages" aria-expanded="{{ $isPackagesActive ? 'true' : 'false' }}" aria-controls="menu-packages">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-box-open group-icon"></i>
            <span>{{ __('Packages and subscriptions') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-packages" class="collapse {{ $isPackagesActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.packages.index', 'GET'))
                <a class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}"><i class="fa-solid fa-boxes-stacked me-2" aria-hidden="true"></i>{{ __('Customer packages') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.packages.create', 'GET'))
                <a class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.create') }}"><i class="fa-solid fa-boxes-stacked me-2" aria-hidden="true"></i>{{ __('Customer packages') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.package-features.index', 'GET'))
                <a class="{{ request()->routeIs('admin.package-features.*') ? 'active' : '' }}" href="{{ route('admin.package-features.index') }}"><i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>{{ __('Package comparison features') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.package-features.create', 'GET'))
                <a class="{{ request()->routeIs('admin.package-features.*') ? 'active' : '' }}" href="{{ route('admin.package-features.create') }}"><i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>{{ __('Package comparison features') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.subscriptions.index', 'GET'))
                <a class="{{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}"><i class="fa-solid fa-repeat me-2" aria-hidden="true"></i>{{ __('Customer subscriptions') }}</a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- 5. العقارات والمواقع --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.realestate.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.realestate.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.regions.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.regions.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.cities.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.cities.create', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isPropertiesActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-properties" aria-expanded="{{ $isPropertiesActive ? 'true' : 'false' }}" aria-controls="menu-properties">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-map-location-dot group-icon"></i>
            <span>{{ __('Properties and locations') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-properties" class="collapse {{ $isPropertiesActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.realestate.index', 'GET'))
                <a class="{{ request()->routeIs('admin.realestate.*') ? 'active' : '' }}" href="{{ route('admin.realestate.index') }}"><i class="fa-solid fa-city me-2" aria-hidden="true"></i>{{ __('Homes') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.realestate.create', 'GET'))
                <a class="{{ request()->routeIs('admin.realestate.*') ? 'active' : '' }}" href="{{ route('admin.realestate.create') }}"><i class="fa-solid fa-city me-2" aria-hidden="true"></i>{{ __('Homes') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.regions.index', 'GET'))
                <a class="{{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.index') }}"><i class="fa-solid fa-map me-2" aria-hidden="true"></i>{{ __('Regions') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.regions.create', 'GET'))
                <a class="{{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.create') }}"><i class="fa-solid fa-map me-2" aria-hidden="true"></i>{{ __('Regions') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.cities.index', 'GET'))
                <a class="{{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.index') }}"><i class="fa-solid fa-tree-city me-2" aria-hidden="true"></i>{{ __('Cities') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.cities.create', 'GET'))
                <a class="{{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.create') }}"><i class="fa-solid fa-tree-city me-2" aria-hidden="true"></i>{{ __('Cities') }}</a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- 6. المستخدمون والشركاء --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.providers.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.providers.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.residents.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.support-tickets.index', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isUsersActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-users" aria-expanded="{{ $isUsersActive ? 'true' : 'false' }}" aria-controls="menu-users">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-users-viewfinder group-icon"></i>
            <span>{{ __('Users and providers') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-users" class="collapse {{ $isUsersActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.providers.index', 'GET'))
                <a class="{{ request()->routeIs('admin.providers.*') ? 'active' : '' }}" href="{{ route('admin.providers.index') }}"><i class="fa-solid fa-building-shield me-2" aria-hidden="true"></i>{{ __('Registered providers') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.providers.create', 'GET'))
                <a class="{{ request()->routeIs('admin.providers.*') ? 'active' : '' }}" href="{{ route('admin.providers.create') }}"><i class="fa-solid fa-building-shield me-2" aria-hidden="true"></i>{{ __('Registered providers') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.residents.index', 'GET'))
                <a class="{{ request()->routeIs('admin.residents.*') ? 'active' : '' }}" href="{{ route('admin.residents.index') }}"><i class="fa-solid fa-users me-2" aria-hidden="true"></i>{{ __('Residents') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.support-tickets.index', 'GET'))
                <a class="{{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}" href="{{ route('admin.support-tickets.index') }}"><i class="fa-solid fa-headset me-2" aria-hidden="true"></i>تذاكر ودعم السكان</a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- 7. النظام والإعدادات --}}
@if(
    $permService->allowsRoute($currentUser, 'admin.users.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.users.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.permissions.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.permissions.create', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.company-settings.edit', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.pages.index', 'GET') ||
    $permService->allowsRoute($currentUser, 'admin.content.index', 'GET')
)
<div class="sidebar-group">
    <button class="sidebar-group-btn {{ $isSettingsActive ? 'has-active-child' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#menu-settings" aria-expanded="{{ $isSettingsActive ? 'true' : 'false' }}" aria-controls="menu-settings">
        <span class="d-flex align-items-center">
            <i class="fa-solid fa-gears group-icon"></i>
            <span>{{ __('System and settings') }}</span>
        </span>
        <i class="fa-solid fa-chevron-down chevron-icon"></i>
    </button>
    <div id="menu-settings" class="collapse {{ $isSettingsActive ? 'show' : '' }}" data-bs-parent="#sidebarMenuAccordion">
        <div class="sidebar-sublinks">
            @if($permService->allowsRoute($currentUser, 'admin.users.index', 'GET'))
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users-gear me-2" aria-hidden="true"></i>{{ __('Administrators') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.users.create', 'GET'))
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.create') }}"><i class="fa-solid fa-users-gear me-2" aria-hidden="true"></i>{{ __('Administrators') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.permissions.index', 'GET'))
                <a class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>{{ __('Permissions') }}</a>
            @elseif($permService->allowsRoute($currentUser, 'admin.permissions.create', 'GET'))
                <a class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.create') }}"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>{{ __('Permissions') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.company-settings.edit', 'GET'))
                <a class="{{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}" href="{{ route('admin.company-settings.edit') }}"><i class="fa-solid fa-building-circle-check me-2" aria-hidden="true"></i>{{ __('Company settings') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.pages.index', 'GET'))
                <a class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-file-contract me-2" aria-hidden="true"></i>{{ __('Pages and policies') }}</a>
            @endif

            @if($permService->allowsRoute($currentUser, 'admin.content.index', 'GET'))
                <a class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}" href="{{ route('admin.content.index') }}"><i class="fa-solid fa-people-roof me-2" aria-hidden="true"></i>{{ __('Resident operations') }}</a>
            @endif
        </div>
    </div>
</div>
@endif

</div>{{-- /.accordion --}}

{{-- 8. عن راوندر --}}
@if($permService->allowsRoute($currentUser, 'admin.about', 'GET'))
<div class="pt-2 mt-2 border-top border-white border-opacity-10">
    <a class="dashboard-link {{ request()->routeIs('admin.about') ? 'active' : '' }}" href="{{ route('admin.about') }}">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        <span>{{ __('About Rounder') }}</span>
    </a>
</div>
@endif

</nav></div>
</aside>
@endif
<div class="{{ auth()->user() instanceof \App\Modules\Identity\Models\Admin ? 'admin-main' : '' }}">
@yield('body')
</div>
<div class="modal fade" id="delete-confirmation" tabindex="-1" aria-labelledby="delete-confirmation-title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
<div class="modal-content border-0 shadow-lg text-center" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
    <div style="height: 5px; background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);"></div>
    <div class="p-4 pt-4 pb-3">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: #fee2e2; color: #dc2626; box-shadow: 0 0 0 8px #fef2f2;">
            <i class="fa-solid fa-trash-can" style="font-size: 1.85rem;"></i>
        </div>
        <h2 class="h5 fw-bold text-dark mb-2" id="delete-confirmation-title">{{ __('Confirm deletion') }}</h2>
        <p class="text-secondary small mb-4 px-2" style="line-height: 1.65; font-size: 0.92rem;">
            {{ __('Are you sure you want to delete this record? This action cannot be undone.') }}
        </p>
        <div class="d-flex align-items-center justify-content-center gap-2">
            <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="border-radius: 12px; min-width: 120px;">
                <i class="fa-solid fa-xmark me-1"></i> {{ __('Cancel') }}
            </button>
            <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm" id="confirm-delete" style="border-radius: 12px; min-width: 130px; background: #dc2626; border-color: #dc2626; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.28);">
                <i class="fa-solid fa-trash-can me-1"></i> {{ __('Yes, delete') }}
            </button>
        </div>
    </div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><script>
(() => {
    let pendingForm = null;
    const node = document.getElementById('delete-confirmation');
    const modal = bootstrap.Modal.getOrCreateInstance(node);
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-confirm-delete]');
        if (!button) return;
        pendingForm = button.closest('.delete-record-form');
        if (pendingForm) modal.show();
    });
    document.getElementById('confirm-delete').addEventListener('click', function () {
        if (!pendingForm) return;
        this.disabled = true;
        HTMLFormElement.prototype.submit.call(pendingForm);
    });
    node.addEventListener('hidden.bs.modal', () => {
        pendingForm = null; document.getElementById('confirm-delete').disabled = false;
    });
})();
</script>
@stack('scripts')</body></html>
