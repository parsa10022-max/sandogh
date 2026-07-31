@extends('layouts.app')

@section('content')

    <div class="card shadow-sm">

        <div class="card-header">
            <h5 class="mb-0">
                واریز به حساب پس‌انداز
            </h5>
        </div>


        <div class="card-body">


            <div class="mb-3">

                <strong>عضو:</strong>

                {{ $account->customer->first_name }}
                {{ $account->customer->last_name }}

                <br>

                <strong>شماره حساب:</strong>

                <span dir="ltr">
                {{ $account->account_number }}
            </span>


                <br>

                <strong>موجودی فعلی:</strong>

                {{ number_format($account->balance) }}

                ریال

            </div>



            <form method="POST"
                  action="{{ route('accounts.deposit') }}">

                @csrf


                <input type="hidden"
                       name="account_id"
                       value="{{ $account->id }}">



                <div class="mb-3">

                    <label class="form-label">
                        مبلغ واریز (ریال)
                    </label>

                    <input type="number"
                           name="amount"
                           class="form-control"
                           value="{{ old('amount') }}"
                           required>

                    @error('amount')

                    <div class="text-danger">
                        {{ $message }}
                    </div>

                    @enderror

                </div>




                <div class="mb-3">

                    <label class="form-label">
                        نوع واریز
                    </label>


                    <select name="payment_method"
                            class="form-select"
                            required>


                        <option value="">
                            انتخاب کنید
                        </option>


                        <option value="1"
                            {{ old('payment_method') == 1 ? 'selected' : '' }}>
                            نقدی
                        </option>


                        <option value="2"
                            {{ old('payment_method') == 2 ? 'selected' : '' }}>
                            دستگاه پوز
                        </option>


                        <option value="3"
                            {{ old('payment_method') == 3 ? 'selected' : '' }}>
                            درگاه آنلاین
                        </option>


                        <option value="4"
                            {{ old('payment_method') == 4 ? 'selected' : '' }}>
                            وام
                        </option>


                    </select>


                    @error('payment_method')

                    <div class="text-danger">
                        {{ $message }}
                    </div>

                    @enderror

                </div>





                <div class="mb-3">

                    <label class="form-label">
                        توضیحات
                    </label>


                    <textarea name="description"
                              class="form-control"
                              rows="3">{{ old('description') }}</textarea>


                </div>




                <button type="submit"
                        class="btn btn-success">

                    ثبت واریز

                </button>


                <a href="{{ route('accounts.show',$account) }}"
                   class="btn btn-secondary">

                    انصراف

                </a>


            </form>


        </div>

    </div>

@endsection
