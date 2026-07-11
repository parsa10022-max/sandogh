<aside class="h-100 bg-white">

    @php
        $user = auth()->user();
    @endphp

    <div class="p-3 border-bottom">

        <h5 class="mb-0 fw-bold text-center">
            منوی اصلی
        </h5>

    </div>

    <div class="list-group list-group-flush rounded-0">

        @foreach(config('menu') as $index => $group)

            @php

                $children = collect($group['children'] ?? []);

                $visibleChildren = $children->filter(function ($item) use ($user) {

                    if (! isset($item['roles'])) {
                        return true;
                    }

                    return in_array($user->role, $item['roles'], true);

                });

                if ($visibleChildren->isEmpty()) {
                    continue;
                }

                $groupActive = $visibleChildren->contains(function ($item) {

                    return collect($item['active'] ?? [$item['route']])
                        ->contains(fn ($route) => request()->routeIs($route));

                });

            @endphp

            <button
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center fw-semibold {{ $groupActive ? '' : 'collapsed' }}"
                data-bs-toggle="collapse"
                data-bs-target="#menu{{ $index }}"
                type="button"
            >

                <span>

                    <i class="bi bi-{{ $group['icon'] }} ms-2"></i>

                    {{ $group['title'] }}

                </span>

                <i class="bi bi-chevron-down small"></i>

            </button>

            <div
                id="menu{{ $index }}"
                class="collapse {{ $groupActive ? 'show' : '' }}"
            >

                <div class="list-group list-group-flush">

                    @foreach($visibleChildren as $item)

                        @php

                            $active = collect($item['active'] ?? [$item['route']])
                                ->contains(fn ($route) => request()->routeIs($route));

                        @endphp

                        <a
                            href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                            class="list-group-item list-group-item-action ps-5 {{ $active ? 'active' : '' }}"
                        >

                            <i class="bi bi-{{ $item['icon'] }} ms-2"></i>

                            {{ $item['title'] }}

                        </a>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

</aside>
