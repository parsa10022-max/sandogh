@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')

    <div class="container-fluid dashboard-page">


        @include('dashboard.partials.statistics')
        {{-- دسترسی سریع --}}
        @include('dashboard.partials.quick-links')
        @include('dashboard.partials.action-needed')


        {{-- آخرین وام‌ها --}}
        @include('dashboard.partials.latest-loans')


        {{-- اقساط معوق --}}
        @include('dashboard.partials.overdue-installments')


        {{-- سررسیدهای پیش رو --}}
        @include('dashboard.partials.upcoming-installments')


        {{-- آخرین پرداخت‌ها --}}
        @include('dashboard.partials.latest-payments')




    </div>

@endsection
