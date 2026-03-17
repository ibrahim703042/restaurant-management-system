@props([
    'label' => null,
    'error' => null,
])

@php
    $err = $error;
@endphp

<div class="mb-3 admin-field admin-checkbox-group">
    @if ($label)
        <div class="form-label fw-semibold">{{ $label }}</div>
    @endif
    <div class="admin-checkbox-group__items {{ $err ? 'is-invalid' : '' }}">
        {{ $slot }}
    </div>
    @if ($err)
        <div class="invalid-feedback d-block">{{ $err }}</div>
    @endif
</div>
