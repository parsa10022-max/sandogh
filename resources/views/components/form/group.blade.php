@props([
'col' => 'col-12 col-md-6',
])

<div {{ $attributes->class([$col, 'mb-3']) }}>
    {{ $slot }}
</div>
