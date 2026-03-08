@php
    if (!isset($scrollTo)) {
        $scrollTo = 'body';
    }
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
            class="d-flex align-items-center justify-content-between">
            <div class="d-flex justify-content-between flex-fill d-sm-none">
                <ul class="pagination pagination-premium mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link px-3 py-2">
                                <i class="fas fa-chevron-left me-1"></i> {{ __('Prev') }}
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link px-3 py-2 cursor-pointer" href="{{ $paginator->previousPageUrl() }}"
                                rel="prev">
                                <i class="fas fa-chevron-left me-1"></i> {{ __('Prev') }}
                            </a>
                        </li>
                    @endif

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link px-3 py-2 cursor-pointer" href="{{ $paginator->nextPageUrl() }}"
                                rel="next">
                                {{ __('Next') }} <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link px-3 py-2">
                                {{ __('Next') }} <i class="fas fa-chevron-right ms-1"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="d-none flex-sm-fill d-sm-flex align-items-center justify-content-between w-100">
                <div>
                    <p class="small text-low mb-0">
                        {!! __('Showing') !!}
                        <span class="fw-bold text-high">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="fw-bold text-high">{{ $paginator->lastItem() }}</span>
                        {!! __('of') !!}
                        <span class="fw-bold text-high">{{ $paginator->total() }}</span>
                        {!! __('results') !!}
                    </p>
                </div>

                <div>
                    <ul class="pagination pagination-premium mb-0 gap-1">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true"
                                aria-label="{{ __('pagination.previous') }}">
                                <span class="page-link" aria-hidden="true">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link cursor-pointer" href="{{ $paginator->previousPageUrl() }}"
                                    rel="prev" aria-label="{{ __('pagination.previous') }}">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link border-0"
                                        style="background: transparent;">{{ $element }}</span></li>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <li class="page-item active" aria-current="page"><span
                                                class="page-link shadow-premium">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link cursor-pointer"
                                                href="{{ $url }}">{{ $page }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <li class="page-item">
                                <a class="page-link cursor-pointer" href="{{ $paginator->nextPageUrl() }}"
                                    rel="next" aria-label="{{ __('pagination.next') }}">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true"
                                aria-label="{{ __('pagination.next') }}">
                                <span class="page-link" aria-hidden="true">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
    @endif
</div>

<style>
    .pagination-premium .page-item .page-link {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        color: var(--text-medium);
        border-radius: 8px;
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    .pagination-premium .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
        box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);
    }

    .pagination-premium .page-item:not(.active):not(.disabled) .page-link:hover {
        background: var(--bg-input);
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .pagination-premium .page-item.disabled .page-link {
        background: var(--bg-input);
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
