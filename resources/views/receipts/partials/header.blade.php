<div class="text-center mb-4">

    {{-- لوگو (بعداً اضافه می‌شود) --}}
    {{-- <img src="{{ asset('images/logo.png') }}" height="70"> --}}

    <h4 class="fw-bold text-success mb-1">

        صندوق قرض الحسنه شهید مطهری داریون

    </h4>

    <h5 class="fw-bold text-primary">

        {{ $title ?? 'رسید تراکنش مالی' }}

    </h5>

</div>

<div class="row mb-3">

    <div class="col-6">

        <strong>شماره رسید :</strong>

        {{ $receipt_number ?? '-' }}

    </div>

    <div class="col-6 text-end">

        <strong>تاریخ :</strong>

        {{ $receipt_date ?? '-' }}

    </div>

</div>
