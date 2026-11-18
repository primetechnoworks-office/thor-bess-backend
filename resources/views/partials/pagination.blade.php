@if ($paginator->hasPages())
    <nav class="t-pagination" role="navigation" aria-label="Pagination">
        <span class="t-pagination__info">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <div class="t-pagination__pages">
            @if ($paginator->onFirstPage())
                <span class="is-disabled" aria-hidden="true"><x-icon name="chevron-left" size="16" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"><x-icon name="chevron-left" size="16" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="is-disabled">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"><x-icon name="chevron-right" size="16" /></a>
            @else
                <span class="is-disabled" aria-hidden="true"><x-icon name="chevron-right" size="16" /></span>
            @endif
        </div>
    </nav>
@endif
