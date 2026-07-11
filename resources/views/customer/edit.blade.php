@extends('layouts.app')

@section('title', 'ویرایش مشتری')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0">

                    <i class="bi bi-pencil-square"></i>

                    ویرایش مشتری

                </h5>

                <a href="{{ route('customers.index') }}"
                   class="btn btn-outline-secondary">

                    بازگشت

                </a>

            </div>

            <div class="card-body">

                <form action="{{ route('customers.update', $customer) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    @include('customer._form')

                </form>

            </div>

        </div>

    </div>

@endsection
