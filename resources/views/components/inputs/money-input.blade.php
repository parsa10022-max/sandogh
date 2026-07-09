@props([
'label',
'name',
'value' => '',
'placeholder' => '1,000,000',
'required' => false,
'readonly' => false,
'disabled' => false,
'live' => '',
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <div class="input-group">

        {{-- آیکون پول --}}
        <span class="input-group-text">
            <i class="bi bi-cash-stack"></i>
        </span>

        {{-- Input --}}
        <input
            type="tel"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            maxlength="18"
            inputmode="numeric"
            autocomplete="off"

            data-live="{{ $live ? 'true' : 'false' }}"

            @required($required)
            @readonly($readonly)
            @disabled($disabled)

            {{ $attributes->class([
                'form-control',
                'money-input',
                'is-invalid' => $errors->has($name),
            ]) }}
        >

        {{-- آیکون وضعیت --}}
        <span class="input-group-text status-icon">
            <i class="bi"></i>
        </span>

    </div>

    {{-- پیام اعتبارسنجی JavaScript --}}
    <div class="money-feedback"></div>

    {{-- پیام اعتبارسنجی Laravel --}}
    <x-form.error :name="$name"/>

</x-form.group>
