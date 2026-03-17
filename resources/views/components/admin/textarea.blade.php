@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
    'rows' => 3,
    'id' => null,
    'wrapperClass' => 'mb-3',
])

@php
    $id = $id ?? str_replace(['[', ']'], '_', $name);
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
    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->class(['form-control', 'is-invalid' => (bool) $err]) }}
    >{{ $value }}</textarea>
    @if ($hint && ! $err)
        <div class="form-text">{{ $hint }}</div>
    @endif
    @if ($err)
        <div class="invalid-feedback d-block">{{ $err }}</div>
    @endif
</div>
