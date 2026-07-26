@props([
'title',
'value',
'icon',
'color' => 'primary',
'subValue' => null,
])

<div class="card border-0 shadow-sm h-100 dashboard-stat-card">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-start">

            <div>

                <div class="text-muted small mb-2">
                    {{ $title }}
                </div>

                <div class="fs-2 fw-bold">
                    {{ $value }}
                </div>

                @if($subValue)
                    <div class="small text-muted mt-2">
                        {{ $subValue }}
                    </div>
                @endif

            </div>

            <div class="rounded-circle bg-{{ $color }} bg-opacity-10 p-3">

                <i class="bi {{ $icon }} text-{{ $color }} fs-3"></i>

            </div>

        </div>

    </div>

</div>

<style>

    .dashboard-stat-card{

        transition:.25s;

    }

    .dashboard-stat-card:hover{

        transform:translateY(-4px);

        box-shadow:0 .75rem 1.5rem rgba(0,0,0,.12)!important;

    }

</style>
