<footer class="border-top bg-light py-2 mt-auto">

    <div class="container-fluid">

        <div class="row align-items-center">

            <div class="col-md-4 text-center text-md-start">

                <small class="text-muted">

                    © {{ date('Y') }}

                    {{ config('app.name') }}

                </small>

            </div>

            <div class="col-md-4 text-center">

                <small class="text-muted">

                    نسخه

                    {{ config('app.version', '1.0.0') }}

                </small>

            </div>

            <div class="col-md-4 text-center text-md-end">

                @auth

                    <small class="text-muted">

                        {{ auth()->user()->username }}

                    </small>

                @endauth

            </div>

        </div>

    </div>
</footer>
