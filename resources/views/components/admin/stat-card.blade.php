@props([
    'variant' => 'stripe',
    'label' => '',
    'value' => '',
    'accent' => 'primary',
    'icon' => 'fas fa-chart-line',
    'iconClass' => 'text-primary',
    'href' => null,
])

@php
    $accents = [
        'primary' => '#0d6efd',
        'success' => '#198754',
        'purple' => '#6f42c1',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#0dcaf0',
    ];
    $borderColor = $accents[$accent] ?? $accents['primary'];
@endphp

@if ($variant === 'icon')
    @if ($href)
        <a href="{{ $href }}" class="card shadow-sm h-100 admin-stat-card admin-stat-card--icon border-0 text-decoration-none text-reset d-block">
    @else
        <div class="card shadow-sm h-100 admin-stat-card admin-stat-card--icon border-0">
    @endif
        <div class="card-body text-center py-4">
            <i class="{{ $icon }} fa-2x {{ $iconClass }} mb-2 d-block"></i>
            <h4 class="mb-1 fw-bold">{{ $value }}</h4>
            <span class="text-muted small">{{ $label }}</span>
            @isset($footer)
                <div class="mt-3">{{ $footer }}</div>
            @endisset
        </div>
    @if ($href)
        </a>
    @else
        </div>
    @endif
@else
    @if ($href)
        <a href="{{ $href }}" class="card border-0 shadow-sm h-100 admin-stat-card admin-stat-card--stripe text-decoration-none text-reset d-block" style="border-left: 4px solid {{ $borderColor }} !important;">
    @else
        <div class="card border-0 shadow-sm h-100 admin-stat-card admin-stat-card--stripe" style="border-left: 4px solid {{ $borderColor }} !important;">
    @endif
        <div class="card-body">
            <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.02em;">{{ $label }}</div>
            <h3 class="mb-0 mt-1 fw-bold">{{ $value }}</h3>
            @isset($footer)
                <div class="mt-2 small">{{ $footer }}</div>
            @endisset
        </div>
    @if ($href)
        </a>
    @else
        </div>
    @endif
@endif
