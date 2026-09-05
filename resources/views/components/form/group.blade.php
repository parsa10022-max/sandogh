@props([
'col' => null,
])

<div {{ $attributes->class([
    'mb-3',
    $col,
]) }}>
    {{ $slot }}
</div>
