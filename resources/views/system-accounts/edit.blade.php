@extends('layouts.app')


@section('content')

    <div class="container">

        <div class="card">

            <div class="card-header">
                ویرایش حساب سیستمی
            </div>


            <div class="card-body">


                <form method="POST"
                      action="{{ route(
                      'system-accounts.update',
                      $systemAccount
                  ) }}">

                    @csrf
                    @method('PUT')


                    <div class="mb-3">

                        <label class="form-label">
                            نام حساب
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old(
                            'name',
                            $systemAccount->name
                        ) }}"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            شماره حساب
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $systemAccount->account_number }}"
                            readonly
                        >

                    </div>


                    <button class="btn btn-primary">

                        ذخیره تغییرات

                    </button>


                    <a href="{{ route('system-accounts.index') }}"
                       class="btn btn-secondary">

                        بازگشت

                    </a>


                </form>


            </div>

        </div>

    </div>


@endsection
