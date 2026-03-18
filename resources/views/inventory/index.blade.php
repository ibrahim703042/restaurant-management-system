@extends('layouts.admin')
@section('title', __('inventory.title'))
@section('page-title', __('inventory.title'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('inventory.title') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4 pb-4">
    @if (session('status'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <x-admin.page-actions :title="__('inventory.title')">
        <x-slot:actions>
            <button type="button" class="btn btn-dt-pro-outline btn-sm" disabled title="{{ __('inventory.coming_soon') }}">
                <i class="fas fa-upload me-1"></i>{{ __('inventory.import') }}
            </button>
            <button type="button" class="btn btn-dt-pro-outline btn-sm" disabled title="{{ __('inventory.coming_soon') }}">
                <i class="fas fa-download me-1"></i>{{ __('inventory.export') }}
            </button>
            @can('ops.menu.products.manage')
            <a href="{{ route('products.index') }}" class="btn btn-dt-pro-primary btn-sm">
                <i class="fas fa-plus me-1"></i>{{ __('inventory.add_product') }}
            </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-actions>

    @if ($storeId)
    <x-admin.inventory-summary
        :totalValue="$summary['total_value']"
        :totalProducts="$summary['total_products']"
        :inStock="$summary['in_stock']"
        :lowStock="$summary['low_stock']"
        :outStock="$summary['out_stock']"
    />
    @endif

    <x-admin.data-table-pro>
        <x-slot:toolbar>
            <form method="get" class="dt-pro-toolbar-split">
                <div class="dt-pro-search-wrap">
                    <i class="fas fa-search dt-pro-search-icon"></i>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('inventory.search_placeholder') }}">
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select name="store_id" class="form-select form-select-sm dt-pro-filter-select" onchange="this.form.submit()">
                        @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected((string) $storeId === (string) $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select form-select-sm dt-pro-filter-select">
                        <option value="">{{ __('inventory.filter_all') }}</option>
                        <option value="in" @selected(request('status') === 'in')>{{ __('inventory.status_in') }}</option>
                        <option value="low" @selected(request('status') === 'low')>{{ __('inventory.status_low') }}</option>
                        <option value="out" @selected(request('status') === 'out')>{{ __('inventory.status_out') }}</option>
                    </select>
                    <select name="per_page" class="form-select form-select-sm dt-pro-filter-select" style="width:auto">
                        @foreach ([10, 25, 50] as $n)
                        <option value="{{ $n }}" @selected((int) request('per_page', 10) === $n)>{{ $n }} / {{ __('inventory.page') }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-dt-pro-outline btn-sm px-3">
                        <i class="fas fa-sliders-h me-1"></i>{{ __('inventory.filter_apply') }}
                    </button>
                </div>
            </form>
        </x-slot:toolbar>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th class="ps-4" style="width:42px;">
                        <input type="checkbox" class="form-check-input" disabled aria-label="—">
                    </th>
                    <th>{{ __('inventory.product') }}</th>
                    <th>{{ __('inventory.category') }}</th>
                    <th>{{ __('inventory.sku') }}</th>
                    <th class="text-end">{{ __('inventory.incoming') }}</th>
                    <th class="text-end">{{ __('inventory.qty') }}</th>
                    <th>{{ __('inventory.unit') }}</th>
                    <th>{{ __('inventory.reorder') }}</th>
                    <th>{{ __('inventory.col_status') }}</th>
                    <th class="text-end">{{ __('inventory.price') }}</th>
                    <th class="text-end pe-4" style="width:72px">{{ __('inventory.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @unless ($storeId)
                <tr>
                    <td colspan="11" class="text-center text-muted py-5">{{ __('inventory.no_store') }}</td>
                </tr>
                @else
                    @forelse ($stocks as $row)
                    @php $p = $row->product; @endphp
                    <tr>
                        <td class="ps-4"><input type="checkbox" class="form-check-input" disabled></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ \App\Support\MediaUrl::forProduct($p) }}" alt="" class="dt-pro-thumb" width="44" height="44">
                                <span class="fw-semibold text-dark">{{ $p->product_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted small">{{ $p->category->name ?? '—' }}</span></td>
                        <td><code class="small bg-light px-2 py-1 rounded">#{{ $p->id }}</code></td>
                        <td class="text-end text-muted">—</td>
                        <td class="text-end font-monospace fw-medium">{{ number_format($row->quantity, 2) }}</td>
                        <td>{{ $row->unit->code ?? '—' }}</td>
                        <td class="text-end font-monospace small">{{ number_format($row->reorder_level, 2) }}</td>
                        <td>
                            <x-admin.stock-status-badge :quantity="$row->quantity" :reorderLevel="$row->reorder_level" />
                        </td>
                        <td class="text-end fw-semibold">{{ number_format((float) ($p->price ?? 0), 0) }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button class="dt-pro-actions-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2">
                                    @can('inventory.stock.adjust')
                                    <li>
                                        <button type="button" class="dropdown-item rounded-2 inv-open-adjust"
                                            data-product-id="{{ $row->product_id }}"
                                            data-name="{{ e($p->product_name ?? '') }}"
                                            data-qty="{{ $row->quantity }}"
                                            data-unit-id="{{ $row->unit_id }}"
                                            data-reorder="{{ $row->reorder_level }}">
                                            <i class="fas fa-edit me-2 text-primary"></i>{{ __('inventory.adjust_stock') }}
                                        </button>
                                    </li>
                                    @endcan
                                    @can('ops.menu.products.manage')
                                    <li><a class="dropdown-item rounded-2" href="{{ route('products.index') }}"><i class="fas fa-box me-2 text-secondary"></i>{{ __('inventory.view_products') }}</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">{{ __('inventory.empty_rows') }}</td>
                    </tr>
                    @endforelse
                @endunless
            </tbody>
        </table>

        @if ($storeId && $stocks->total() > 0)
        <x-slot:footer>
            <div class="small text-muted">
                {{ __('inventory.showing', ['from' => $stocks->firstItem() ?? 0, 'to' => $stocks->lastItem() ?? 0, 'total' => $stocks->total()]) }}
            </div>
            <div class="dt-pro-pagination-wrapper ms-auto">
                {{ $stocks->withQueryString()->links() }}
            </div>
        </x-slot:footer>
        @endif
    </x-admin.data-table-pro>

    @if ($storeId && $recentMovements->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
        <div class="card-header bg-white border-bottom px-4 py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-exchange-alt me-2 text-muted"></i>{{ __('inventory.recent_movements') }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.movement_date') }}</th>
                        <th style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.product') }}</th>
                        <th style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.movement_type') }}</th>
                        <th class="text-end" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.qty') }}</th>
                        <th style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.unit') }}</th>
                        <th style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.movement_user') }}</th>
                        <th class="pe-4" style="background:#f4f5f7;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#5c636a">{{ __('inventory.movement_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentMovements as $mv)
                    <tr>
                        <td class="ps-4 small text-muted">{{ $mv->created_at->format('d M Y H:i') }}</td>
                        <td class="fw-medium">{{ $mv->product->product_name ?? '—' }}</td>
                        <td>
                            @if ($mv->type === 'in')
                                <span class="dt-pro-movement-in"><i class="fas fa-arrow-down me-1"></i>{{ __('inventory.movement_in') }}</span>
                            @else
                                <span class="dt-pro-movement-out"><i class="fas fa-arrow-up me-1"></i>{{ __('inventory.movement_out') }}</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace fw-medium">{{ number_format((float) $mv->quantity, 2) }}</td>
                        <td>{{ $mv->unit->code ?? '—' }}</td>
                        <td class="small">{{ $mv->user->name ?? '—' }}</td>
                        <td class="pe-4 small text-muted">{{ $mv->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <p class="small text-muted mt-3 mb-0"><i class="fas fa-info-circle me-1"></i>{{ __('inventory.middleware_hint') }}</p>
</div>

@can('inventory.stock.adjust')
@if($storeId)
<div class="modal fade" id="invAdjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form method="post" action="{{ route('inventory.adjust') }}" id="invAdjustForm">
                @csrf
                <input type="hidden" name="store_id" value="{{ $storeId }}">
                <input type="hidden" name="product_id" id="invAdjProductId">
                @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="invAdjTitle">{{ __('inventory.adjust_stock') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-3"><span id="invAdjCurrent"></span></p>
                    <x-admin.input name="delta" type="number" step="0.001" :label="__('inventory.delta_placeholder')" required wrapperClass="mb-3" id="invAdjDelta" />
                    <div class="admin-field mb-0">
                        <label class="form-label fw-semibold" for="invAdjUnit">{{ __('inventory.unit') }}</label>
                        <select name="unit_id" id="invAdjUnit" class="form-select">
                            @foreach ($units as $u)
                            <option value="{{ $u->id }}">{{ $u->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">{{ __('orders.cancel') }}</button>
                    <button type="submit" class="btn btn-dt-pro-primary rounded-pill px-4">{{ __('inventory.apply') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection

@push('scripts')
@can('inventory.stock.adjust')
<script>
(function () {
    const modal = document.getElementById('invAdjustModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn || !btn.classList.contains('inv-open-adjust')) return;
        document.getElementById('invAdjProductId').value = btn.dataset.productId;
        document.getElementById('invAdjTitle').textContent = btn.dataset.name || {{ json_encode(__('inventory.adjust_stock')) }};
        document.getElementById('invAdjCurrent').textContent = {{ json_encode(__('inventory.qty')) }} + ': ' + btn.dataset.qty;
        document.getElementById('invAdjDelta').value = '';
        document.getElementById('invAdjUnit').value = btn.dataset.unitId || '';
    });
})();
</script>
@endcan
@endpush
