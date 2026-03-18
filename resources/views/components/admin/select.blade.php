@props([
    'name',
    'label' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'id' => null,
    'wrapperClass' => 'mb-3',
])

@php
    $id = $id ?: str_replace(['[', ']'], '_', $name);
    $id = trim($id, '_');
    $err = $error ?? ($errors->has($name) ? $errors->first($name) : null);
@endphp

<div class="{{ $wrapperClass }} admin-field">
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required)<span class="text-danger">*</span>@endif</label>
    @endif
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-select', 'is-invalid' => (bool) $err]) }}
    >{{ $slot }}</select>
    @if ($hint && ! $err)
        <div class="form-text">{{ $hint }}</div>
    @endif
    @if ($err)
        <div class="invalid-feedback d-block">{{ $err }}</div>
    @endif
</div>
