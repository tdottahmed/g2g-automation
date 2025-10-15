@props([
    'variant' => 'primary',
    'openModal' => false,
    'icon' => '',
    'title' => '',
    'disabled' => false,
    'id' => '',
    'href' => '#',
])

@php
    $linkClasses = [
        'success' => 'btn-success',
        'primary' => 'btn-primary',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
        'secondary' => 'btn-secondary',
        'dark' => 'btn-dark',
    ];
    $class = $linkClasses[$variant] ?? 'btn-primary';
@endphp

<a href="{{ $href }}" class="btn {{ $class }} btn-label waves-effect waves-light"
    {{ $disabled ? 'aria-disabled="true"' : '' }} {{ $attributes->merge(['class' => 'btn']) }}>
    <i class="{{ $icon }} label-icon align-middle fs-16 me-2"></i>
    <span>{{ $slot }}</span>
</a>
