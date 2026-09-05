@props([
'label' => '',
'name',
'value' => '',
'placeholder' => '24 رقم بدون IR',
'required' => false,
'readonly' => false,
'disabled' => false,
'live' => false,
'col' => null,
])

<x-form.group :col="$col">

    @if($label)
        <x-form.label
            :label="$label"
            :name="$name"
            :required="$required"
        />
    @endif

    <div class="sheba-field">

        {{-- نام بانک --}}
        <div class="sheba-bank">
            <span class="sheba-bank__text">بانک</span>
        </div>

        {{-- شماره شبا --}}
        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, \App\Support\Iban::formatDigits($value)) }}"
            placeholder="{{ $placeholder }}"
            minlength="24"
            maxlength="24"
            inputmode="numeric"
            autocomplete="off"
            dir="ltr"

            data-live="{{ $live ? 'true' : 'false' }}"

            @required($required)
            @readonly($readonly)
            @disabled($disabled)

            {{ $attributes->class([
                'sheba-input',
                'is-invalid' => $errors->has($name),
            ]) }}
        >

        {{-- IR --}}
        <div class="sheba-prefix">
            IR
        </div>

    </div>

    <div class="sheba-feedback"></div>

    <x-form.error :name="$name"/>

</x-form.group>

