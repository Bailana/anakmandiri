@if ($paginator->hasPages())
<nav>
  <ul class="pagination mb-0 justify-content-end flex-wrap flex-sm-nowrap small-pagination" id="custom-pagination">
    @php
    $total = $paginator->lastPage();
    $current = $paginator->currentPage();
    $isMobile = false;
    if (request()->header('x-pagination-mobile') || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile|Android|iPhone|iPad/i', $_SERVER['HTTP_USER_AGENT']))) {
    $isMobile = true;
    }
    @endphp
    @if ($isMobile)
    {{-- Mobile: hanya prev/next --}}
    @if ($paginator->onFirstPage())
    <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
      <span class="page-link" aria-hidden="true">&lsaquo;</span>
    </li>
    @else
    <li class="page-item">
      <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="« Previous">&lsaquo;</a>
    </li>
    @endif
    @if ($paginator->hasMorePages())
    <li class="page-item">
      <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next »">&rsaquo;</a>
    </li>
    @else
    <li class="page-item disabled" aria-disabled="true" aria-label="Next »">
      <span class="page-link" aria-hidden="true">&rsaquo;</span>
    </li>
    @endif
    @else
    {{-- Desktop: tampilkan semua --}}
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
    <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
      <span class="page-link" aria-hidden="true">&lsaquo;</span>
    </li>
    @else
    <li class="page-item">
      <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="« Previous">&lsaquo;</a>
    </li>
    @endif
    @php
    $window = 2;
    @endphp
    @if ($total <= 7)
      @for ($i=1; $i <=$total; $i++)
      <li class="page-item {{ $i == $current ? 'active' : '' }}">
      <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
      </li>
      @endfor
      @else
      <li class="page-item {{ 1 == $current ? 'active' : '' }}">
        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
      </li>
      @if ($current > $window + 2)
      <li class="page-item disabled"><span class="page-link">…</span></li>
      @endif
      @for ($i = max(2, $current - $window); $i <= min($total - 1, $current + $window); $i++)
        <li class="page-item {{ $i == $current ? 'active' : '' }}">
        <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
        </li>
        @endfor
        @if ($current < $total - $window - 1)
          <li class="page-item disabled"><span class="page-link">…</span></li>
          @endif
          <li class="page-item {{ $total == $current ? 'active' : '' }}">
            <a class="page-link" href="{{ $paginator->url($total) }}">{{ $total }}</a>
          </li>
          @endif
          {{-- Next Page Link --}}
          @if ($paginator->hasMorePages())
          <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next »">&rsaquo;</a>
          </li>
          @else
          <li class="page-item disabled" aria-disabled="true" aria-label="Next »">
            <span class="page-link" aria-hidden="true">&rsaquo;</span>
          </li>
          @endif
          @endif
  </ul>
</nav>
<style>
  @media (max-width: 767.98px) {
    #custom-pagination {
      font-size: 0.95em;
    }

    #custom-pagination.pagination {
      padding: 0;
    }

    #custom-pagination.pagination-sm .page-link {
      padding: 0.25rem 0.5rem;
      font-size: 0.95em;
      min-width: 32px;
    }

    #custom-pagination.pagination-sm .page-item {
      min-width: 32px;
    }
  }
</style>
<script>
  // Tambahkan class pagination-sm di mobile
  document.addEventListener('DOMContentLoaded', function() {
    var pag = document.getElementById('custom-pagination');
    if (pag && window.innerWidth <= 767.98) {
      pag.classList.add('pagination-sm');
    }
  });
</script>
@endif