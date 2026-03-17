@props([
    'name',
    'value' => '1',
    'label' => '',
    'checked' => false,
    'hint' => null,
    'error' => null,
    'id' => null,
])

@php
    $id = $id ?? str_replace(['[', ']'], '_', $name).'_'.preg_replace('/[^a-z0-9]/i', '', (string) $value);
    $err = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    if (old($name) !== null) {
        $checked = old($name) == $value || (is_array(old($name)) && in_array($value, old($name), true));
    }
@endphp

<div class="mb-2 admin-field">
    <div class="form-check">
        <input
            class="form-check-input @if($err) is-invalid @endif"
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            id="{{ $id }}"
            @checked($checked)
            {{ $attributes->except(['class']) }}
        />
        <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
    </div>
    @if ($hint && ! $err)
        <div class="form-text ms-4">{{ $hint }}</div>
    @endif
    @if ($err)
        <div class="invalid-feedback d-block ms-4">{{ $err }}</div>
    @endif
</div>
