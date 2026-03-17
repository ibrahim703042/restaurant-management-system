@extends('layouts.admin')

@section('main-section')
<div class="container-fluid px-2 px-md-4">
    <h1 class="mt-3"><i class="fas fa-cash-register me-2"></i>Point of Sale</h1>
    <p class="text-muted">Tap items to add to cart, then checkout.</p>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('pos.checkout') }}" method="post" id="pos-form">
        @csrf
        <div class="row g-3">
            <div class="col-lg-7">
                @foreach ($categories as $cat)
                    @if ($cat->products->isEmpty()) @continue @endif
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-dark text-white py-2">{{ $cat->name }}</div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach ($cat->products as $p)
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100 h-100 py-3 pos-add"
                                            data-id="{{ $p->id }}" data-name="{{ $p->product_name }}" data-price="{{ $p->price }}">
                                            <strong>{{ $p->product_name }}</strong><br>
                                            <span class="text-success">{{ number_format($p->price, 0) }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-lg-5">
                <div class="card shadow sticky-top" style="top:1rem;">
                    <div class="card-header bg-primary text-white">Current order</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3" id="cart-lines"></ul>
                        <div class="d-flex justify-content-between fs-5 mb-3">
                            <span>Total</span>
                            <strong id="cart-total">0</strong>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Client (required for debt / partial pay)</label>
                            <select name="client_id" class="form-select">
                                <option value="">— Walk-in —</option>
                                @foreach ($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone) ({{ $c->phone }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Payment</label>
                            <select name="payment_method" class="form-select" id="pay-method">
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile money</option>
                                <option value="bank">Bank</option>
                                <option value="debt">Full debt</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount paid</label>
                            <input type="number" step="0.01" min="0" name="amount_paid" class="form-control" id="amount-paid" value="0" placeholder="0 = full debt">
                        </div>
                        <div id="items-json"></div>
                        <button type="submit" class="btn btn-success btn-lg w-100" id="btn-checkout" disabled>
                            <i class="fas fa-check me-1"></i> Complete sale
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
(function(){
    const cart = {};
    function render() {
        const ul = document.getElementById('cart-lines');
        const holder = document.getElementById('items-json');
        ul.innerHTML = '';
        holder.innerHTML = '';
        let total = 0;
        let i = 0;
        for (const id in cart) {
            const row = cart[id];
            const line = row.qty * row.price;
            total += line;
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `<span>${row.name} × ${row.qty}</span><span>${line.toFixed(0)} <button type="button" class="btn btn-sm btn-outline-danger ms-2" data-rid="${id}">−</button></span>`;
            ul.appendChild(li);
            holder.innerHTML += `<input type="hidden" name="items[${i}][product_id]" value="${id}"><input type="hidden" name="items[${i}][quantity]" value="${row.qty}">`;
            i++;
        }
        document.getElementById('cart-total').textContent = total.toFixed(0);
        document.getElementById('btn-checkout').disabled = i === 0;
    }
    document.querySelectorAll('.pos-add').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (!cart[id]) cart[id] = { name: btn.dataset.name, price: parseFloat(btn.dataset.price), qty: 0 };
            cart[id].qty++;
            render();
        });
    });
    document.getElementById('cart-lines').addEventListener('click', e => {
        if (e.target.dataset.rid) {
            const id = e.target.dataset.rid;
            cart[id].qty--;
            if (cart[id].qty <= 0) delete cart[id];
            render();
        }
    });
    document.getElementById('pay-method').addEventListener('change', function() {
        const t = document.getElementById('cart-total').textContent;
        document.getElementById('amount-paid').value = this.value === 'debt' ? '0' : t;
    });
})();
</script>
@endsection
