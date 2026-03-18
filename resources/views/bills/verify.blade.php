@extends('layouts.admin')
@section('title', __('pos.verify_bill'))
@section('page-title', __('pos.verify_bill'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('bills.index') }}">{{ __('bills.title') }}</a></li>
<li class="breadcrumb-item active">{{ __('pos.verify_bill') }}</li>
@endsection

@section('main-section')
<div class="container-fluid px-lg-4 pb-4" style="max-width:820px">
    @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body text-center py-4 px-4">
            <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                <span class="sidebar-brand-mark" style="font-size:1.4rem">Rg</span>
                <span class="fw-bold text-dark">Bar-restaurant</span>
            </div>
            <div class="d-inline-block border rounded-3 p-2 bg-white shadow-sm mb-3" style="max-width:200px">{!! $qrSvg !!}</div>
            <p class="small text-muted text-break mb-2">{{ $verifyUrl }}</p>
            @php
                $statusColor = match($bill->payment_status) {
                    'paid' => 'success',
                    'partial' => 'warning',
                    default => 'danger',
                };
            @endphp
            <div class="d-flex align-items-center justify-content-center flex-wrap gap-3 mt-3">
                <span class="fw-bold fs-5">{{ $bill->bill_number }}</span>
                <span class="text-muted">—</span>
                <span class="fw-semibold">{{ __('pos.total_label') }}: {{ number_format($bill->total, 0) }}</span>
                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} px-3 py-2 rounded-pill fw-semibold">
                    {{ ucfirst($bill->payment_status) }}
                </span>
            </div>
            @if ($bill->order && $bill->order->items->isNotEmpty())
            <div class="table-responsive mt-3">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-start ps-3" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.product') }}</th>
                            <th class="text-center" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">Qty</th>
                            <th class="text-end pe-3" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('pos.total_label') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bill->order->items as $line)
                        <tr>
                            <td class="ps-3">{{ $line->product->product_name }}</td>
                            <td class="text-center font-monospace">{{ $line->quantity }}</td>
                            <td class="text-end pe-3 fw-semibold">{{ number_format($line->line_total, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-4 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-money-check-alt me-2 text-success"></i>{{ __('pos.record_payment') }}</h6>
        </div>
        <div class="card-body px-4 py-3">
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            <form method="post" action="{{ route('bills.verify.confirm', $bill->qr_token) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="vClientId">{{ __('pos.existing_client') }}</label>
                        <select name="client_id" id="vClientId" class="form-select">
                            <option value="">{{ __('pos.new_below') }}</option>
                            @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="vNewName">{{ __('pos.new_client_name') }}</label>
                        <input name="new_client_name" id="vNewName" class="form-control" placeholder="{{ __('common.name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="vPhone">{{ __('pos.phone') }}</label>
                        <input name="new_client_phone" id="vPhone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="vAmount">{{ __('pos.amount') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="vAmount" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="vMethod">{{ __('pos.method') }}</label>
                        <select name="payment_method" id="vMethod" class="form-select">
                            <option value="cash">{{ __('pos.cash') }}</option>
                            <option value="mobile_money">{{ __('pos.mobile_money') }}</option>
                            <option value="bank">{{ __('pos.bank') }}</option>
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">
                        <i class="fas fa-check me-1"></i>{{ __('pos.confirm_payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
