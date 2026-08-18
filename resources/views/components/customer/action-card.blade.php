@props([
'title',
'icon' => 'bi-grid',
'href' => '#',
])

<a href="{{ $href }}"
   class="customer-action-card text-decoration-none">

    <div class="customer-action-icon">
        <i class="bi {{ $icon }}"></i>
    </div>

    <div class="customer-action-title">
        {{ $title }}
    </div>

</a>
