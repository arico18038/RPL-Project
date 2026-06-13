@if ($items->hasPages())
    <nav class="pagination-bar" aria-label="Navigasi halaman">
        @if ($items->onFirstPage())
            <span class="pagination-button disabled">
                <img src="{{ asset('images/icon/Icon hide sidebar.png') }}" alt="" class="button-icon invert-icon">
            </span>
        @else
            <a class="pagination-button" href="{{ $items->previousPageUrl() }}" rel="prev">
                <img src="{{ asset('images/icon/Icon hide sidebar.png') }}" alt="" class="button-icon invert-icon">
            </a>
        @endif

        <span class="pagination-info">Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>

        @if ($items->hasMorePages())
            <a class="pagination-button" href="{{ $items->nextPageUrl() }}" rel="next">
                <img src="{{ asset('images/icon/Icon show sidebar.png') }}" alt="" class="button-icon invert-icon">
            </a>
        @else
            <span class="pagination-button disabled">
                <img src="{{ asset('images/icon/Icon show sidebar.png') }}" alt="" class="button-icon invert-icon">
            </span>
        @endif
    </nav>
@endif
