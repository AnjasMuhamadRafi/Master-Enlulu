@if ($paginator->hasPages())
<nav class="enlulu-pagination" aria-label="Navigasi halaman">
    <div class="enlulu-pagination__info">
        <span>Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</span>
    </div>
    <ul class="enlulu-pagination__list" role="list">
        {{-- Tombol Previous --}}
        @if ($paginator->onFirstPage())
            <li class="enlulu-pagination__item disabled" aria-disabled="true">
                <span class="enlulu-pagination__link" aria-label="Sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </li>
        @else
            <li class="enlulu-pagination__item">
                <a class="enlulu-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </li>
        @endif

        {{-- Nomor Halaman dengan window ±2 + ellipsis --}}
        @php
            $current   = $paginator->currentPage();
            $last      = $paginator->lastPage();
            $window    = 2;
            $pages     = [];

            // Selalu tampilkan halaman pertama
            $pages[] = 1;

            // Ellipsis kiri jika ada gap antara halaman 1 dan window kiri
            if ($current - $window > 2) {
                $pages[] = '...';
            }

            // Window halaman di sekitar current
            for ($p = max(2, $current - $window); $p <= min($last - 1, $current + $window); $p++) {
                $pages[] = $p;
            }

            // Ellipsis kanan jika ada gap antara window kanan dan halaman terakhir
            if ($current + $window < $last - 1) {
                $pages[] = '...';
            }

            // Selalu tampilkan halaman terakhir (jika > 1)
            if ($last > 1) {
                $pages[] = $last;
            }
        @endphp

        @foreach ($pages as $page)
            @if ($page === '...')
                <li class="enlulu-pagination__item disabled" aria-hidden="true">
                    <span class="enlulu-pagination__link enlulu-pagination__ellipsis">…</span>
                </li>
            @elseif ($page == $current)
                <li class="enlulu-pagination__item active" aria-current="page">
                    <span class="enlulu-pagination__link">{{ $page }}</span>
                </li>
            @else
                <li class="enlulu-pagination__item">
                    <a class="enlulu-pagination__link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                </li>
            @endif
        @endforeach

        {{-- Tombol Next --}}
        @if ($paginator->hasMorePages())
            <li class="enlulu-pagination__item">
                <a class="enlulu-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </li>
        @else
            <li class="enlulu-pagination__item disabled" aria-disabled="true">
                <span class="enlulu-pagination__link" aria-label="Berikutnya">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 12l4-4-4-4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </li>
        @endif
    </ul>
</nav>
@endif
