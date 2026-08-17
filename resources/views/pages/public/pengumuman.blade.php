@extends('layouts.public')

@section('title', 'Pengumuman - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Pengumuman</h1>
    <p>Informasi dan pengumuman resmi dari pengurus {{ $siteMosque->name }}.</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-filter-bar">
      <input type="text" id="searchInput" placeholder="Cari pengumuman..." />
    </div>
    <div class="lp-activity-grid" id="pengumumanGrid">
      @forelse ($list as $p)
        <div class="lp-activity-card pengumuman-card" data-title="{{ strtolower($p->judul) }}">
          <div class="lp-activity-thumb"><i class="fa-solid fa-bullhorn"></i></div>
          <div class="lp-activity-body">
            <span class="lp-badge">{{ $p->kategori ?: 'Umum' }}</span>
            <div class="lp-activity-title">{{ $p->judul }}</div>
            <div class="lp-activity-meta">
              <span><i class="fa-regular fa-calendar"></i> {{ $p->tanggal->translatedFormat('d M Y') }}</span>
            </div>
            <div class="lp-activity-desc">{{ \Illuminate\Support\Str::limit($p->isi, 100) }}</div>
          </div>
        </div>
      @empty
        <p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">Belum ada pengumuman.</p>
      @endforelse
      <p id="pengumumanEmpty" style="grid-column:1/-1; text-align:center; color:var(--text-muted);" hidden>Tidak ada pengumuman yang cocok.</p>
    </div>
    <div class="lp-pagination" id="pengumumanPagination"></div>
  </div>
@endsection

@section('scripts')
  <script>
    // Maksimal 8 pengumuman per halaman, sisanya lewat pagination.
    const PER_PAGE = 8
    let currentPage = 1
    const cards = Array.from(document.querySelectorAll('.pengumuman-card'))
    const emptyMsg = document.getElementById('pengumumanEmpty')
    const noData = cards.length === 0

    function filteredCards() {
      const keyword = document.getElementById('searchInput').value.toLowerCase()
      return cards.filter((card) => card.dataset.title.includes(keyword))
    }

    function render() {
      const filtered = filteredCards()
      const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE))
      if (currentPage > totalPages) currentPage = totalPages
      const start = (currentPage - 1) * PER_PAGE

      filtered.forEach((card, i) => {
        card.style.display = i >= start && i < start + PER_PAGE ? '' : 'none'
      })
      emptyMsg.hidden = noData || filtered.length > 0

      renderPagination(filtered.length, totalPages)
    }

    function renderPagination(totalItems, totalPages) {
      const container = document.getElementById('pengumumanPagination')
      if (totalPages <= 1) {
        container.innerHTML = ''
        return
      }

      let html = ''
      html += `<button type="button" class="lp-page-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>&laquo; Sebelumnya</button>`
      for (let p = 1; p <= totalPages; p++) {
        html += `<button type="button" class="lp-page-btn${p === currentPage ? ' active' : ''}" data-page="${p}">${p}</button>`
      }
      html += `<button type="button" class="lp-page-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>Berikutnya &raquo;</button>`

      container.innerHTML = html
    }

    document.getElementById('pengumumanPagination').addEventListener('click', (e) => {
      const btn = e.target.closest('.lp-page-btn')
      if (!btn || btn.disabled) return
      currentPage = Math.max(1, Number(btn.dataset.page) || 1)
      render()
    })

    document.getElementById('searchInput').addEventListener('input', () => {
      currentPage = 1
      render()
    })
    render()
  </script>
@endsection
