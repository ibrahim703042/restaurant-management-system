@props([
    'name',
    'value',
    'label' => '',
    'checked' => false,
    'id' => null,
])

@php
    $id = $id ?? str_replace(['[', ']'], '_', $name).'_'.preg_replace('/[^a-z0-9]/i', '', (string) $value);
    if (old($name) !== null) {
        $checked = (string) old($name) === (string) $value;
    }
@endphp

<div class="form-check mb-2">
    <input
        class="form-check-input"
        type="radio"
        name="{{ $name }}"
        value="{{ $value }}"
        id="{{ $id }}"
        @checked($checked)
        {{ $attributes->except(['class']) }}
    />
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>
