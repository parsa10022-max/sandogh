@props([
'label',
'name',
'rows' => 3,
'value' => '',
'placeholder' => '',
'required' => false,
'col' => 'col-12',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $attributes->class([
    'form-control',
    'is-invalid' => $errors->has($name),
]) }}
    >
        {{ old($name, $value) }}</textarea>

    <x-form.error :name="$name"/>
</x-form.group>


