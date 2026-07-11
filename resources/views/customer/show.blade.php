@extends('layouts.app')

@section('title', 'پروفایل مشتری')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header">

                <h5>

                    <i class="bi bi-person-vcard"></i>

                    پروفایل مشتری

                </h5>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="200">کد مشتری</th>
                        <td>{{ $customer->customer_code }}</td>
                    </tr>

                    <tr>
                        <th>نام</th>
                        <td>{{ $customer->full_name }}</td>
                    </tr>

                    <tr>
                        <th>نام پدر</th>
                        <td>{{ $customer->father_name }}</td>
                    </tr>

                    <tr>
                        <th>کد ملی</th>
                        <td>{{ $customer->national_code }}</td>
                    </tr>

                    <tr>
                        <th>موبایل</th>
                        <td>{{ $customer->mobile }}</td>
                    </tr>

                    <tr>
                        <th>موبایل دوم</th>
                        <td>{{ $customer->mobile_second }}</td>
                    </tr>

                    <tr>
                        <th>وضعیت</th>
                        <td>{{ $customer->status->label() ?? $customer->status->value }}</td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

@endsection
