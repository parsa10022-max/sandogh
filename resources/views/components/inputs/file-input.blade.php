@props([
'label',
'name',
'required' => false,
'accept' => 'image/*',
'col' => null,
])
<x-form.group :col="$col" >
    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />
    <input
        type="file"
        id="{{ $name }}"
        name="{{ $name }}"
        accept="{{ $accept }}"
        @required($required)
        {{ $attributes->class([
           'form-control',
          'is-invalid' => $errors->has($name),
]) }}  >
    <x-form.error :name="$name"/>
</x-form.group>
