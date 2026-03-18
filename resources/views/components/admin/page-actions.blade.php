@props([
    'title',
])
<div class="dt-pro-page-head d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="dt-pro-page-title h3 fw-bold text-dark mb-0">{{ $title }}</h1>
    @isset($actions)
    <div class="d-flex flex-wrap gap-2 align-items-center">{{ $actions }}</div>
    @endisset
</div>
