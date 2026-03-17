@props([
    'title' => '',
    'icon' => '',
    'tableId' => 'dt-table',
])

<div {{ $attributes->class(['card', 'card-outline', 'card-primary', 'admin-table-card']) }}>
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title mb-0">
            @if ($icon)
                <i class="{{ $icon }} me-2"></i>
            @endif
            {{ $title }}
        </h3>
        @isset($actions)
            <div class="d-flex flex-wrap gap-2 align-items-center">{{ $actions }}</div>
        @endisset
    </div>
    <div class="card-body">
        @isset($filters)
            <div class="admin-table-card__filters mb-3">{{ $filters }}</div>
        @endisset
        <div class="table-responsive">
            <table id="{{ $tableId }}" class="table table-admin table-bordered table-hover table-striped align-middle w-100">
                {{ $slot }}
            </table>
        </div>
    </div>
</div>
