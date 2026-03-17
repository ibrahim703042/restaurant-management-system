@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
    'id' => null,
    'wrapperClass' => 'mb-3',
])

@php
    $id = $id ?: $attributes->get('id');
    $id = $id ?: str_replace(['[', ']'], '_', $name);
    $id = trim($id, '_');
    if ($value === null && old($name) !== null) {
        $value = old($name);
    }
    $err = $error ?? ($errors->has($name) ? $errors->first($name) : null);
@endphp

<div class="{{ $wrapperClass }} admin-field">
    @if ($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required)<span class="text-danger">*</span>@endif</label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        @if ($required) required @endif
        {{ $attributes->except(['id'])->class(['form-control', 'is-invalid' => (bool) $err]) }}
    />
    @if ($hint && ! $err)
        <div class="form-text">{{ $hint }}</div>
    @endif
    @if ($err)
        <div class="invalid-feedback d-block">{{ $err }}</div>
    @endif
</div>
