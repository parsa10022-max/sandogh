@props([
'label',
'name',
'type' => 'text',
'required' => false,
'col' => null,
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        @required($required)

        {{ $attributes->class([
            'form-control',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

    <x-form.error :name="$name"/>

</x-form.group>
