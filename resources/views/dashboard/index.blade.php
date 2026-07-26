@extends('layouts.app')

@section('title','داشبورد')

@section('content')

    <div class="container-fluid">

        @include('dashboard.partials.loan-statistics')

        @include('dashboard.partials.latest-loans')

        @include('dashboard.partials.overdue-installments')

        @include('dashboard.partials.latest-payments')

        @include('dashboard.partials.latest-payments')

        @include('dashboard.partials.quick-links')
    </div>

@endsection
