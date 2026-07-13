@extends('layouts.app')

@section('title', 'ویرایش نوع وام')

@section('content')

    <div class="container-fluid">

        <x-page-header title="ویرایش نوع وام">

            <a href="{{ route('loan-types.index') }}"
               class="btn btn-secondary">

                <i class="bi bi-arrow-right"></i>

                بازگشت

            </a>

        </x-page-header>

        <div class="card">

            <div class="card-body">

                <form action="{{ route('loan-types.update', $loanType) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    @include('loan-types._form')

                </form>

            </div>

        </div>

    </div>

@endsection
