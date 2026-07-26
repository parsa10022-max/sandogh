@extends('layouts.app')

@section('content')

    <div class="container py-4">

        <div class="receipt shadow rounded bg-white">

            {{-- Header --}}
            @include('receipts.partials.header')

            <hr>

            {{-- محتوای رسید --}}
            @yield('receipt-content')

            <hr>

            {{-- Footer --}}
            @include('receipts.partials.footer')

        </div>

        <div class="text-center mt-3 d-print-none">

            <button
                class="btn btn-success"
                onclick="window.print()">

                <i class="bi bi-printer"></i>

                چاپ رسید

            </button>

            <a
                href="{{ url()->previous() }}"
                class="btn btn-secondary">

                بازگشت

            </a>

        </div>

    </div>

@endsection
