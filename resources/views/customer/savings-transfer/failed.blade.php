@extends('layouts.app')


@section('content')

    <div class="container">


        <div class="alert alert-danger">

            پرداخت انجام نشد.

            لطفاً دوباره تلاش کنید.

        </div>



        <a href="{{ route('customer.savings-transfer.create') }}"
           class="btn btn-primary">

            بازگشت به صفحه پرداخت

        </a>


    </div>

@endsection
