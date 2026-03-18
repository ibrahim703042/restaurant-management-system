@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'dt-pro-card card border-0 shadow-sm rounded-4 overflow-hidden '.$class]) }}>
    @isset($toolbar)
        <div class="dt-pro-toolbar border-bottom bg-white px-3 px-lg-4 py-3">
            {{ $toolbar }}
        </div>
    @endisset
    <div class="dt-pro-table-wrap">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="dt-pro-footer border-top bg-white px-3 px-lg-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{ $footer }}
        </div>
    @endisset
</div>
