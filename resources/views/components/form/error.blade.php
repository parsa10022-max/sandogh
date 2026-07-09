@props([
'name',
])
@error($name)
<div {{ $attributes->class(['invalid-feedback']) }}>
    {{ $message }}
</div>
@enderror
