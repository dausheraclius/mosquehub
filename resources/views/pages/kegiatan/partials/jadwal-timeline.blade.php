{{-- Potongan daftar agenda (maks 10/halaman) — dipakai render awal & AJAX --}}
<div class="jk-timeline" id="jkTimeline">
  @forelse ($agendaList as $k)
    <div
      class="jk-timeline-item status-{{ $k['statusSlug'] }}"
      data-name="{{ strtolower($k['nama']) }}"
      data-category="{{ $k['kategori'] }}"
      data-location="{{ $k['lokasi'] }}"
      data-status="{{ $k['statusSlug'] }}"
      data-date="{{ $k['tanggal'] }}"
    >
      <div class="jk-timeline-time">{{ $k['jamMulaiDot'] }}</div>
      <div class="jk-timeline-dot-col"><span class="jk-timeline-dot"></span></div>
      <div class="jk-activity-card">
        <div class="jk-date"><i class="fa-regular fa-calendar"></i>{{ $k['tanggalLabel'] }} · {{ $k['hari'] }}</div>
        <div class="jk-activity-top">
          <div>
            <div class="jk-activity-name">{{ $k['nama'] }}</div>
            <div class="jk-activity-meta">
              <span><i class="fa-regular fa-clock"></i>{{ $k['jamMulai'] }} - {{ $k['jamSelesai'] }}</span>
              <span><i class="fa-solid fa-location-dot"></i>{{ $k['lokasi'] ?: '-' }}</span>
              <span><i class="fa-solid fa-user"></i>{{ $k['pemateri'] ?: '-' }}</span>
            </div>
          </div>
          <div class="jk-badges">
            <span class="jk-badge cat-{{ $k['kategoriSlug'] }}">{{ $k['kategori'] }}</span>
            <span class="jk-badge status-{{ $k['statusSlug'] }}">{{ $k['status'] }}</span>
          </div>
        </div>
        <div class="jk-activity-bottom">
          <button type="button" class="btn-sm btn-detail" onclick="jkToggleDetail(this)">Detail</button>
        </div>
        <div class="jk-detail-panel">
          <div class="jk-detail-row">
            <span class="label">Deskripsi</span><span>{{ $k['deskripsi'] ?: '-' }}</span>
          </div>
          <div class="jk-detail-row">
            <span class="label">Pemateri</span><span>{{ $k['pemateri'] ?: '-' }}</span>
          </div>
          <div class="jk-detail-row">
            <span class="label">Estimasi Peserta</span><span>{{ $k['peserta'] ?? '-' }}</span>
          </div>
        </div>
      </div>
    </div>
  @empty
  @endforelse

  <div class="jk-empty {{ $isEmpty ? 'show' : '' }}" id="jkEmpty">
    @if ($isEmpty)
      {{ $hasActiveFilter ? 'Tidak ada agenda yang cocok dengan filter yang dipilih.' : 'Belum ada agenda.' }}
    @endif
  </div>
</div>

@if ($paginator->hasPages())
  <div class="pagination-row jk-pagination" id="jkPagination">
    <button
      type="button"
      class="pagination-btn"
      data-page="{{ $paginator->currentPage() - 1 }}"
      aria-label="Halaman sebelumnya"
      {{ $paginator->onFirstPage() ? 'disabled' : '' }}
    >
      <i class="fa-solid fa-chevron-left"></i>
    </button>

    @foreach ($pageWindow as $p)
      @if ($p === '...')
        <span class="jk-pagination-ellipsis">…</span>
      @else
        <button
          type="button"
          class="pagination-btn {{ $p === $paginator->currentPage() ? 'active' : '' }}"
          data-page="{{ $p }}"
        >{{ $p }}</button>
      @endif
    @endforeach

    <button
      type="button"
      class="pagination-btn"
      data-page="{{ $paginator->currentPage() + 1 }}"
      aria-label="Halaman berikutnya"
      {{ $paginator->hasMorePages() ? '' : 'disabled' }}
    >
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  </div>
  <div class="jk-pagination-info">
    Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} agenda
  </div>
@endif
