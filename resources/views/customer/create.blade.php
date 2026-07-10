@extends('layouts.app')

@section('title', 'ثبت مشتری جدید')

@section('content')

    <div class="container-fluid">

        <div class="card">

            <div class="card-header">
                <h4 class="mb-0">
                    ثبت مشتری جدید
                </h4>
            </div>

            <div class="card-body">

                <form action="{{ route('customers.store') }}"
                      method="POST">

                    @csrf

                    @include('customer._form')

                </form>

            </div>

        </div>

    </div>

@endsection
