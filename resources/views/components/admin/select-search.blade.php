@props([
    'name',
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'multiple' => false,
    'createUrl' => null,
    'createLabel' => null,
    'createTarget' => '_blank',
    'error' => null,
    'id' => null,
    'wrapperClass' => 'mb-3',
])

@php
    $id = $id ?: 'ts_'.preg_replace('/[^a-z0-9]+/i', '_', $name).'_'.substr(sha1($name), 0, 6);
    $err = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $ph = $placeholder ?? __('Search or choose…');
@endphp

<div class="{{ $wrapperClass }} admin-field admin-select-search-wrap">
    @if ($label)
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
            <label for="{{ $id }}" class="form-label mb-0">{{ $label }}@if ($required)<span class="text-danger">*</span>@endif</label>
            @if ($createUrl && $createLabel)
                <a href="{{ $createUrl }}" target="{{ $createTarget }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-plus me-1"></i>{{ $createLabel }}
                </a>
            @endif
        </div>
    @elseif ($createUrl && $createLabel)
        <div class="d-flex justify-content-end mb-1">
            <a href="{{ $createUrl }}" target="{{ $createTarget }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-plus me-1"></i>{{ $createLabel }}
            </a>
        </div>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($multiple) multiple @endif
        @if ($required) required @endif
        data-placeholder="{{ $ph }}"
        {{ $attributes->class(['form-select', 'admin-ts-select', 'is-invalid' => (bool) $err]) }}
    >{{ $slot }}</select>
    @if ($err)
        <div class="invalid-feedback d-block mt-1">{{ $err }}</div>
    @endif
</div>
