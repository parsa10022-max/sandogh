@props([
'title',
'value',
'icon',
'color' => 'primary',
'subValue' => null,
'unit' => null,
])

<div class="card dashboard-stat-card h-100">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-start gap-3">

            <div class="flex-grow-1">

                <div class="dashboard-stat-title">
                    {{ $title }}
                </div>

                <div class="dashboard-stat-value">
                    {{ is_numeric($value) ? number_format($value) : $value }}

                    @if($unit)
                        <span class="dashboard-stat-unit">
                            {{ $unit }}
                        </span>
                    @endif
                </div>

                @if($subValue)
                    <div class="dashboard-stat-subvalue">
                        {{ is_numeric($subValue) ? number_format($subValue) : $subValue }}
                    </div>
                @endif

            </div>

            <div class="dashboard-stat-icon bg-{{ $color }} bg-opacity-10">
                <i class="bi {{ $icon }} text-{{ $color }}"></i>
            </div>

        </div>

    </div>

</div>



