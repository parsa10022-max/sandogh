@php
    $name = $name ?? 'customer_id';
    $label = $label ?? 'مشتری';
    $required = $required ?? false;

    $value = $value
        ?? $customerId
        ?? old($name);

    $selectedCustomer = null;

    if (!empty($value)) {
        $selectedCustomer = \App\Models\Customer::find($value);
    }
@endphp


<div class="customer-picker"
     data-search-url="{{ route('customers.search.code') }}">


    <label class="form-label fw-semibold">

        {{ $label }}

        @if($required)
            <span class="text-danger">*</span>
        @endif

    </label>



    <div class="input-group">

        <input
            type="text"
            class="form-control customer-code"
            placeholder="کد مشتری را وارد کنید"
            autocomplete="off"
            value="{{ $selectedCustomer?->customer_code ?? '' }}">


        <button
            type="button"
            class="btn btn-outline-primary customer-search-btn">

            <i class="bi bi-search"></i>

        </button>

    </div>



    <input
        type="hidden"
        name="{{ $name }}"
        class="customer-id"
        value="{{ $value }}">



    <div
        class="customer-result alert alert-light border mt-3
        {{ $selectedCustomer ? '' : 'd-none' }}">


        <div class="fw-bold customer-name">

            {{ $selectedCustomer?->full_name }}

        </div>



        <div class="small text-muted">

            کد مشتری:

            <span class="customer-code-view">

                {{ $selectedCustomer?->customer_code }}

            </span>

        </div>



        <div class="small text-muted">

            موبایل:

            <span class="customer-mobile">

                {{ $selectedCustomer?->mobile }}

            </span>

        </div>


    </div>



    <div
        class="customer-error alert alert-danger mt-3 d-none">

    </div>


</div>
