@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination">

        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm" style="opacity:.45">← Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline btn-sm" rel="prev">← Previous</a>
        @endif

        <span class="btn btn-sm" style="color:var(--admin-muted)">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline btn-sm" rel="next">Next →</a>
        @else
            <span class="btn btn-outline btn-sm" style="opacity:.45">Next →</span>
        @endif

    </nav>
@endif
