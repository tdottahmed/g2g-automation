@props([
    'type' => 'submit',
    'variant' => 'success',
    'icon' => 'ri-check-line',
    'disabled' => false,
    'id' => '',
])

@php
    $buttonClasses = [
        'success' => 'btn-success',
        'primary' => 'btn-primary',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
        'secondary' => 'btn-secondary',
        'dark' => 'btn-dark',
    ];
    $class = $buttonClasses[$variant] ?? 'btn-success';
@endphp

<button type="{{ $type }}" id="{{ $id }}"
    class="btn {{ $class }} btn-label waves-effect waves-light" {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'btn']) }}>
    <i class="{{ $icon }} label-icon align-middle fs-16 me-2"></i>
    <span>{{ $slot }}</span>
</button>
