@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4" style="max-width:720px">
    <h1 class="mt-4">Verify bill</h1>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <div class="card mb-3"><div class="card-body">
        <p><strong>{{ $bill->bill_number }}</strong> — Total {{ number_format($bill->total, 0) }} — {{ $bill->payment_status }}</p>
        <ul class="mb-0">
            @foreach ($bill->order->items as $line)
            <li>{{ $line->product->product_name }} × {{ $line->quantity }} = {{ number_format($line->line_total, 0) }}</li>
            @endforeach
        </ul>
    </div></div>
    <form method="post" action="{{ route('bills.verify.confirm', $bill->qr_token) }}" class="card card-body">
        @csrf
        <h5>Record payment</h5>
        @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="mb-2">
            <label class="form-label">Existing client</label>
            <select name="client_id" class="form-select">
                <option value="">— New below —</option>
                @foreach ($clients as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2"><label class="form-label">Or new client name</label><input name="new_client_name" class="form-control" placeholder="Name"></div>
        <div class="mb-2"><label class="form-label">Phone</label><input name="new_client_phone" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
        <div class="mb-3">
            <label class="form-label">Method</label>
            <select name="payment_method" class="form-select"><option value="cash">Cash</option><option value="mobile_money">Mobile money</option><option value="bank">Bank</option></select>
        </div>
        <button class="btn btn-success">Confirm payment</button>
    </form>
</div>
@endsection
