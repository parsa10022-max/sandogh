@props([
'label',
'name',
'value' => '',
'required' => false,
'disabled' => false,
'col' => 'col-12 col-md-6',
])

<x-form.group :col="$col">

    <x-form.label
        :label="$label"
        :name="$name"
        :required="$required"
    />

    <div class="input-group">

        {{-- Calendar Icon --}}
        <span class="input-group-text">
            <i class="bi bi-calendar-event"></i>
        </span>

        {{-- Persian Date Picker --}}
        <persian-datepicker-element
            id="{{ $name }}_picker"
            class="form-control date-input"
            format="YYYY/MM/DD"
            @required($required)
            @disabled($disabled)
        >
        </persian-datepicker-element>

        {{-- Hidden Gregorian Date --}}
        <input
            type="hidden"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name,$value) }}"
        >

        {{-- Status --}}
        <span class="input-group-text status-icon">
            <i class="bi"></i>
        </span>

    </div>

    <div class="date-feedback"></div>

    <x-form.error :name="$name"/>

</x-form.group>
