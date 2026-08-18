@props([
'title',
'value',
'icon' => 'bi-info-circle',
'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'customer-stat-card']) }}>

    {{-- Icon --}}
    <div class="customer-stat-icon">
        <i class="bi {{ $icon }}"></i>
    </div>

    {{-- Content --}}
    <div class="customer-stat-content">

        <div class="customer-stat-title">
            {{ $title }}
        </div>

        <div class="customer-stat-value">
            {{ $value }}
        </div>

        @if($subtitle)
            <div class="customer-stat-subtitle">
                {{ $subtitle }}
            </div>
        @endif

    </div>

</div>
