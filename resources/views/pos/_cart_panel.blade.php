@php
    $s = $idSuffix;
@endphp
<div class="card-body border-bottom bg-light py-2">
    <strong class="small text-muted">{{ strtoupper(__('pos.line_items')) }}</strong>
    <ul class="list-group list-group-flush mt-2" id="cart-lines{{ $s }}"></ul>
    <div class="d-flex justify-content-between fs-5 mt-3 mb-2">
        <span>{{ __('pos.total') }}</span>
        <strong class="text-success" id="cart-total{{ $s }}">0</strong>
    </div>
</div>
<div class="card-body">
    <div class="mb-2">
        <label class="form-label small mb-1"><i class="fas fa-chair me-1 text-muted"></i>{{ __('pos.table') ?? 'Table' }}</label>
        <select class="form-select" id="sel-table{{ $s }}">
            <option value="">{{ __('pos.no_table') ?? '— No table —' }}</option>
            @foreach ($tables as $t)
            <option value="{{ $t->id }}">{{ $t->table_name }}@if($t->zone) · {{ $t->zone->name }}@endif</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label small mb-1">{{ __('pos.client') }}</label>
        <select class="form-select" id="sel-client{{ $s }}">
            <option value="">{{ __('pos.walkin') }}</option>
            @foreach ($clients as $c)
            <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone) ({{ $c->phone }}) @endif</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label small mb-1">{{ __('pos.payment') }}</label>
        <select class="form-select" id="sel-pay{{ $s }}">
            <option value="cash">{{ __('pos.cash') }}</option>
            <option value="mobile_money">{{ __('pos.mobile_money') }}</option>
            <option value="bank">{{ __('pos.bank') }}</option>
            <option value="debt">{{ __('pos.full_debt') }}</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label small mb-1">{{ __('pos.amount_paid') }}</label>
        <input type="number" step="0.01" min="0" class="form-control" id="inp-paid{{ $s }}" value="0">
    </div>
    <button type="submit" class="btn btn-success btn-lg w-100 py-3" id="btn-checkout{{ $s }}">
        <i class="fas fa-check me-1"></i> {{ __('pos.complete_sale') }}
    </button>
</div>
