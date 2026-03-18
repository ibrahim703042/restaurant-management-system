@props([
    'totalValue' => 0,
    'totalProducts' => 0,
    'inStock' => 0,
    'lowStock' => 0,
    'outStock' => 0,
])
@php
    $t = (int) $inStock + (int) $lowStock + (int) $outStock;
    if ($t > 0) {
        $pctIn = round(((int) $inStock / $t) * 100);
        $pctLow = round(((int) $lowStock / $t) * 100);
        $pctOut = max(0, 100 - $pctIn - $pctLow);
    } else {
        $pctIn = $pctLow = $pctOut = 0;
    }
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4 dt-pro-summary">
    <div class="card-body p-4 p-lg-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-4">
                <div class="text-muted small fw-semibold text-uppercase letter-spacing">{{ __('inventory.summary_total_value') }}</div>
                <div class="dt-pro-summary-value fw-bold text-dark mt-1">{{ number_format((float) $totalValue, 0) }}</div>
            </div>
            <div class="col-lg-8">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-2">
                    <span class="fw-semibold text-dark">{{ number_format((int) $totalProducts) }} {{ __('inventory.summary_products') }}</span>
                </div>
                <div class="dt-pro-segbar rounded-pill overflow-hidden d-flex mb-2" style="height:10px;">
                    <div class="dt-pro-segbar-in" style="width: {{ $pctIn }}%;" title="{{ __('inventory.status_in') }}"></div>
                    <div class="dt-pro-segbar-low" style="width: {{ $pctLow }}%;" title="{{ __('inventory.status_low') }}"></div>
                    <div class="dt-pro-segbar-out" style="width: {{ $pctOut }}%;" title="{{ __('inventory.status_out') }}"></div>
                </div>
                <div class="d-flex flex-wrap gap-4 small">
                    <span><span class="dt-pro-dot dt-pro-dot--in"></span> {{ __('inventory.status_in') }} <strong>{{ $inStock }}</strong></span>
                    <span><span class="dt-pro-dot dt-pro-dot--low"></span> {{ __('inventory.status_low') }} <strong>{{ $lowStock }}</strong></span>
                    <span><span class="dt-pro-dot dt-pro-dot--out"></span> {{ __('inventory.status_out') }} <strong>{{ $outStock }}</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>
