@props([
'label',
'name',
'options' => [],
'value' => '',
'attributesMap' => [],
'required' => false,
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @required($required)

        {{ $attributes->class([
            'form-select',
            'is-invalid' => $errors->has($name),
        ]) }}
    >

        <option value="">انتخاب کنید...</option>

        @foreach($options as $key => $text)

            <option
                value="{{ $key }}"

            @foreach($attributesMap[$key] ?? [] as $attr => $attrValue)
                {{ $attr }}="{{ $attrValue }}"
            @endforeach

            @selected(old($name, $value) == $key)
            >

            {{ $text }}

            </option>

        @endforeach

    </select>

    <x-form.error :name="$name"/>

</x-form.group>
