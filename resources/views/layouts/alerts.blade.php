{{-- Success Message --}}
@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show" role="alert">

        <i class="bi bi-check-circle-fill me-2"></i>

        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Error Message --}}
@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show" role="alert">

        <i class="bi bi-x-circle-fill me-2"></i>

        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Warning Message --}}
@if(session('warning'))

    <div class="alert alert-warning alert-dismissible fade show" role="alert">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        {{ session('warning') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Info Message --}}
@if(session('info'))

    <div class="alert alert-info alert-dismissible fade show" role="alert">

        <i class="bi bi-info-circle-fill me-2"></i>

        {{ session('info') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Validation Errors --}}
@if($errors->any())

    <div class="alert alert-danger">

        <div class="fw-bold mb-2">

            <i class="bi bi-exclamation-octagon-fill me-2"></i>

            لطفاً خطاهای زیر را برطرف کنید.

        </div>

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif
