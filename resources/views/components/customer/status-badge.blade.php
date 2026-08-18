@props([
'type' => 'default',
'label',
'icon' => null,
])

@php
    $classes = match ($type) {
        'success' => 'customer-status-success',
        'warning' => 'customer-status-warning',
        'danger'  => 'customer-status-danger',
        'info'    => 'customer-status-info',
        default   => 'customer-status-default',
    };
@endphp

<span {{ $attributes->merge(['class' => "customer-status-badge {$classes}"]) }}>

    @if($icon)
        <i class="bi {{ $icon }}"></i>
    @endif

    {{ $label }}

</span>
