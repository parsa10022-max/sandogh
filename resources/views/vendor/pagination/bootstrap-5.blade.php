@if ($paginator->hasPages())

    <div class="app-pagination">

        {{-- اطلاعات صفحات --}}
        <div class="app-pagination__info">
            نمایش
            <strong>{{ fa_number($paginator->firstItem()) }}</strong>
            تا
            <strong>{{ fa_number($paginator->lastItem()) }}</strong>
            از
            <strong>{{ fa_number($paginator->total()) }}</strong>
            نتیجه
        </div>

        {{-- Pagination --}}
        <ul class="pagination">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())

                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>

            @else

                <li class="page-item">
                    <a
                        class="page-link"
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        aria-label="صفحه قبلی"
                    >
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>

            @endif


            {{-- Pages --}}
            @foreach ($elements as $element)

                @if (is_string($element))

                    <li class="page-item disabled">
                        <span class="page-link">
                            {{ $element }}
                        </span>
                    </li>

                @endif


                @if (is_array($element))

                    @foreach ($element as $page => $url)

                        @if ($page == $paginator->currentPage())

                            <li
                                class="page-item active"
                                aria-current="page"
                            >
                                <span class="page-link">
                                    {{ fa_number($page) }}
                                </span>
                            </li>

                        @else

                            <li class="page-item">

                                <a
                                    class="page-link"
                                    href="{{ $url }}"
                                >
                                    {{ fa_number($page) }}
                                </a>

                            </li>

                        @endif

                    @endforeach

                @endif

            @endforeach


            {{-- Next --}}
            @if ($paginator->hasMorePages())

                <li class="page-item">

                    <a
                        class="page-link"
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        aria-label="صفحه بعدی"
                    >
                        <i class="bi bi-chevron-left"></i>
                    </a>

                </li>

            @else

                <li class="page-item disabled">

                    <span class="page-link">
                        <i class="bi bi-chevron-left"></i>
                    </span>

                </li>

            @endif

        </ul>

    </div>

@endif



