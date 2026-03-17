@extends('layouts.admin')

@section('title', 'POS')
@section('page-title', 'Point of Sale')

@push('styles')
<link href="{{ asset('css/pos.css') }}?v=2" rel="stylesheet">
@endpush

@section('main-section')
<div class="pos-terminal container-fluid px-2 px-md-3">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('pos.checkout') }}" method="post" id="pos-form">
        @csrf
        <input type="hidden" name="client_id" id="f_client_id" value="">
        <input type="hidden" name="payment_method" id="f_payment_method" value="cash">
        <input type="hidden" name="amount_paid" id="f_amount_paid" value="0">

        <div class="pos-category-tabs nav nav-pills" role="tablist">
            @php $ci = 0; @endphp
            @foreach ($categories as $cat)
                @if ($cat->products->isEmpty()) @continue @endif
                <button type="button" class="nav-link {{ $ci === 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#cat-{{ $cat->id }}">{{ $cat->name }}</button>
                @php $ci++; @endphp
            @endforeach
        </div>

        <div class="tab-content">
            @php $ci = 0; @endphp
            @foreach ($categories as $cat)
                @if ($cat->products->isEmpty()) @continue @endif
                <div class="tab-pane fade {{ $ci === 0 ? 'show active' : '' }}" id="cat-{{ $cat->id }}">
                    <div class="row g-2 g-md-3">
                        @foreach ($cat->products as $p)
                            <div class="col-6 col-sm-4 col-md-4 col-lg-3 col-xl-2">
                                <button type="button" class="btn btn-outline-primary w-100 pos-add pos-product-btn"
                                    data-id="{{ $p->id }}" data-name="{{ e($p->product_name) }}" data-price="{{ $p->price }}">
                                    <strong class="d-block text-truncate">{{ $p->product_name }}</strong>
                                    <span class="text-success fw-bold">{{ number_format($p->price, 0) }}</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @php $ci++; @endphp
            @endforeach
        </div>

        <div class="row g-3 mt-1 d-none d-lg-flex">
            <div class="col-lg-5 ms-auto">
                <div class="card shadow sticky-top" style="top:4.5rem;">
                    @include('pos._cart_panel', ['idSuffix' => ''])
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-bottom pos-offcanvas-cart d-lg-none" tabindex="-1" id="posCartCanvas" style="height: 88vh; max-height: 100dvh;">
            <div class="offcanvas-header border-bottom py-2">
                <h5 class="offcanvas-title mb-0"><i class="fas fa-shopping-cart me-2"></i>Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body pt-2">
                @include('pos._cart_panel', ['idSuffix' => '-m'])
            </div>
        </div>

        <div id="items-json"></div>
    </form>

    <div class="pos-mobile-bar d-lg-none">
        <div>
            <div class="small text-white-50">Total</div>
            <div class="pos-total" id="cart-total-bar">0</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">
            <span class="badge bg-light text-dark" id="cart-count-bar">0</span>
            <button type="button" class="btn btn-warning fw-bold px-3" data-bs-toggle="offcanvas" data-bs-target="#posCartCanvas">Cart &amp; pay</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const cart = {};
    const holder = document.getElementById('items-json');
    const totalBar = document.getElementById('cart-total-bar');
    const countBar = document.getElementById('cart-count-bar');
    const fClient = document.getElementById('f_client_id');
    const fPay = document.getElementById('f_payment_method');
    const fPaid = document.getElementById('f_amount_paid');

    function bindCheckoutSync(suffix) {
        const c = document.getElementById('sel-client' + suffix);
        const p = document.getElementById('sel-pay' + suffix);
        const a = document.getElementById('inp-paid' + suffix);
        const t = document.getElementById('cart-total' + suffix);
        if (c) c.addEventListener('change', () => { fClient.value = c.value; if (suffix === '') syncOtherClient(); else syncOtherClient(true); });
        if (p) p.addEventListener('change', () => { fPay.value = p.value; syncPay(); });
        if (a) a.addEventListener('input', () => { fPaid.value = a.value; });
        return { c, p, a, t };
    }
    const d = bindCheckoutSync('');
    const m = bindCheckoutSync('-m');
    function syncOtherClient(fromMobile) {
        const src = fromMobile ? m.c : d.c;
        const dst = fromMobile ? d.c : m.c;
        if (src && dst) dst.value = src.value;
    }
    function syncPay() {
        const v = fPay.value;
        if (d.p) d.p.value = v;
        if (m.p) m.p.value = v;
        const tot = (d.t && d.t.textContent) || totalBar.textContent;
        const paid = (v === 'debt') ? '0' : tot;
        fPaid.value = paid;
        if (d.a) d.a.value = paid;
        if (m.a) m.a.value = paid;
    }
    if (d.c) fClient.value = d.c.value;
    if (d.p) fPay.value = d.p.value;
    if (d.a) fPaid.value = d.a.value;

    function render() {
        ['', '-m'].forEach(suf => {
            const list = document.getElementById('cart-lines' + suf);
            if (!list) return;
            list.innerHTML = '';
        });
        holder.innerHTML = '';
        let total = 0, count = 0, i = 0;
        for (const id in cart) {
            const row = cart[id];
            const line = row.qty * row.price;
            total += line;
            count += row.qty;
            const liHtml = `<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
                <span class="me-2">${escapeHtml(row.name)}</span>
                <span class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-outline-secondary pos-qty-btn" data-chg="${id}" data-d="-1">−</button>
                    <span class="px-2 fw-bold">${row.qty}</span>
                    <button type="button" class="btn btn-outline-secondary pos-qty-btn" data-chg="${id}" data-d="1">+</button>
                    <span class="ms-1 text-success">${line.toFixed(0)}</span>
                </span></li>`;
            document.querySelectorAll('#cart-lines, #cart-lines-m').forEach(el => { if (el) el.insertAdjacentHTML('beforeend', liHtml); });
            holder.innerHTML += `<input type="hidden" name="items[${i}][product_id]" value="${id}"><input type="hidden" name="items[${i}][quantity]" value="${row.qty}">`;
            i++;
        }
        const t = total.toFixed(0);
        if (d.t) d.t.textContent = t;
        if (m.t) m.t.textContent = t;
        if (totalBar) totalBar.textContent = t;
        if (countBar) countBar.textContent = count;
        const dis = i === 0;
        document.querySelectorAll('#btn-checkout, #btn-checkout-m').forEach(b => { if (b) b.disabled = dis; });
        syncPay();
    }
    function escapeHtml(s) {
        const el = document.createElement('div');
        el.textContent = s;
        return el.innerHTML;
    }
    document.querySelectorAll('.pos-add').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (!cart[id]) cart[id] = { name: btn.dataset.name, price: parseFloat(btn.dataset.price), qty: 0 };
            cart[id].qty++;
            render();
        });
    });
    document.addEventListener('click', e => {
        const chg = e.target.closest('[data-chg]');
        if (!chg) return;
        const id = chg.dataset.chg;
        const delta = parseInt(chg.dataset.d, 10);
        if (!cart[id]) return;
        cart[id].qty += delta;
        if (cart[id].qty <= 0) delete cart[id];
        render();
    });
    document.getElementById('pos-form').addEventListener('submit', () => {
        if (d.c) fClient.value = d.c.value;
        if (m.c && window.innerWidth < 992) fClient.value = m.c.value;
        if (window.innerWidth < 992) {
            if (m.p) fPay.value = m.p.value;
            if (m.a) fPaid.value = m.a.value;
        } else {
            if (d.p) fPay.value = d.p.value;
            if (d.a) fPaid.value = d.a.value;
        }
    });
    render();
})();
</script>
@endpush
@endsection
