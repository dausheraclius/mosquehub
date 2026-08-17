@extends('layouts.public')

@section('title', 'Jadwal Kegiatan - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Jadwal Kegiatan</h1>
    <p>Agenda dan kegiatan {{ $siteMosque->name }} dalam 7 hari ke depan.</p>
  </div>

  <div class="lp-tabs">
    <button type="button" class="lp-tab active" data-tab="jadwal7">Jadwal 7 Hari</button>
    <button type="button" class="lp-tab" data-tab="semua-agenda">Semua Agenda</button>
  </div>

  {{-- Tab: Jadwal 7 hari ke depan --}}
  <div class="lp-public-section lp-tab-panel" id="tab-jadwal7">
    <div class="lp-jadwal-grid">
      @forelse ($jadwal as $day)
        <div class="lp-jadwal-day">
          <div class="lp-jadwal-day-title">{{ $day['hari'] }} <span style="color: var(--text-muted); font-size: 12px;">{{ $day['tanggal'] }}</span></div>
          @forelse ($day['items'] as $item)
            <div class="lp-jadwal-item">
              <span class="lp-jadwal-time">{{ $item['time'] }}</span>
              <div class="lp-jadwal-info">
                <div class="lp-jadwal-name">{{ $item['name'] }}</div>
                <div class="lp-jadwal-meta">{{ $item['loc'] }}</div>
              </div>
            </div>
          @empty
            <div class="lp-jadwal-item">
              <span style="color: var(--text-muted); font-size: 13px;">Tidak ada kegiatan</span>
            </div>
          @endforelse
        </div>
      @empty
        <p style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:24px;">Belum ada jadwal kegiatan.</p>
      @endforelse
    </div>
  </div>

  {{-- Tab: Semua agenda mendatang --}}
  <div class="lp-public-section lp-tab-panel" id="tab-semua-agenda" hidden>
    <h2 class="lp-section-title">Semua Agenda</h2>
    <div class="lp-filter-bar">
      <input type="text" id="searchInput" placeholder="Cari agenda..." />
      <select id="kategoriFilter">
        <option value="">Semua Kategori</option>
        <option value="Kajian">Kajian</option>
        <option value="Rapat">Rapat</option>
        <option value="Sosial">Sosial</option>
      </select>
    </div>
    <div class="lp-activity-grid" id="agendaGrid"></div>
    <div class="lp-pagination" id="agendaPagination"></div>
  </div>
@endsection

@section('scripts')
  <script>
    // Pindah tab: Jadwal 7 Hari ⇄ Semua Agenda
    document.querySelectorAll('.lp-tab').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.lp-tab').forEach((b) => b.classList.remove('active'))
        document.querySelectorAll('.lp-tab-panel').forEach((p) => (p.hidden = true))
        btn.classList.add('active')
        document.getElementById('tab-' + btn.dataset.tab).hidden = false
      })
    })

    const agendaData = @json($agendaList)

    function esc(value) {
      const s = value == null ? '' : String(value)
      return s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;')
    }

    const PER_PAGE = 8
    let currentPage = 1

    function filteredData() {
      const keyword = document.getElementById('searchInput').value.toLowerCase()
      const kategori = document.getElementById('kategoriFilter').value
      return agendaData.filter(
        (a) => a.title.toLowerCase().includes(keyword) && (kategori === '' || a.kategori === kategori),
      )
    }

    function render() {
      const filtered = filteredData()
      const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE))
      if (currentPage > totalPages) currentPage = totalPages

      const start = (currentPage - 1) * PER_PAGE
      const pageItems = filtered.slice(start, start + PER_PAGE)

      document.getElementById('agendaGrid').innerHTML =
        pageItems
          .map(
            (a) => `
    <div class="lp-activity-card" data-id="${a.id}">
      <div class="lp-activity-thumb"><i class="fa-solid fa-image"></i></div>
      <div class="lp-activity-body">
        <span class="lp-badge">${esc(a.kategori || 'Umum')}</span>
        <div class="lp-activity-title">${esc(a.title)}</div>
        <div class="lp-activity-meta">
          <span><i class="fa-regular fa-calendar"></i> ${esc(a.date)}</span>
          <span><i class="fa-solid fa-location-dot"></i> ${esc(a.location)}</span>
        </div>
        <div class="lp-activity-desc">${esc(a.desc)}</div>
        <button type="button" class="btn-lp-view-details lp-detail-toggle">Detail</button>
        <div class="lp-activity-detail" hidden>
          <div class="lp-detail-row">
            <span class="lp-detail-label"><i class="fa-solid fa-user-tie"></i> Penanggung Jawab</span>
            <span class="lp-detail-value">${esc(a.pj || '-')}</span>
          </div>
          <div class="lp-detail-row">
            <span class="lp-detail-label"><i class="fa-solid fa-hands-helping"></i> Relawan</span>
            <span class="lp-detail-value">${a.relawan.length ? a.relawan.map(esc).join(', ') : 'Belum ada relawan'}</span>
          </div>
        </div>
      </div>
    </div>
  `,
          )
          .join('') ||
        `<p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">Agenda tidak ditemukan.</p>`

      renderPagination(filtered.length, totalPages)
    }

    function renderPagination(totalItems, totalPages) {
      const container = document.getElementById('agendaPagination')
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

    document.getElementById('agendaGrid').addEventListener('click', (e) => {
      const btn = e.target.closest('.lp-detail-toggle')
      if (!btn) return
      const card = btn.closest('.lp-activity-card')
      const detail = card.querySelector('.lp-activity-detail')
      const isOpen = !detail.toggleAttribute('hidden')
      btn.textContent = isOpen ? 'Tutup' : 'Detail'
      btn.classList.toggle('active', isOpen)
    })

    document.getElementById('agendaPagination').addEventListener('click', (e) => {
      const btn = e.target.closest('.lp-page-btn')
      if (!btn || btn.disabled) return
      currentPage = Math.max(1, Number(btn.dataset.page) || 1)
      render()
    })

    document.getElementById('searchInput').addEventListener('input', () => {
      currentPage = 1
      render()
    })
    document.getElementById('kategoriFilter').addEventListener('change', () => {
      currentPage = 1
      render()
    })
    render()
  </script>
@endsection
