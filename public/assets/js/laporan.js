const laporanData = window.laporanData || []

let currentPage = 1
const perPage = 9
let activeReportId = laporanData.length > 0 ? laporanData[0].id : null // default: laporan pertama

function getFilteredData() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const kategori = document.getElementById('kategoriFilter').value

  return laporanData.filter((r) => {
    const matchSearch = r.judul.toLowerCase().includes(keyword)
    const matchKategori = kategori === '' || r.kategori === kategori
    return matchSearch && matchKategori
  })
}

function renderGrid() {
  const filtered = getFilteredData()
  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage))
  if (currentPage > totalPages) currentPage = totalPages

  const start = (currentPage - 1) * perPage
  const pageItems = filtered.slice(start, start + perPage)

  const grid = document.getElementById('laporanGrid')
  grid.innerHTML = ''

  if (pageItems.length === 0) {
    grid.innerHTML = `<p style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:30px 0;">Tidak ada laporan ditemukan</p>`
  }

  pageItems.forEach((r) => {
    grid.insertAdjacentHTML(
      'beforeend',
      `
      <div class="laporan-card ${r.id === activeReportId ? 'active' : ''}" data-id="${r.id}">
        <div class="laporan-card-top">
          <div class="laporan-card-icon"><i class="fa-solid ${r.icon}"></i></div>
          <span class="laporan-card-title">${escapeHtml(r.judul)}</span>
        </div>
        <div class="laporan-card-desc">${escapeHtml(r.desc)}</div>
        <div class="laporan-card-meta">
          <span>${escapeHtml(r.updated)}</span>
          <span><strong>${escapeHtml(r.count)}</strong> ${escapeHtml(r.countLabel)}</span>
        </div>
      </div>
    `,
    )
  })

  renderPagination(totalPages)

  document.querySelectorAll('.laporan-card').forEach((el) => {
    el.addEventListener('click', () => {
      const id = parseInt(el.dataset.id)
      activeReportId = id
      renderPreview(id)
      renderGrid()
    })
  })
}

function renderPagination(totalPages) {
  const row = document.getElementById('paginationRow')
  row.innerHTML = ''
  row.insertAdjacentHTML(
    'beforeend',
    `<button class="pagination-btn" id="prevPageBtn"><i class="fa-solid fa-chevron-left"></i></button>`,
  )
  for (let p = 1; p <= totalPages; p++) {
    row.insertAdjacentHTML(
      'beforeend',
      `<button class="pagination-btn ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`,
    )
  }
  row.insertAdjacentHTML(
    'beforeend',
    `<button class="pagination-btn" id="nextPageBtn"><i class="fa-solid fa-chevron-right"></i></button>`,
  )

  row.querySelectorAll('[data-page]').forEach((btn) => {
    btn.addEventListener('click', () => {
      currentPage = parseInt(btn.dataset.page)
      renderGrid()
    })
  })
  document.getElementById('prevPageBtn').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--
      renderGrid()
    }
  })
  document.getElementById('nextPageBtn').addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++
      renderGrid()
    }
  })
}

function renderPreview(id) {
  const r = laporanData.find((item) => item.id === id)
  const panel = document.getElementById('previewPanel')

  if (!r) {
    panel.innerHTML = `
      <div class="preview-empty">
        <i class="fa-regular fa-file-lines"></i>
        <span>Tidak ada laporan untuk ditampilkan.</span>
      </div>
    `
    return
  }

  panel.innerHTML = `
    <div class="preview-panel-header">
      <div class="preview-panel-title">${escapeHtml(r.judul)} - Preview</div>
      <div class="preview-panel-sub">${escapeHtml(r.dibuat || 'Dibuat: -')} | ${escapeHtml(r.periode || 'Periode: Semua data hingga saat ini')}</div>
    </div>
    <div class="preview-summary-box">
      <div class="preview-summary-title">Ringkasan</div>
      ${r.summary
        .map(
          (s) => `
        <div class="preview-summary-row">
          <span class="preview-summary-label">${escapeHtml(s.label)}</span>
          <span class="preview-summary-value">${escapeHtml(s.value)}</span>
        </div>
      `,
        )
        .join('')}
    </div>
    <table class="preview-mini-table">
      <thead><tr>${r.columns.map((c) => `<th>${escapeHtml(c)}</th>`).join('')}</tr></thead>
      <tbody>
        ${r.rows.map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`).join('')}
      </tbody>
    </table>
    <div class="preview-footer-actions">
      <button class="btn btn-outline" id="previewPrintBtn">Cetak</button>
      <button class="btn btn-outline" id="previewPdfBtn">Ekspor PDF</button>
      <button class="btn btn-primary" id="previewExcelBtn">Ekspor Excel</button>
    </div>
  `

  document.getElementById('previewPrintBtn').addEventListener('click', () => {
    window.open(`/laporan/${r.id}/print${filterQuery()}`, '_blank')
  })
  document.getElementById('previewPdfBtn').addEventListener('click', () => {
    downloadUrl(`/laporan/${r.id}/pdf${filterQuery()}`, `laporan-${slugify(r.judul)}.pdf`)
  })
  document.getElementById('previewExcelBtn').addEventListener('click', () => {
    downloadUrl(`/laporan/${r.id}/excel${filterQuery()}`, `laporan-${slugify(r.judul)}.xlsx`)
  })
}

// ==================== FILTER PERIODE & TAHUN (server-side) ====================

// Query string filter aktif (periode/tahun) untuk dipakai di fetch & ekspor.
function filterQuery() {
  const params = new URLSearchParams()
  const periode = document.getElementById('periodeFilter').value
  const tahun = document.getElementById('tahunFilter').value
  if (periode) params.set('periode', periode)
  if (tahun) params.set('tahun', tahun)
  const qs = params.toString()
  return qs ? '?' + qs : ''
}

// Minta ulang data laporan ke server sesuai filter aktif.
async function reloadFromServer() {
  try {
    const res = await fetch(`/laporan${filterQuery()}`, {
      headers: { Accept: 'application/json' },
    })
    if (!res.ok) throw new Error('Gagal memuat data laporan')

    const data = await res.json()
    laporanData.length = 0
    laporanData.push(...data)

    activeReportId = laporanData.length > 0 ? laporanData[0].id : null
    currentPage = 1

    renderGrid()
    if (activeReportId !== null) renderPreview(activeReportId)
    updateDateRangeBtn()
  } catch (err) {
    console.error(err)
    showToast('Gagal memuat laporan dengan filter terpilih.', 'fa-solid fa-triangle-exclamation')
  }
}

function updateDateRangeBtn() {
  const span = document.querySelector('#dateRangeBtn span')
  if (!span || laporanData.length === 0) return
  span.textContent = laporanData[0].periode
}

// ==================== EKSPOR (server: cetak / PDF / Excel) ====================

// Amankan data yang berasal dari input user sebelum dirender ke HTML.
function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

function slugify(text) {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
}
document.getElementById('exportSemuaPdfBtn').addEventListener('click', () => {
  if (laporanData.length === 0) {
    showToast('Tidak ada data untuk diekspor.', 'fa-solid fa-triangle-exclamation')
    return
  }
  downloadUrl(`/laporan/ekspor/pdf${filterQuery()}`, 'laporan-semua.pdf')
})

document.getElementById('exportSemuaBtn').addEventListener('click', () => {
  if (laporanData.length === 0) {
    showToast('Tidak ada data untuk diekspor.', 'fa-solid fa-triangle-exclamation')
    return
  }
  downloadUrl(`/laporan/ekspor/excel${filterQuery()}`, 'laporan-semua.xlsx')
})

// ==================== FILTER & PENCARIAN ====================

document.getElementById('searchInput').addEventListener('input', () => {
  currentPage = 1
  renderGrid()
})
document.getElementById('kategoriFilter').addEventListener('change', () => {
  currentPage = 1
  renderGrid()
})
document.getElementById('periodeFilter').addEventListener('change', reloadFromServer)
document.getElementById('tahunFilter').addEventListener('change', reloadFromServer)

renderGrid()
if (activeReportId !== null) renderPreview(activeReportId)
