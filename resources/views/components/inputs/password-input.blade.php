@props([
'label',
'name',
'placeholder' => '',
'required' => false,
'readonly' => false,
'disabled' => false,
'autocomplete' => 'current-password',
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <div class="input-group">

        <input
            type="password"
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @required($required)
            @readonly($readonly)
            @disabled($disabled)

            {{ $attributes->class([
                'form-control',
                'is-invalid' => $errors->has($name),
            ]) }}
        >

        <button
            type="button"
            class="btn btn-outline-secondary toggle-password"
            data-target="{{ $name }}"
        >
            <i class="bi bi-eye"></i>
        </button>

    </div>

    <x-form.error :name="$name"/>

</x-form.group>
