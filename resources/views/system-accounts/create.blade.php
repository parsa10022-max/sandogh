@extends('layouts.app')


@section('content')

    <div class="container">

        <div class="card">

            <div class="card-header">
                ایجاد حساب سیستمی
            </div>


            <div class="card-body">

                <form method="POST"
                      action="{{ route('system-accounts.store') }}">

                    @csrf


                    <div class="mb-3">

                        <label class="form-label">
                            نام حساب
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="مثلا کمک‌های مردمی"
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            شماره حساب
                        </label>

                        <input
                            type="text"
                            name="account_number"
                            class="form-control"
                        >

                    </div>


                    <button class="btn btn-primary">
                        ذخیره
                    </button>


                </form>

            </div>

        </div>

    </div>

@endsection
