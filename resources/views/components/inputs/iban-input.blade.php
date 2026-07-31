@props([
'label'=> '',
'name',
'value' => '',
'placeholder' => '24 رقم بدون IR',
'required' => false,
'readonly' => false,
'disabled' => false,
'live' => false,
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    @if($label)

        <x-form.label
            :label="$label"
            :name="$name"
            :required="$required"
        />

    @endif

        <div class="input-group sheba-group">

            @if($readonly)

                <input type="hidden"
                       name="{{ $name }}"
                       value="{{ $value }}">

            @endif

        {{-- نام بانک --}}
        <span class="input-group-text bg-light bank-name">
    -
</span>


        {{-- Input --}}
        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, \App\Support\Iban::formatDigits($value)) }}"
            placeholder="{{ $placeholder }}"
            minlength="24"
            maxlength="31"
            inputmode="numeric"
            autocomplete="off"
            dir="ltr"

            data-live="{{ $live ? 'true' : 'false' }}"

            @required($required)
            @readonly($readonly)
            @disabled($disabled)

            {{ $attributes->class([
                'form-control',
                'sheba-input',
                'is-invalid' => $errors->has($name),
            ]) }}
        >


        {{-- پیشوند شبا --}}
        <span class="input-group-text sheba-prefix fw-bold">
            IR
        </span>

    </div>


    {{-- اعتبارسنجی JavaScript --}}
    <div class="sheba-feedback"></div>


    {{-- اعتبارسنجی Laravel --}}
        {{-- <x-form.error :name="$name"/> --}}

</x-form.group>
