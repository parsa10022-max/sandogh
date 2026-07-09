@props([
'label',
'name',
'value' => '',
'placeholder' => '1234567890',
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

    <div class="input-group">

        {{-- آیکون کد ملی --}}
        <span class="input-group-text">
            <i class="bi bi-person-vcard"></i>
        </span>

        {{-- Input --}}
        <input
            type="tel"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            maxlength="10"
            inputmode="numeric"
            autocomplete="off"
            dir="ltr"
            data-live="{{ $live ? 'true' : 'false' }}"

            @required($required)
            @readonly($readonly)
            @disabled($disabled)

            {{ $attributes->class([
                'form-control',
                'national-code-input',
                'is-invalid' => $errors->has($name),
            ]) }}
        >

        {{-- آیکون وضعیت --}}
        <span class="input-group-text status-icon">
            <i class="bi"></i>
        </span>

    </div>

    {{-- پیام اعتبارسنجی JavaScript --}}
    <div class="national-code-feedback"></div>

    {{-- پیام اعتبارسنجی Laravel --}}
    <x-form.error :name="$name"/>

</x-form.group>
