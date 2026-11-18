@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'icon' => null,
    'help' => null,
    'bag' => 'default',
])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name));
    $error = $errors->getBag($bag)->first($name);
    $isPassword = $type === 'password';
@endphp
<div class="t-field">
    @if ($label)
        <label for="{{ $id }}" class="t-label">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>
    @endif
    <div class="t-input-wrap" @if ($isPassword) x-data="{ show: false }" @endif>
        @if ($icon)
            <x-icon :name="$icon" />
        @endif
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($isPassword) :type="show ? 'text' : 'password'" type="password" @else type="{{ $type }}" value="{{ old($name, $value) }}" @endif
            @if ($required) required @endif
            @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('id')->class(['t-input', 't-input--icon' => $icon, 'is-invalid' => $error]) }}
            @if ($isPassword) style="padding-right:44px" @endif
        >
        @if ($isPassword)
            <button type="button" class="t-eye" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'">
                <x-icon name="eye" x-show="!show" />
                <x-icon name="eye-off" x-show="show" x-cloak />
            </button>
        @endif
    </div>
    @if ($error)
        <span id="{{ $id }}-error" class="t-error">{{ $error }}</span>
    @elseif ($help)
        <span class="t-help">{{ $help }}</span>
    @endif
</div>
