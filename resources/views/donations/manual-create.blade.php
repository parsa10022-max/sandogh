@extends('layouts.app')

@section('title','ثبت کمک دستی')

@section('content')

    <div class="container py-4">

        <div class="card shadow">

            <div class="card-header">
                <h5 class="mb-0">
                    ثبت کمک دستی
                </h5>
            </div>


            <div class="card-body">


                @if(session('success'))

                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>

                @endif


                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif



                <form method="POST"
                      action="{{ route('donations.manual.store') }}">

                    @csrf


                    <div class="mb-3">

                        <label class="form-label">
                            نوع کمک
                        </label>


                        <select name="account_id"
                                class="form-select"
                                required>

                            <option value="">
                                انتخاب حساب
                            </option>


                            @foreach($accounts as $account)

                                <option value="{{ $account->id }}">

                                    {{ $account->name }}
                                    -
                                    {{ $account->account_number }}

                                </option>

                            @endforeach

                        </select>

                    </div>



                    <div class="mb-3">

                        <label class="form-label">
                            مبلغ (ریال)
                        </label>


                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1000"
                               required>

                    </div>



                    <button class="btn btn-success">

                        ثبت کمک

                    </button>


                </form>


            </div>

        </div>

    </div>

@endsection
