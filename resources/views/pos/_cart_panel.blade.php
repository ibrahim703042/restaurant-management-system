@php
    $s = $idSuffix;
@endphp
<div class="card-body border-bottom bg-light py-2">
    <strong class="small text-muted">LINE ITEMS</strong>
    <ul class="list-group list-group-flush mt-2" id="cart-lines{{ $s }}"></ul>
    <div class="d-flex justify-content-between fs-5 mt-3 mb-2">
        <span>Total</span>
        <strong class="text-success" id="cart-total{{ $s }}">0</strong>
    </div>
</div>
<div class="card-body">
    <div class="mb-2">
        <label class="form-label small mb-1">Client</label>
        <select class="form-select" id="sel-client{{ $s }}">
            <option value="">— Walk-in —</option>
            @foreach ($clients as $c)
            <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone) ({{ $c->phone }}) @endif</option>
            @endforeach
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label small mb-1">Payment</label>
        <select class="form-select" id="sel-pay{{ $s }}">
            <option value="cash">Cash</option>
            <option value="mobile_money">Mobile money</option>
            <option value="bank">Bank</option>
            <option value="debt">Full debt</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label small mb-1">Amount paid</label>
        <input type="number" step="0.01" min="0" class="form-control" id="inp-paid{{ $s }}" value="0">
    </div>
    <button type="submit" class="btn btn-success btn-lg w-100 py-3" id="btn-checkout{{ $s }}">
        <i class="fas fa-check me-1"></i> Complete sale
    </button>
</div>
