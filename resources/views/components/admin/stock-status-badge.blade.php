@props([
    'quantity',
    'reorderLevel',
])
@php
    $q = (float) $quantity;
    $r = (float) $reorderLevel;
    if ($q <= 0) {
        $variant = 'out';
        $label = __('inventory.status_out');
    } elseif ($r > 0 && $q <= $r) {
        $variant = 'low';
        $label = __('inventory.status_low');
    } else {
        $variant = 'in';
        $label = __('inventory.status_in');
    }
@endphp
<span class="dt-pro-badge dt-pro-badge--{{ $variant }}">
    <span class="dt-pro-badge-dot"></span>{{ $label }}
</span>
