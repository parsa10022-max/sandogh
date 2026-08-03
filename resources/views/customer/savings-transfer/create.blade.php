@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="card shadow-sm">

            <div class="card-header">
                <h5 class="mb-0">
                    واریز به حساب پس‌انداز عضو دیگر
                </h5>
            </div>


            <div class="card-body">


                <div id="customer-result"
                     class="alert alert-info d-none">

                </div>



                <form method="POST"
                      action="{{ route('customer.savings-transfer.store') }}">

                    @csrf



                    <input type="hidden"
                           name="receiver_customer_id"
                           id="receiver_customer_id">



                    <div class="mb-3">

                        <label class="form-label">
                            شماره عضویت مقصد
                        </label>


                        <div class="input-group">


                            <input type="text"
                                   id="customer_keyword"
                                   class="form-control"
                                   placeholder="مثلاً 10025">


                            <button type="button"
                                    id="search_customer"
                                    class="btn btn-primary">

                                جستجو

                            </button>


                        </div>

                    </div>





                    <div class="mb-3">

                        <label class="form-label">
                            مبلغ واریز (ریال)
                        </label>


                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1000"
                               required>

                    </div>




                    <button type="submit"
                            class="btn btn-success"
                            id="payment_button"
                            disabled>

                        پرداخت آنلاین

                    </button>



                </form>


            </div>

        </div>


    </div>
    @push('scripts')

        <script>

            const searchUrl =
                "{{ route('customer.savings-transfer.search') }}";


            const csrfToken =
                "{{ csrf_token() }}";

        </script>


        @vite([
        'resources/js/customer/savings-transfer.js'
        ])


    @endpush


@endsection
