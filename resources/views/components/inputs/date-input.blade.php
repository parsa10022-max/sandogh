@props([
'label',
'name',
'value' => '',
'required' => false,
'readonly' => false,
'disabled' => false,
'placeholder' => '1405/04/28',
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <input
        type="text"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        data-jdp
        autocomplete="off"

        @required($required)
        @readonly($readonly)
        @disabled($disabled)

        {{ $attributes->class([
            'form-control',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

    <x-form.error :name="$name"/>

</x-form.group>
