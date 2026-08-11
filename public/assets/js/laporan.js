const laporanData = [
  {
    id: 1,
    judul: 'Data Jemaah',
    kategori: 'Jemaah',
    icon: 'fa-users',
    desc: 'Informasi dan statistik jamaah secara lengkap.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Data',
    count: '1,284',
    summary: [
      { label: 'Total Jemaah', value: '1,485' },
      { label: 'Jemaah Aktif', value: '1,200' },
    ],
    columns: ['No.', 'Nama', 'Status', 'Terakhir Hadir'],
    rows: [
      ['1', 'M. Reza', 'Aktif', '15 Jun 2024'],
      ['2', 'Fatimah', 'Anggota', '16 Jun 2024'],
      ['3', 'S. Abdullah', 'Aktif', '17 Jun 2024'],
    ],
  },
  {
    id: 2,
    judul: 'Laporan Keuangan',
    kategori: 'Keuangan',
    icon: 'fa-chart-pie',
    desc: 'Rincian keuangan tahunan secara komprehensif.',
    updated: 'Diperbarui: 1 Minggu Lalu',
    countLabel: 'Modul',
    count: '12',
    summary: [
      { label: 'Total Pemasukan', value: 'Rp 2.510.000.000' },
      { label: 'Total Pengeluaran', value: 'Rp 1.850.000.000' },
    ],
    columns: ['Bulan', 'Pemasukan', 'Pengeluaran'],
    rows: [
      ['Mei', 'Rp 210jt', 'Rp 165jt'],
      ['Jun', 'Rp 225jt', 'Rp 170jt'],
      ['Jul', 'Rp 240jt', 'Rp 175jt'],
    ],
  },
  {
    id: 3,
    judul: 'Kas Masjid',
    kategori: 'Keuangan',
    icon: 'fa-wallet',
    desc: 'Arus kas harian dan bulanan secara detail.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Transaksi',
    count: '54',
    summary: [
      { label: 'Saldo Kas', value: 'Rp 185.450.000' },
      { label: 'Transaksi Bulan Ini', value: '54' },
    ],
    columns: ['Tanggal', 'Jenis', 'Nominal'],
    rows: [
      ['19/07', 'Donasi', 'Rp 50jt'],
      ['18/07', 'Listrik', '-Rp 1.2jt'],
      ['17/07', 'Infaq', 'Rp 8.5jt'],
    ],
  },
  {
    id: 4,
    judul: 'Infaq & Sodaqoh',
    kategori: 'Keuangan',
    icon: 'fa-hand-holding-heart',
    desc: 'Statistik penerimaan dan penggunaan secara detail.',
    updated: 'Diperbarui: 2 Hari Lalu',
    countLabel: 'Koleksi',
    count: '89',
    summary: [
      { label: 'Total Donasi Bulan Ini', value: 'Rp 58.700.000' },
      { label: 'Donatur Aktif', value: '312' },
    ],
    columns: ['Donatur', 'Jenis', 'Nominal'],
    rows: [
      ['Bpk. Hendra', 'Infaq Jumat', 'Rp 500rb'],
      ['Anonim', 'Pembangunan', 'Rp 1jt'],
      ['Ibu Sri', 'Sodaqoh', 'Rp 250rb'],
    ],
  },
  {
    id: 5,
    judul: 'Agenda',
    kategori: 'Kegiatan',
    icon: 'fa-calendar-days',
    desc: 'Ringkasan agenda dan kegiatan yang akan datang.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Acara',
    count: '15',
    summary: [
      { label: 'Agenda Bulan Ini', value: '55' },
      { label: 'Agenda Selesai', value: '210' },
    ],
    columns: ['Tanggal', 'Nama Agenda', 'Status'],
    rows: [
      ['22 Jul', 'Kajian Subuh', 'Berlangsung'],
      ['21 Jul', 'Rapat YMBPK', 'Selesai'],
      ['30 Jul', 'Jumsih', 'Akan Datang'],
    ],
  },
  {
    id: 6,
    judul: 'Kepengurusan',
    kategori: 'Kegiatan',
    icon: 'fa-sitemap',
    desc: 'Struktur pengurus dan status jabatan terkini.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Jabatan',
    count: '12',
    summary: [
      { label: 'Jabatan Terisi', value: '9' },
      { label: 'Jabatan Kosong', value: '3' },
    ],
    columns: ['Jabatan', 'Nama', 'Status'],
    rows: [
      ['Ketua YMBPK', 'Ust. Daus Morgan', 'Terisi'],
      ['Wakil Ketua', 'Ust. Hakim', 'Terisi'],
      ['Bendahara', '-', 'Kosong'],
    ],
  },
  {
    id: 7,
    judul: 'Laporan Relawan',
    kategori: 'Kegiatan',
    icon: 'fa-hands-helping',
    desc: 'Partisipasi dan jam kontribusi relawan.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Data',
    count: '58',
    summary: [
      { label: 'Relawan Aktif', value: '58 Orang' },
      { label: 'Total Jam Kontribusi', value: '312 Jam' },
    ],
    columns: ['Nama', 'Kegiatan', 'Status'],
    rows: [
      ['M. Reza', 'Kerja Bakti', 'Hadir'],
      ['Fatimah', 'Rapat YMBPK', 'Hadir'],
      ['S. Abdullah', 'Jumsih', 'Hadir'],
    ],
  },
  {
    id: 8,
    judul: 'Inventaris',
    kategori: 'Aset',
    icon: 'fa-boxes-stacked',
    desc: 'Daftar aset dan perlengkapan masjid.',
    updated: 'Diperbarui: Hari Ini',
    countLabel: 'Item',
    count: '215',
    summary: [
      { label: 'Total Item', value: '9' },
      { label: 'Kondisi Baik', value: '6' },
    ],
    columns: ['Item', 'Kategori', 'Kondisi'],
    rows: [
      ['Sound System', 'Audio', 'Good'],
      ['Projector', 'Electronics', 'Good'],
      ['Plastic Chairs', 'Furniture', 'Damaged'],
    ],
  },
  {
    id: 9,
    judul: 'Surat Resmi',
    kategori: 'Aset',
    icon: 'fa-envelope-open-text',
    desc: 'Arsip surat masuk dan keluar.',
    updated: 'Diperbarui: Kemarin',
    countLabel: 'Surat',
    count: '102',
    summary: [
      { label: 'Terkirim', value: '72' },
      { label: 'Draft', value: '30' },
    ],
    columns: ['No. Surat', 'Subjek', 'Status'],
    rows: [
      ['911230100001', 'Undangan Maulid', 'Terkirim'],
      ['911230100002', 'Certificate of YMBPK', 'Draft'],
      ['911230100003', 'Undangan Maulid', 'Terkirim'],
    ],
  },
]

let currentPage = 1
const perPage = 9
let activeReportId = 1 // default: Data Jemaah, sesuai referensi

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
          <span class="laporan-card-title">${r.judul}</span>
        </div>
        <div class="laporan-card-desc">${r.desc}</div>
        <div class="laporan-card-meta">
          <span>${r.updated}</span>
          <span><strong>${r.count}</strong> ${r.countLabel}</span>
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

  panel.innerHTML = `
    <div class="preview-panel-header">
      <div class="preview-panel-title">${r.judul} - Preview</div>
      <div class="preview-panel-sub">Dibuat: 20 Jul 2026 | Periode: Semua data hingga saat ini</div>
    </div>
    <div class="preview-summary-box">
      <div class="preview-summary-title">Ringkasan</div>
      ${r.summary
        .map(
          (s) => `
        <div class="preview-summary-row">
          <span class="preview-summary-label">${s.label}</span>
          <span class="preview-summary-value">${s.value}</span>
        </div>
      `,
        )
        .join('')}
    </div>
    <table class="preview-mini-table">
      <thead><tr>${r.columns.map((c) => `<th>${c}</th>`).join('')}</tr></thead>
      <tbody>
        ${r.rows.map((row) => `<tr>${row.map((cell) => `<td>${cell}</td>`).join('')}</tr>`).join('')}
      </tbody>
    </table>
    <div class="preview-footer-actions">
      <button class="btn btn-outline" id="previewPrintBtn">Cetak</button>
      <button class="btn btn-outline" id="previewPdfBtn">Ekspor PDF</button>
      <button class="btn btn-primary" id="previewExcelBtn">Ekspor Excel</button>
    </div>
  `

  document
    .getElementById('previewPrintBtn')
    .addEventListener('click', () => showToast(`Cetak laporan "${r.judul}" (dummy).`, 'fa-solid fa-print'))
  document.getElementById('previewPdfBtn').addEventListener('click', () => showToast(`Export PDF "${r.judul}" (dummy).`, 'fa-solid fa-file-pdf'))
  document
    .getElementById('previewExcelBtn')
    .addEventListener('click', () => showToast(`Export Excel "${r.judul}" (dummy).`, 'fa-solid fa-file-excel'))
}

document.getElementById('exportSemuaBtn').addEventListener('click', () => {
  showToast('Export semua laporan (dummy, belum connect backend).', 'fa-solid fa-download')
})

document.getElementById('searchInput').addEventListener('input', () => {
  currentPage = 1
  renderGrid()
})
document.getElementById('kategoriFilter').addEventListener('change', () => {
  currentPage = 1
  renderGrid()
})
document.getElementById('periodeFilter').addEventListener('change', renderGrid)
document.getElementById('tahunFilter').addEventListener('change', renderGrid)

renderGrid()
renderPreview(activeReportId)
