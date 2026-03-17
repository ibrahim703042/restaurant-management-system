@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'error' => null,
])

@php
    $err = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $sel = old($name, $selected);
@endphp

<div class="mb-3 admin-field admin-radio-group">
    @if ($label)
        <div class="form-label fw-semibold">{{ $label }}@if(isset($required) && $required)<span class="text-danger">*</span>@endif</div>
    @endif
    @foreach ($options as $optValue => $optLabel)
        @php
            $rid = str_replace(['[', ']'], '_', $name).'_'.preg_replace('/[^a-z0-9]/i', '', (string) $optValue);
            $isChecked = (string) $sel === (string) $optValue;
        @endphp
        <div class="form-check">
            <input
                class="form-check-input @if($err) is-invalid @endif"
                type="radio"
                name="{{ $name }}"
                value="{{ $optValue }}"
                id="{{ $rid }}"
                @checked($isChecked)
            />
            <label class="form-check-label" for="{{ $rid }}">{{ $optLabel }}</label>
        </div>
    @endforeach
    @isset($slot)
        @if ((string) $slot !== '')
            <div class="mt-1">{{ $slot }}</div>
        @endif
    @endisset
    @if ($err)
        <div class="invalid-feedback d-block">{{ $err }}</div>
    @endif
</div>
