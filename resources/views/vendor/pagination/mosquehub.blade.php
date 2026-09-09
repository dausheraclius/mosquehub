{{-- Pagination server-side dengan gaya tombol global (.pagination-btn di components.css)
     supaya konsisten dengan menu lain. Menggantikan template bawaan Tailwind
     yang SVG panahnya melebar tanpa batas karena Tailwind tidak dimuat. --}}
@if ($paginator->hasPages())
    <nav class="pagination-row" role="navigation" aria-label="{{ __('pagination.navigation') }}">

        {{-- Tombol sebelumnya --}}
        @if ($paginator->onFirstPage())
            <button class="pagination-btn" type="button" disabled aria-label="{{ __('pagination.previous') }}">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
        @else
            <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
              <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-ellipsis">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Tombol berikutnya --}}
        @if ($paginator->hasMorePages())
            <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
              <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <button class="pagination-btn" type="button" disabled aria-label="{{ __('pagination.next') }}">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
        @endif
    </nav>
@endif
