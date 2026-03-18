@extends('layouts.admin')

@section('title', __('pos.title'))
@section('page-title', __('pos.title'))

@push('styles')
<link href="{{ asset('css/pos.css') }}?v=4" rel="stylesheet">
@endpush

@section('main-section')
<div class="pos-terminal container-fluid px-2 px-md-3">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="post" action="{{ route('pos.set-store') }}" class="mb-3 d-flex flex-wrap align-items-center gap-2 bg-light rounded p-2">
        @csrf
        <label class="form-label mb-0 fw-bold"><i class="fas fa-store me-1"></i>{{ __('pos.store_label') }}</label>
        <select name="store_id" class="form-select form-select-sm" style="max-width:280px" onchange="this.form.submit()">
            @foreach ($stores as $s)
            <option value="{{ $s->id }}" @selected((int)$posStoreId === (int)$s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
    </form>

    <form action="{{ route('pos.checkout') }}" method="post" id="pos-form">
        @csrf
        <input type="hidden" name="store_id" value="{{ $posStoreId }}">
        <input type="hidden" name="table_id" id="f_table_id" value="">
        <input type="hidden" name="client_id" id="f_client_id" value="">
        <input type="hidden" name="payment_method" id="f_payment_method" value="cash">
        <input type="hidden" name="amount_paid" id="f_amount_paid" value="0">

        @php
            $posCategories = $categories->filter(fn ($c) => $c->products->isNotEmpty())->values();
            $posCatCount = $posCategories->count();
            $manyPosCategories = $posCatCount > 14;
        @endphp

        <div class="row g-3 pos-layout align-items-start">
            {{-- Products + categories: main area --}}
            <div class="col-12 col-lg-8 col-xl-8 pos-products-column">
                <div class="pos-category-wrap mb-2 {{ $manyPosCategories ? 'pos-category-wrap--many' : '' }}">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="pos-category-label text-muted small text-uppercase fw-semibold d-none d-md-inline mb-0">{{ __('pos.categories') }}</span>
                        @if ($manyPosCategories)
                            <button type="button" class="btn btn-sm btn-primary w-100 w-md-auto ms-md-auto flex-shrink-0" data-bs-toggle="modal" data-bs-target="#posCategoriesModal">
                                <i class="fas fa-th-large me-1"></i>{{ __('pos.all_categories') }}
                                <span class="badge bg-light text-primary ms-1">{{ $posCatCount }}</span>
                            </button>
                        @endif
                    </div>
                    <div class="pos-category-tabs-inner">
                        <div class="pos-category-tabs nav nav-pills" role="tablist" id="posCategoryTabs">
                            @foreach ($posCategories as $ci => $cat)
                            <button type="button" class="nav-link d-flex align-items-center gap-2 {{ $ci === 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#cat-{{ $cat->id }}">
                                <span class="pos-cat-thumb"><img src="{{ \App\Support\MediaUrl::forCategory($cat) }}" alt="" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('pos-cat-thumb--fallback')"></span>
                                <span class="text-truncate" style="max-width: 10rem">{{ $cat->name }}</span>
                            </button>
                            @endforeach
                        </div>
                        @if ($manyPosCategories)
                            <div class="pos-category-fade pos-category-fade--end" aria-hidden="true"></div>
                        @endif
                    </div>
                    @if ($manyPosCategories)
                        <p class="pos-category-scroll-hint small text-muted mb-0 mt-1">
                            <i class="fas fa-hand-pointer me-1"></i>
                            <span class="d-none d-md-inline">{{ __('pos.scroll_hint_desktop') }} <strong>{{ __('pos.all_categories') }}</strong>.</span>
                            <span class="d-md-none">{{ __('pos.scroll_hint_mobile') }} <strong>{{ __('pos.all_categories') }}</strong>.</span>
                        </p>
                    @endif
                </div>

                <div class="tab-content pos-tab-content">
                    @foreach ($posCategories as $ci => $cat)
                        <div class="tab-pane fade {{ $ci === 0 ? 'show active' : '' }}" id="cat-{{ $cat->id }}">
                            <p class="text-muted small mb-2 d-none d-md-block">{{ __('pos.tap_to_add') }} · {{ $cat->products->count() }} {{ __('pos.items') }}</p>
                            <div class="pos-product-grid" role="list">
                                @foreach ($cat->products as $p)
                                    <button type="button" role="listitem"
                                        class="pos-add pos-product-tile"
                                        data-id="{{ $p->id }}" data-name="{{ e($p->product_name) }}" data-price="{{ $p->price }}">
                                        <span class="pos-product-tile__media">
                                            <img src="{{ \App\Support\MediaUrl::forProduct($p) }}" alt="" loading="lazy" onerror="this.style.visibility='hidden'; this.parentElement.classList.add('pos-product-tile__media--empty')">
                                            <span class="pos-product-tile__ph" aria-hidden="true"><i class="fas fa-image"></i></span>
                                        </span>
                                        <span class="pos-product-tile__name">{{ $p->product_name }}</span>
                                        <span class="pos-product-tile__price">{{ number_format($p->price, 0) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Cart: fixed column on desktop --}}
            <div class="col-lg-4 col-xl-4 d-none d-lg-block pos-cart-column">
                <div class="card shadow-sm border-0 pos-cart-sticky">
                    @include('pos._cart_panel', ['idSuffix' => ''])
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-bottom pos-offcanvas-cart d-lg-none" tabindex="-1" id="posCartCanvas" style="height: 88vh; max-height: 100dvh;">
            <div class="offcanvas-header border-bottom py-2">
                <h5 class="offcanvas-title mb-0"><i class="fas fa-shopping-cart me-2"></i>{{ __('pos.order') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body pt-2">
                @include('pos._cart_panel', ['idSuffix' => '-m'])
            </div>
        </div>

        <div id="items-json"></div>
    </form>

    @if ($manyPosCategories)
    <div class="modal fade" id="posCategoriesModal" tabindex="-1" aria-labelledby="posCategoriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="posCategoriesModalLabel"><i class="fas fa-layer-group me-2"></i>{{ __('pos.all_categories') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">{{ __('pos.tap_category') }}</p>
                    <div class="row g-2">
                        @foreach ($posCategories as $cat)
                        <div class="col-6 col-sm-4 col-md-3">
                            <button type="button" class="btn btn-outline-primary w-100 h-100 py-2 pos-cat-modal-pick text-start d-flex align-items-center gap-2" data-pos-tab-target="#cat-{{ $cat->id }}">
                                <span class="pos-cat-thumb flex-shrink-0"><img src="{{ \App\Support\MediaUrl::forCategory($cat) }}" alt="" loading="lazy" onerror="this.style.display='none'; this.parentElement.classList.add('pos-cat-thumb--fallback')"></span>
                                <span class="text-truncate">{{ $cat->name }}</span>
                                <span class="badge bg-secondary ms-auto flex-shrink-0">{{ $cat->products->count() }}</span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="pos-mobile-bar d-lg-none">
        <div>
            <div class="small text-white-50">{{ __('pos.total') }}</div>
            <div class="pos-total" id="cart-total-bar">0</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">
            <span class="badge bg-light text-dark" id="cart-count-bar">0</span>
            <button type="button" class="btn btn-warning fw-bold px-3" data-bs-toggle="offcanvas" data-bs-target="#posCartCanvas">{{ __('pos.cart_pay') }}</button>
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
    const fTable = document.getElementById('f_table_id');
    const fClient = document.getElementById('f_client_id');
    const fPay = document.getElementById('f_payment_method');
    const fPaid = document.getElementById('f_amount_paid');

    function bindCheckoutSync(suffix) {
        const tb = document.getElementById('sel-table' + suffix);
        const c = document.getElementById('sel-client' + suffix);
        const p = document.getElementById('sel-pay' + suffix);
        const a = document.getElementById('inp-paid' + suffix);
        const t = document.getElementById('cart-total' + suffix);
        if (tb) tb.addEventListener('change', () => { fTable.value = tb.value; syncOtherTable(suffix === '-m'); });
        if (c) c.addEventListener('change', () => { fClient.value = c.value; if (suffix === '') syncOtherClient(); else syncOtherClient(true); });
        if (p) p.addEventListener('change', () => { fPay.value = p.value; syncPay(); });
        if (a) a.addEventListener('input', () => { fPaid.value = a.value; });
        return { tb, c, p, a, t };
    }
    const d = bindCheckoutSync('');
    const m = bindCheckoutSync('-m');
    function syncOtherTable(fromMobile) {
        const src = fromMobile ? m.tb : d.tb;
        const dst = fromMobile ? d.tb : m.tb;
        if (src && dst) dst.value = src.value;
    }
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
        if (window.innerWidth < 992) {
            if (m.tb) fTable.value = m.tb.value;
            if (m.c) fClient.value = m.c.value;
            if (m.p) fPay.value = m.p.value;
            if (m.a) fPaid.value = m.a.value;
        } else {
            if (d.tb) fTable.value = d.tb.value;
            if (d.c) fClient.value = d.c.value;
            if (d.p) fPay.value = d.p.value;
            if (d.a) fPaid.value = d.a.value;
        }
    });
    render();

    @if ($manyPosCategories)
    (function () {
        var modal = document.getElementById('posCategoriesModal');
        document.querySelectorAll('.pos-cat-modal-pick').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-pos-tab-target');
                var pill = document.querySelector('#posCategoryTabs button[data-bs-target="' + target + '"]');
                if (pill && typeof bootstrap !== 'undefined') {
                    bootstrap.Tab.getOrCreateInstance(pill).show();
                    pill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
                if (modal && typeof bootstrap !== 'undefined') {
                    var m = bootstrap.Modal.getInstance(modal);
                    if (m) m.hide();
                }
            });
        });
        var inner = document.querySelector('.pos-category-tabs-inner');
        var fade = document.querySelector('.pos-category-fade--end');
        var tabs = document.getElementById('posCategoryTabs');
        function updateFade() {
            if (!inner || !fade || !tabs) return;
            var el = tabs;
            var over = el.scrollWidth > el.clientWidth + 2 || el.scrollHeight > el.clientHeight + 2;
            var atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4 && el.scrollTop + el.clientHeight >= el.scrollHeight - 4;
            fade.classList.toggle('is-hidden', !over || atEnd);
        }
        if (tabs) {
            tabs.addEventListener('scroll', updateFade);
            window.addEventListener('resize', updateFade);
            updateFade();
        }
    })();
    @endif
})();
</script>
@endpush
@endsection
