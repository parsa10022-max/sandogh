@extends('layouts.app')

@section('title', 'ثبت درخواست وام')

@section('content')

    <div class="container">

        <h4 class="mb-4">ثبت درخواست وام</h4>

        <div class="alert alert-info">
            <strong>شرایط وام</strong>

            <ul class="mb-0 mt-2">
                <li>وام تا سقف ۴ میلیون تومان با بازپرداخت ۱۰ ماهه.</li>
                <li>وام از ۵ تا ۲۰ میلیون تومان با بازپرداخت ۵ ماهه.</li>
                <li>
                    وام‌های بیشتر از ۱۰ میلیون تومان پس از بررسی وضعیت مالی، سابقه بازپرداخت و منابع صندوق، توسط مدیریت بررسی و تصمیم‌گیری خواهد شد.
                </li>
                <li>وام ازدواج ۱۰ میلیون تومان با بازپرداخت ۲۰ ماهه.</li>
            </ul>
        </div>

        <div class="alert alert-warning">
            <strong>شرایط ضامن</strong>

            <ul class="mb-0 mt-2">
                <li>تمام وام‌ها نیازمند ارائه دو ضامن می‌باشند.</li>
                <li>تا سقف ۱۰ میلیون: دو سفته، دو چک صیادی یا یک سفته و یک چک صیادی.</li>
                <li>بالاتر از ۱۰ میلیون: ارائه حداقل یک چک صیادی معتبر الزامی است.</li>
            </ul>
        </div>

        <div class="card shadow-sm border-0">

            <div class="card-body">

                <form method="POST" action="{{ route('loan-requests.store') }}">

                    @csrf

                    <div class="row">

                        <div class="col-md-12 mb-3">

                            <label class="form-label">مشتری</label>

                            <select name="customer_id"
                                    class="form-select @error('customer_id') is-invalid @enderror">

                                <option value="">انتخاب کنید...</option>

                                @foreach($customers as $customer)

                                    <option value="{{ $customer->id }}"
                                            @selected(old('customer_id') == $customer->id)>

                                    {{ $customer->full_name }}

                                    </option>

                                @endforeach

                            </select>

                            @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="col-md-6 mb-3">

                            <label class="form-label">مبلغ درخواستی</label>

                            <input type="number"
                                   name="requested_amount"
                                   class="form-control @error('requested_amount') is-invalid @enderror"
                                   value="{{ old('requested_amount') }}">

                            @error('requested_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>


                        <div class="col-md-12 mb-3">

                            <label class="form-label">توضیحات</label>

                            <textarea name="description"
                                      rows="4"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>

                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                    </div>

                    <div class="text-end">

                        <a href="{{ route('loan-requests.index') }}"
                           class="btn btn-secondary">

                            انصراف

                        </a>

                        <button class="btn btn-primary">

                            ثبت درخواست

                        </button>

                    </div>
                    {{-- فیلد مبلغ --}}
                    {{-- توضیحات --}}
                    {{-- دکمه ثبت --}}

                </form>

            </div>

        </div>

    </div>

@endsection
