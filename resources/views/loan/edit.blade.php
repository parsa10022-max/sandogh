@extends('layouts.app')

@section('title', 'ویرایش وام')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header">

                <h5 class="mb-0">
                    ویرایش وام
                </h5>

            </div>

            <div class="card-body">

                <form
                    id="loan-form"
                    action="{{ route('loans.update', $loan) }}"
                    method="POST"
                    data-calculate-url="{{ route('loans.calculate') }}">

                    @csrf
                    @method('PUT')

                    @include('loan._form')

                </form>

            </div>

        </div>

    </div>

@endsection
