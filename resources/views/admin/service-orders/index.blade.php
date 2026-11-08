@extends('layouts.admin')
@section('title', __('Service orders').' · Rounder')
@section('body')
<div class="container-fluid">
    <div class="row g-3 p-3 p-lg-4">
        <main class="col-12 content">
            <!-- Header Section -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="small text-secondary fw-medium">{{ __('Provider assignment and commissions') }}</div>
                    <h1 class="h3 fw-bold mb-0 text-dark">{{ __('Service orders') }}</h1>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- View Mode Switcher -->
                    <div class="btn-group shadow-sm" role="group" aria-label="{{ __('View mode') }}" style="border-radius: 10px; overflow: hidden;">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btnViewTable" onclick="switchOrdersView('table')" style="padding: 0.45rem 0.95rem; font-weight: 600; font-size: 0.82rem;">
                            <i class="fa-solid fa-table-list me-1"></i> {{ __('Table view') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnViewCards" onclick="switchOrdersView('cards')" style="padding: 0.45rem 0.95rem; font-weight: 600; font-size: 0.82rem;">
                            <i class="fa-solid fa-table-cells-large me-1"></i> {{ __('Cards view') }}
                        </button>
                    </div>

                    @adminCan('admin.service-orders.create')
                    <a class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm" href="{{ route('admin.service-orders.create') }}" style="border-radius: 10px; padding: 0.45rem 1rem; font-weight: 600;">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('Create service order') }}</span>
                    </a>
                    @endadminCan
                </div>
            </div>

            @include('admin.partials.feedback')

            <!-- ========================================== -->
            <!-- 1. CLEAN EXECUTIVE TABLE VIEW (DEFAULT)    -->
            <!-- ========================================== -->
            <div id="ordersTableView" class="orders-view-pane">
                <div class="panel p-3 p-lg-4 mb-4" style="border-radius: 14px;">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="min-width: 860px;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5ede9;">
                                    <th style="width: 14%;">{{ __('Order') }}</th>
                                    <th style="width: 16%;">{{ __('Resident and home') }}</th>
                                    <th style="width: 18%;">{{ __('Service') }}</th>
                                    <th style="width: 22%;">{{ __('Assigned provider') }}</th>
                                    <th style="width: 8%;">{{ __('Photos') }}</th>
                                    <th style="width: 11%;">{{ __('Status') }}</th>
                                    <th style="width: 11%;" class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    @php
                                        $statusColor = match($order->status_code) {
                                            'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                                            'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                                            'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                                            default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                                        };
                                        try {
                                            $schedDate = !empty($order->scheduled_at) ? \Carbon\Carbon::parse($order->scheduled_at)->translatedFormat('d M Y · h:i A') : '—';
                                        } catch (\Throwable $e) {
                                            $schedDate = $order->scheduled_at;
                                        }
                                    @endphp
                                    <tr style="border-bottom: 1px solid #edf3f0;">
                                        <!-- 1. Order ID & Schedule -->
                                        <td class="py-3">
                                            <div class="d-flex align-items-center gap-1.5 mb-1">
                                                <a href="{{ route('admin.service-orders.show', $order->id) }}" class="badge text-decoration-none shadow-sm" style="background: var(--petrol); color: #fff; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 7px; font-weight: 700;" title="{{ __('View details') }}">
                                                    #{{ $order->id }}
                                                </a>
                                            </div>
                                            <div class="small text-secondary" style="font-size: 0.76rem;">
                                                <i class="fa-regular fa-calendar text-rounder-orange me-1"></i>{{ $schedDate }}
                                            </div>
                                            @if($order->notes)
                                                <div class="small text-muted text-truncate mt-1" style="max-width: 160px; font-size: 0.74rem;" title="{{ $order->notes }}">
                                                    <i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i>{{ $order->notes }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- 2. Resident & Home -->
                                        <td class="py-3">
                                            <div class="fw-bold text-dark fs-6">{{ $order->resident_name }}</div>
                                            <div class="small text-secondary mt-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $order->home_name }}
                                            </div>
                                        </td>

                                        <!-- 3. Service & Price -->
                                        <td class="py-3">
                                            <div>
                                                <a href="{{ route('admin.service-orders.show', $order->id) }}" class="fw-bold text-dark text-decoration-none" title="{{ __('View details') }}">
                                                    {{ $order->service_name ?? $order->service_code }}
                                                </a>
                                            </div>
                                            @if($order->sub_service_name || $order->sub_service_code)
                                                <div class="mt-1">
                                                    <span class="badge" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-weight: 600; padding: 0.25rem 0.55rem; border-radius: 6px; font-size: 0.74rem;">
                                                        {{ $order->sub_service_name ?? $order->sub_service_code }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="fw-bold mt-1" style="color: var(--petrol); font-size: 0.92rem;">
                                                {{ number_format($order->total_amount, 2) }}
                                                <span class="small fw-normal text-secondary" style="font-size: 0.76rem;">{{ __('SAR') }}</span>
                                            </div>
                                        </td>

                                        <!-- 4. Assigned Provider -->
                                        <td class="py-3">
                                            @if($order->provider_name)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: rgba(251, 130, 47, 0.1); color: var(--orange);">
                                                        <i class="fa-solid fa-truck-fast" style="font-size: 0.85rem;"></i>
                                                    </div>
                                                    <div style="min-width: 0;">
                                                        <div class="fw-bold text-dark small" style="line-height: 1.35;">{{ $order->provider_name }}</div>
                                                        <span class="badge mt-1" style="background: #eef8f4; color: #0e6c51; font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem;">
                                                            <i class="fa-solid fa-check-circle me-1"></i>{{ __('Assigned') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge" style="background: #f4f7f6; color: #768882; font-weight: 500; font-size: 0.76rem; border: 1px dashed #cddad5; padding: 0.35rem 0.65rem;">
                                                    <i class="fa-solid fa-user-clock me-1"></i>{{ __('Not assigned yet') }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- 5. Photos -->
                                        <td class="py-3">
                                            @if($order->bookingMedia && $order->bookingMedia->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 90px;">
                                                    @foreach($order->bookingMedia as $media)
                                                        <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" title="{{ __('View full image') }}" class="d-inline-block rounded-2 overflow-hidden border shadow-sm">
                                                            <img src="{{ $media->url }}" alt="Photo" width="34" height="34" style="object-fit: cover;">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-secondary small">—</span>
                                            @endif
                                        </td>

                                        <!-- 6. Status -->
                                        <td class="py-3">
                                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['color'] }}; border: 1px solid {{ $statusColor['border'] }}; font-size: 0.78rem; font-weight: 600; padding: 0.35rem 0.75rem;">
                                                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor['dot'] }};"></span>
                                                {{ $order->status_name }}
                                            </span>
                                        </td>

                                        <!-- 7. Actions -->
                                        <td class="py-3 text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1.5 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-popper-config='{"strategy":"fixed"}' style="border-radius: 9px; font-weight: 600; font-size: 0.8rem; padding: 0.38rem 0.85rem; border-color: #d1dfda; background: #ffffff; color: var(--petrol); white-space: nowrap;">
                                                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                                                    <span>{{ __('Actions') }}</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; font-size: 0.84rem; min-width: 185px; padding: 0.45rem; box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important; z-index: 1060;">
                                                    @adminCan('admin.service-orders.update','PUT')
                                                    <li>
                                                        <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2" data-bs-toggle="modal" data-bs-target="#orderUpdateModal-{{ $order->id }}">
                                                            <i class="fa-solid fa-sliders text-rounder-orange" style="width: 16px;"></i>
                                                            <span class="fw-semibold">{{ __('Assign & Update') }}</span>
                                                        </button>
                                                    </li>
                                                    @endadminCan

                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2" href="{{ route('admin.service-orders.show', $order->id) }}">
                                                            <i class="fa-solid fa-eye text-primary" style="width: 16px;"></i>
                                                            <span class="fw-semibold">{{ __('View details') }}</span>
                                                        </a>
                                                    </li>

                                                    @adminCan('admin.service-orders.destroy','DELETE')
                                                    <li><hr class="dropdown-divider my-1" style="border-color: #edf3f0;"></li>
                                                    <li>
                                                        <form method="post" action="{{ route('admin.service-orders.destroy', $order->id) }}" class="m-0 delete-record-form">
                                                            @csrf @method('DELETE')
                                                            <button type="button" data-confirm-delete class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 text-danger">
                                                                <i class="fa-solid fa-trash-can" style="width: 16px;"></i>
                                                                <span class="fw-semibold">{{ __('Delete') }}</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @endadminCan
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-secondary">
                                            <i class="fa-solid fa-clipboard-list fs-1 mb-2 text-secondary opacity-50"></i>
                                            <div>{{ __('No service orders found') }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- 2. LUXURY CARDS VIEW                       -->
            <!-- ========================================== -->
            <div id="ordersCardsView" class="orders-view-pane d-none">
                @forelse($orders as $order)
                    @php
                        $statusColor = match($order->status_code) {
                            'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                            'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                            'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                            default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                        };
                        try {
                            $schedDate = !empty($order->scheduled_at) ? \Carbon\Carbon::parse($order->scheduled_at)->translatedFormat('d M Y · h:i A') : '—';
                        } catch (\Throwable $e) {
                            $schedDate = $order->scheduled_at;
                        }
                    @endphp
                    <div class="card border-0 shadow-sm mb-3 order-card" style="border-radius: 14px; overflow: hidden; border: 1px solid #e2ece8 !important; background: #ffffff;">
                        <!-- Card Header -->
                        <div class="card-header bg-white py-3 px-3 px-lg-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-color: #eaf1ee !important;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge" style="background: var(--petrol); color: #fff; font-size: 0.85rem; padding: 0.42rem 0.8rem; border-radius: 9px; font-weight: 700;">
                                    #{{ $order->id }}
                                </span>
                                <span class="d-inline-flex align-items-center gap-1 text-secondary small fw-medium" style="background: #f7faf9; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid #e1ece7;">
                                    <i class="fa-regular fa-calendar text-rounder-orange"></i>
                                    <span>{{ $schedDate }}</span>
                                </span>
                                <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['color'] }}; border: 1px solid {{ $statusColor['border'] }}; font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusColor['dot'] }};"></span>
                                    {{ $order->status_name }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @adminCan('admin.service-orders.update','PUT')
                                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#orderUpdateModal-{{ $order->id }}" style="border-radius: 8px; font-weight: 600; padding: 0.35rem 0.8rem; font-size: 0.82rem;">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span>{{ __('Assign & Update') }}</span>
                                </button>
                                @endadminCan

                                <a href="{{ route('admin.service-orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600; padding: 0.35rem 0.8rem; font-size: 0.82rem;">
                                    <i class="fa-solid fa-eye text-rounder-orange"></i>
                                    <span>{{ __('View details') }}</span>
                                </a>

                                @adminCan('admin.service-orders.destroy','DELETE')
                                <form method="post" action="{{ route('admin.service-orders.destroy', $order->id) }}" class="d-inline-flex m-0 delete-record-form">
                                    @csrf @method('DELETE')
                                    <button type="button" data-confirm-delete class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center shadow-sm" title="{{ __('Delete') }}" style="width: 34px; height: 34px; border-radius: 8px; padding: 0; border-color: #fca5a5;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endadminCan
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 p-lg-4">
                            <div class="row g-3">
                                <!-- Resident & Home -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 h-100" style="background: #f8faf9; border: 1px solid #e5ede9;">
                                        <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-user me-1 text-rounder-orange"></i> {{ __('Resident and home') }}
                                        </div>
                                        <div class="fw-bold text-dark fs-6">{{ $order->resident_name }}</div>
                                        <div class="small text-secondary mt-1">
                                            <i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $order->home_name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Service & Pricing -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 h-100" style="background: #f8faf9; border: 1px solid #e5ede9;">
                                        <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-screwdriver-wrench me-1 text-rounder-orange"></i> {{ __('Service') }}
                                        </div>
                                        <div class="fw-bold text-dark fs-6">{{ $order->service_name ?? $order->service_code }}</div>
                                        @if($order->sub_service_name || $order->sub_service_code)
                                            <div class="mt-1">
                                                <span class="badge" style="background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.76rem;">
                                                    {{ $order->sub_service_name ?? $order->sub_service_code }}
                                                </span>
                                            </div>
                                        @endif
                                        <div class="fw-bold fs-6 mt-2" style="color: var(--petrol);">
                                            {{ number_format($order->total_amount, 2) }}
                                            <span class="small fw-normal text-secondary">{{ __('SAR') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assigned Provider (Full Width in Box - Zero Truncation!) -->
                                <div class="col-md-4">
                                    <div class="p-3 rounded-3 h-100" style="background: #f8faf9; border: 1px solid #e5ede9;">
                                        <div class="small text-secondary mb-1 fw-medium" style="font-size: 0.78rem;">
                                            <i class="fa-solid fa-truck-fast me-1 text-rounder-orange"></i> {{ __('Assigned provider') }}
                                        </div>
                                        @if($order->provider_name)
                                            <div class="fw-bold text-dark fs-6" style="line-height: 1.4;">{{ $order->provider_name }}</div>
                                            <div class="mt-1">
                                                <span class="badge" style="background: #eef8f4; color: #0e6c51; font-weight: 600; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-check-circle me-1"></i> {{ __('Assigned') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-secondary small fst-italic mt-1">
                                                <i class="fa-solid fa-user-clock me-1"></i> {{ __('Not assigned yet') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Notes & Photos -->
                                @if($order->notes || ($order->bookingMedia && $order->bookingMedia->isNotEmpty()))
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-2.5 px-3 rounded-3" style="background: #fbfdfc; border: 1px solid #e6f0ec;">
                                            @if($order->notes)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="small text-secondary fw-semibold" style="font-size: 0.78rem;"><i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i> {{ __('Notes') }}:</span>
                                                    <span class="small text-dark">{{ $order->notes }}</span>
                                                </div>
                                            @endif

                                            @if($order->bookingMedia && $order->bookingMedia->isNotEmpty())
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="small text-secondary fw-semibold" style="font-size: 0.78rem;"><i class="fa-solid fa-images text-rounder-orange me-1"></i> {{ __('Photos') }}:</span>
                                                    @foreach($order->bookingMedia as $media)
                                                        <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer" class="rounded-2 overflow-hidden border shadow-sm d-inline-block">
                                                            <img src="{{ $media->url }}" alt="Photo" width="36" height="36" style="object-fit: cover;">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="panel p-5 text-center text-secondary mb-4" style="border-radius: 14px;">
                        <i class="fa-solid fa-clipboard-list fs-1 mb-3 text-secondary opacity-50"></i>
                        <p class="mb-0 fs-6">{{ __('No service orders found') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- ==================================================== -->
            <!-- 3. ASSIGNMENT & UPDATE MODAL (FULL WIDTH & SPACIOUS) -->
            <!-- ==================================================== -->
            @foreach($orders as $order)
                @adminCan('admin.service-orders.update','PUT')
                <div class="modal fade" id="orderUpdateModal-{{ $order->id }}" tabindex="-1" aria-labelledby="orderUpdateModalLabel-{{ $order->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                            <!-- Modal Header -->
                            <div class="modal-header py-3 px-4" style="background: var(--petrol); color: #fff;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge" style="background: var(--orange); color: #fff; font-size: 0.85rem; padding: 0.35rem 0.7rem; border-radius: 8px;">
                                        #{{ $order->id }}
                                    </span>
                                    <h5 class="modal-title fw-bold mb-0 fs-6" id="orderUpdateModalLabel-{{ $order->id }}">
                                        {{ __('Provider assignment and commissions') }}
                                    </h5>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <!-- Modal Form -->
                            <form method="post" action="{{ route('admin.service-orders.update', $order->id) }}" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="modal-body p-4">
                                    <!-- Order Overview Header -->
                                    <div class="p-3 rounded-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: #f7faf9; border: 1px solid #dce8e4;">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-size: 1.1rem;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6">{{ $order->resident_name }}</div>
                                                <div class="small text-secondary"><i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $order->home_name }}</div>
                                            </div>
                                        </div>
                                        <div class="text-sm-end">
                                            <div class="fw-bold text-dark">{{ $order->service_name ?? $order->service_code }}</div>
                                            <div class="fw-bold fs-6" style="color: var(--petrol);">{{ number_format($order->total_amount, 2) }} <span class="small fw-normal text-secondary">{{ __('SAR') }}</span></div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Provider Selection (FULL WIDTH - NEVER TRUNCATED!) -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark small mb-1">
                                                <i class="fa-solid fa-truck-fast text-rounder-orange me-1"></i> {{ __('Choose provider') }}
                                            </label>
                                            <select class="form-select provider-select" name="provider_profile_id" style="border-radius: 10px; border-color: #ccdcd6; padding: 0.6rem 0.9rem;">
                                                <option value="">{{ __('Choose provider') }} ({{ __('Not assigned yet') }})</option>
                                                @foreach($providers as $provider)
                                                    <option value="{{ $provider->id }}" @selected($order->provider_profile_id === $provider->id)>
                                                        {{ $provider->company_name }} · {{ $provider->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text text-secondary small" style="font-size: 0.78rem;">
                                                {{ __('Select the approved service provider to assign this order to.') }}
                                            </div>
                                        </div>

                                        <!-- Order Status Selection -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark small mb-1">
                                                <i class="fa-solid fa-circle-dot text-rounder-orange me-1"></i> {{ __('Status') }} <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select order-status" name="booking_status_id" required style="border-radius: 10px; border-color: #ccdcd6; padding: 0.6rem 0.9rem;">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($order->status_code === $status->code)>
                                                        {{ $status->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Final Amount for Commission -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold text-dark small mb-1">
                                                <i class="fa-solid fa-coins text-rounder-orange me-1"></i> {{ __('Final amount') }}
                                            </label>
                                            <div class="input-group">
                                                <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control gross-amount" placeholder="{{ __('Final amount for commission') }}" style="border-radius: 10px 0 0 10px; border-color: #ccdcd6; padding: 0.6rem 0.9rem;">
                                                <span class="input-group-text bg-light text-secondary fw-bold" style="border-radius: 0 10px 10px 0; border-color: #ccdcd6;">{{ __('SAR') }}</span>
                                            </div>
                                        </div>

                                        <!-- Internal Note -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark small mb-1">
                                                <i class="fa-solid fa-pen-to-square text-rounder-orange me-1"></i> {{ __('Internal note') }}
                                            </label>
                                            <input name="note" maxlength="2000" class="form-control" placeholder="{{ __('Internal note') }}" style="border-radius: 10px; border-color: #ccdcd6; padding: 0.6rem 0.9rem;">
                                        </div>

                                        <!-- Add Photos -->
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark small mb-1" for="modal-img-{{ $order->id }}">
                                                <i class="fa-solid fa-camera text-rounder-orange me-1"></i> {{ __('Add photos') }}
                                            </label>
                                            <input id="modal-img-{{ $order->id }}" type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp,image/jpg" multiple style="border-radius: 10px; border-color: #ccdcd6; padding: 0.55rem 0.85rem;">
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer py-3 px-4 bg-light d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 9px; font-weight: 600; padding: 0.45rem 1.2rem;">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4 shadow-sm" style="border-radius: 9px; font-weight: 600; padding: 0.45rem 1.4rem;">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>{{ __('Save order update') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endadminCan
            @endforeach

            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Synchronize gross amount required status dynamically inside modals
document.querySelectorAll('.order-status').forEach((select) => {
    const form = select.closest('form');
    const amount = form ? form.querySelector('.gross-amount') : null;
    if (!amount) return;
    const sync = () => {
        const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
        amount.required = complete;
        amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
    };
    select.addEventListener('change', sync);
    sync();
});

// View switch handler (Table vs Cards) with localStorage memory
function switchOrdersView(mode) {
    const tablePane = document.getElementById('ordersTableView');
    const cardsPane = document.getElementById('ordersCardsView');
    const btnTable = document.getElementById('btnViewTable');
    const btnCards = document.getElementById('btnViewCards');

    if (!tablePane || !cardsPane || !btnTable || !btnCards) return;

    if (mode === 'cards') {
        tablePane.classList.add('d-none');
        cardsPane.classList.remove('d-none');
        btnCards.classList.add('active');
        btnTable.classList.remove('active');
        try { localStorage.setItem('rounder_orders_view', 'cards'); } catch(e){}
    } else {
        cardsPane.classList.add('d-none');
        tablePane.classList.remove('d-none');
        btnTable.classList.add('active');
        btnCards.classList.remove('active');
        try { localStorage.setItem('rounder_orders_view', 'table'); } catch(e){}
    }
}

// Restore user view preference (defaults to table)
document.addEventListener('DOMContentLoaded', function() {
    try {
        const savedView = localStorage.getItem('rounder_orders_view');
        if (savedView === 'cards') {
            switchOrdersView('cards');
        }
    } catch(e) {}
});
</script>
@endpush
