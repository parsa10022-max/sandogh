@props([
'label',
'name',
'value' => '',
'placeholder' => '820540102680020817909002',
'required' => false,
'readonly' => false,
'disabled' => false,
'live' => false,
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <div class="input-group sheba-group" >

        {{-- پیشوند شبا --}}
        <span class="input-group-text status-icon">
            <i class="bi"></i>
        </span>


        {{-- Input --}}
        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            maxlength="24"
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

        {{-- وضعیت --}}
        <span class="input-group-text sheba-prefix fw-bold">
            IR
        </span>

    </div>

    {{-- اعتبارسنجی JavaScript --}}
    <div class="sheba-feedback"></div>

    {{-- اعتبارسنجی Laravel --}}
    <x-form.error :name="$name"/>

</x-form.group>
