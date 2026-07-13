@extends('layouts.app')

@section('content')

    <div class="container">

        <h4 class="mb-4">
            ثبت نوع وام
        </h4>

        <form action="{{ route('loan-types.store') }}" method="POST">

            @csrf

            <div class="mb-3">
                <label class="form-label">
                    نام نوع وام
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                >

                @error('name')
                <div class="text-danger">
                    {{ $message }}
                </div>
                @enderror
            </div>


            <div class="mb-3">
                <label class="form-label">
                    پیش‌شماره وام
                </label>

                <input
                    type="text"
                    name="prefix"
                    class="form-control"
                    value="{{ old('prefix') }}"
                >

                @error('prefix')
                <div class="text-danger">
                    {{ $message }}
                </div>
                @enderror
            </div>


            <div class="mb-3">
                <label class="form-label">
                    توضیحات
                </label>

                <textarea
                    name="description"
                    class="form-control"
                >{{ old('description') }}</textarea>
            </div>


            <div class="mb-3">

                <label class="form-label">
                    وضعیت
                </label>

                <select name="is_active" class="form-select">

                    <option value="1">
                        فعال
                    </option>

                    <option value="0">
                        غیرفعال
                    </option>

                </select>

            </div>


            <button class="btn btn-primary">
                ذخیره
            </button>

        </form>

    </div>

@endsection
