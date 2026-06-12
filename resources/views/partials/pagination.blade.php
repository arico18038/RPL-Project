@if ($items->hasPages())
    <nav class="pagination-bar" aria-label="Navigasi halaman">
        @if ($items->onFirstPage())
            <span class="pagination-button disabled">&lt;&lt;</span>
        @else
            <a class="pagination-button" href="{{ $items->previousPageUrl() }}" rel="prev">&lt;&lt;</a>
        @endif

        <span class="pagination-info">Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>

        @if ($items->hasMorePages())
            <a class="pagination-button" href="{{ $items->nextPageUrl() }}" rel="next">&gt;&gt;</a>
        @else
            <span class="pagination-button disabled">&gt;&gt;</span>
        @endif
    </nav>
@endif
