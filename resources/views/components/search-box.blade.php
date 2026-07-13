<div class="card mb-3">

    <div class="card-body">

        <form method="GET" action="{{ $action }}">

            <div class="row">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="{{ $placeholder ?? 'جستجو...' }}"
                        value="{{ request('search') }}"
                    >

                </div>

                <div class="col-auto">

                    <button class="btn btn-primary">

                        <i class="bi bi-search"></i>

                        جستجو

                    </button>

                </div>

                @if(request()->filled('search'))

                    <div class="col-auto">

                        <a
                            href="{{ $action }}"
                            class="btn btn-secondary">

                            پاک کردن

                        </a>

                    </div>

                @endif

            </div>

        </form>

    </div>

</div>
