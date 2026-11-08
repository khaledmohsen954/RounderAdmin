@extends('layouts.admin')
@section('title', 'تذاكر ورسائل الدعم الفني · Rounder')
@section('body')
<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge rounded-pill" style="background: rgba(15, 76, 92, 0.1); color: #0f4c5c; font-weight: 700;">
                    <i class="fa-solid fa-headset me-1"></i> خدمة العملاء والدعم
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1 text-dark">تذاكر ورسائل الدعم الفني</h1>
            <p class="text-secondary mb-0 small">متابعة رسائل واستفسارات السكان الواردة عبر شاشة "تواصل معنا" بتطبيق الجوال والرد عليها.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                <i class="fa-solid fa-inbox text-secondary me-1"></i> إجمالي الرسائل: <strong class="text-dark">{{ $counts['total'] }}</strong>
            </span>
            <a href="{{ route('admin.company-settings.edit') }}#contact-settings" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="fa-solid fa-gear me-1"></i> إعدادات طرق التواصل
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background: #ffffff; border-right: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold">جديدة (قيد الانتظار)</div>
                        <div class="h4 fw-bold mb-0 text-dark mt-1">{{ $counts['open'] }}</div>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:40px;height:40px;background:#fef3c7;color:#b45309;">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background: #ffffff; border-right: 4px solid #0284c7 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold">قيد المتابعة</div>
                        <div class="h4 fw-bold mb-0 text-dark mt-1">{{ $counts['in_progress'] }}</div>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:40px;height:40px;background:#e0f2fe;color:#0369a1;">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background: #ffffff; border-right: 4px solid #10b981 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold">تم الرد والحل</div>
                        <div class="h4 fw-bold mb-0 text-dark mt-1">{{ $counts['resolved'] }}</div>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:40px;height:40px;background:#dcfce7;color:#15803d;">
                        <i class="fa-solid fa-check-double"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="background: #ffffff; border-right: 4px solid #64748b !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small fw-bold">مغلقة</div>
                        <div class="h4 fw-bold mb-0 text-dark mt-1">{{ $counts['closed'] }}</div>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:40px;height:40px;background:#f1f5f9;color:#475569;">
                        <i class="fa-solid fa-folder-closed"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3" style="background: #ffffff;">
        <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control border-start-0" placeholder="بحث بالرقم المرجعي، الساكن، الموضوع...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="category" class="form-select">
                    <option value="all">كل التصنيفات</option>
                    <option value="order_issue" @selected(($filters['category'] ?? '') === 'order_issue')>مشكلة في طلب</option>
                    <option value="payment" @selected(($filters['category'] ?? '') === 'payment')>الدفع</option>
                    <option value="housing" @selected(($filters['category'] ?? '') === 'housing')>السكن</option>
                    <option value="other" @selected(($filters['category'] ?? '') === 'other')>أخرى</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="all">كل الحالات</option>
                    <option value="open" @selected(($filters['status'] ?? '') === 'open')>جديدة</option>
                    <option value="in_progress" @selected(($filters['status'] ?? '') === 'in_progress')>قيد المتابعة</option>
                    <option value="resolved" @selected(($filters['status'] ?? '') === 'resolved')>تم الرد والحل</option>
                    <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>مغلقة</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3">
                    <i class="fa-solid fa-filter me-1"></i> تصفية
                </button>
                @if(!empty($filters['q']) || (!empty($filters['category']) && $filters['category'] !== 'all') || (!empty($filters['status']) && $filters['status'] !== 'all'))
                    <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-outline-secondary rounded-3" title="إعادة الضبط">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tickets Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff;">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 140px;">الرقم المرجعي</th>
                        <th>الساكن</th>
                        <th style="width: 130px;">التصنيف</th>
                        <th>الموضوع والمشكلة</th>
                        <th class="text-center" style="width: 90px;">المرفق</th>
                        <th style="width: 120px;">الحالة</th>
                        <th style="width: 130px;">تاريخ الإرسال</th>
                        <th class="text-center pe-4" style="width: 100px;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        @php
                            $categoryPillStyle = match($ticket->category) {
                                'order_issue' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
                                'payment' => 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                'housing' => 'background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;',
                                default => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="fw-bold font-monospace text-decoration-none" style="color: #0f4c5c;">
                                    #{{ $ticket->reference }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $ticket->user?->name ?? 'مستخدم غير معروف' }}</div>
                                @if($ticket->user?->phone)
                                    <div class="small text-secondary font-monospace" dir="ltr">
                                        <i class="fa-solid fa-phone me-1 text-muted" style="font-size: 0.75rem;"></i>{{ $ticket->user->phone }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill fw-semibold px-2 py-1" style="{{ $categoryPillStyle }}">
                                    {{ $ticket->category_label }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $ticket->subject }}">
                                    {{ $ticket->subject }}
                                </div>
                                <div class="small text-secondary text-truncate" style="max-width: 280px;" title="{{ $ticket->message }}">
                                    {{ $ticket->message }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($ticket->attachment_url)
                                    <a href="{{ $ticket->attachment_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-pill p-1 px-2" title="عرض الصورة المرفقة">
                                        <i class="fa-solid fa-paperclip text-primary me-1"></i>صورة
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1 {{ $ticket->status_badge_class }}">
                                    {{ $ticket->status_label }}
                                </span>
                                @if($ticket->admin_reply)
                                    <div class="small text-success mt-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-circle-check me-1"></i>تم الرد
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark font-monospace">{{ $ticket->created_at?->format('Y-m-d') }}</div>
                                <div class="small text-secondary font-monospace" style="font-size: 0.75rem;">{{ $ticket->created_at?->format('H:i') }}</div>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                                    <i class="fa-solid fa-eye me-1"></i> عرض
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fa-regular fa-comment-dots text-secondary mb-3" style="font-size: 2.8rem;"></i>
                                    <h5 class="fw-bold text-secondary">لا توجد رسائل دعم فني حالياً</h5>
                                    <p class="text-muted small mb-0">ستظهر هنا الرسائل والتذاكر المرسلة من السكان فور ورودها من التطبيق.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</main>
@endsection

