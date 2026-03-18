@extends('layouts.admin')
@section('title', __('pos.bill_title', ['number' => $bill->bill_number]))
@section('page-title', __('pos.bill_title', ['number' => $bill->bill_number]))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('bills.index') }}">{{ __('bills.title') }}</a></li>
<li class="breadcrumb-item active">{{ $bill->bill_number }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>{{ $bill->bill_number }}</h6>
                    @php
                        $statusColor = match($bill->payment_status) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            default => 'danger',
                        };
                    @endphp
                    <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3 py-2 rounded-pill fw-semibold">
                        {{ ucfirst($bill->payment_status) }}
                    </span>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">{{ __('pos.total_label') }}</div>
                            <div class="fs-3 fw-bold text-dark">{{ number_format($bill->total, 0) }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">{{ __('pos.status') }}</div>
                            <div class="fw-semibold text-{{ $statusColor }}">{{ ucfirst($bill->payment_status) }}</div>
                        </div>
                        @if($bill->order?->diningTable)
                        <div class="col-6 col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">{{ __('pos.table') }}</div>
                            <div class="fw-semibold"><i class="fas fa-chair me-1 text-muted"></i>{{ $bill->order->diningTable->table_name }}</div>
                        </div>
                        @endif
                    </div>

                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <div class="text-muted small text-uppercase fw-semibold mb-2">{{ __('pos.verify_url') }}</div>
                        <a href="{{ $verifyUrl }}" class="small text-break text-primary">{{ $verifyUrl }}</a>
                    </div>

                    <div class="text-center py-3">
                        <div class="d-inline-block border rounded-3 p-2 bg-white shadow-sm">{!! $qrSvg !!}</div>
                    </div>

                    <div class="text-center mt-2">
                        <a href="{{ route('bills.print', $bill) }}" target="_blank" class="btn btn-dt-pro-outline btn-sm rounded-pill px-4">
                            <i class="fas fa-print me-1"></i>{{ __('pos.print_receipt') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>{{ __('pos.payments') }}</h6>
                </div>
                <div class="card-body p-0">
                    @forelse ($bill->payments as $p)
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ number_format($p->amount, 0) }}</div>
                            <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</div>
                        </div>
                        <span class="text-muted small">{{ $p->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">{{ __('pos.no_payments') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
