@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">

        {{-- Info teks --}}
        <p class="text-sm text-gray-500 order-2 sm:order-1">
            @if ($paginator->firstItem())
                Menampilkan
                <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>
                &ndash;
                <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span>
                data
            @else
                {{ $paginator->count() }} data
            @endif
        </p>

        {{-- Tombol navigasi --}}
        <div class="flex items-center gap-1 order-1 sm:order-2">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-500 hover:border-green-500 hover:text-green-600 hover:bg-green-50 transition-colors"
                   aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 select-none">
                        &hellip;
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-green-600 bg-green-600 text-sm font-semibold text-white shadow-sm cursor-default">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:border-green-500 hover:text-green-600 hover:bg-green-50 transition-colors"
                               aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-500 hover:border-green-500 hover:text-green-600 hover:bg-green-50 transition-colors"
                   aria-label="{{ __('pagination.next') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif

        </div>
    </nav>
@endif
