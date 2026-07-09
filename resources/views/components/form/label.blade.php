@props([
'label',
'name',
'required' => false,
])
<label
    for="{{ $name }}"
    {{ $attributes->class(['form-label']) }}
>
    {{ $label }}

    @if($required)
        <span class="text-danger">*</span>
    @endif
</label>
