// ============================
// INFAQ, SODAQOH, ZAKAT, WAKAF & QURBAN (ZISWAF) - Logic Halaman
// Semua angka (summary, stat cards, chart, tabel) dihitung dari SATU
// sumber data (dataDonasi) -- biar gak ada angka yang beda-beda /
// gak nyambung antar bagian (penting buat transparansi keuangan).
//
// Tabungan Qurban DIPISAH dari dataDonasi -- bentuknya beda (progres
// per peserta, bukan transaksi flat), jadi punya sumber data & view
// sendiri (qurbanView), lihat bagian bawah file ini.
// ============================

// ---- SUMBER DATA UTAMA (nanti diganti fetch dari backend/API) ----
const dataDonasi = window.__DONASI_DATA__ || []

// ---- Daftar petugas penerima (dari Data Jemaah) ----
const PETUGAS_OPTIONS =
  window.__PETUGAS_OPTIONS__ && window.__PETUGAS_OPTIONS__.length
    ? window.__PETUGAS_OPTIONS__
    : ['Ust. Daus Morgan', 'Bpk. Ahmad Fauzi', 'Bpk. Rizki Ramadhan']

function populatePetugasOptions() {
  ;[document.getElementById('inputDonasiPetugas'), document.getElementById('inputSetoranPetugas')].forEach((select) => {
    if (!select) return
    select.innerHTML = PETUGAS_OPTIONS.map((p) => `<option value="${esc(p)}">${esc(p)}</option>`).join('')
  })
}

// ---- Metadata tiap tab/kategori (khusus tab standar, bukan Qurban) ----
const CATEGORY_META = {
  ringkasan: {
    label: 'Ringkasan',
    kategori: null,
    trend: '+18%',
    title: 'Ringkasan Ziswaf',
    subtitle: 'Kelola seluruh penerimaan Zakat, Infaq, Sodaqoh, Waqaf dan Donasi jemaah.',
    summaryLabel: 'Total Donasi Bulan Ini (Semua Kategori)',
    btnLabel: null,
    donaturLabel: 'Penyumbang',
  },
  zakat: {
    label: 'Zakat',
    kategori: 'Zakat',
    kategoris: ['Zakat', 'Zakat Fitrah', 'Zakat Maal'],
    kategoriOptions: ['Zakat Fitrah', 'Zakat Maal'],
    kategoriLocked: false,
    filterLabel: 'Jenis Zakat',
    filterOptions: ['Zakat Fitrah', 'Zakat Maal'],
    hiddenFields: ['formGroupDonasiJenis', 'formGroupDonasiPeriode'],
    donaturLabel: 'Muzakki',
    trend: '+21%',
    icon: 'fa-hand-holding-dollar',
    title: 'Rincian Zakat',
    subtitle:
      'Breakdown penerimaan zakat berdasarkan jenisnya (fitrah & maal) bulan ini.',
    summaryLabel: 'Total Zakat Bulan Ini',
    btnLabel: 'Catat Zakat',
  },
  infaq: {
    label: 'Infaq',
    kategori: 'Infaq',
    kategoris: ['Infaq', 'Infaq Jumat', 'Infaq Harian'],
    kategoriOptions: ['Infaq Jumat', 'Infaq Harian'],
    kategoriLocked: false,
    filterLabel: 'Jenis Infaq',
    filterOptions: ['Infaq Jumat', 'Infaq Harian'],
    hiddenFields: [
      'formGroupDonasiJenis',
      'formGroupDonasiDonatur',
      'formGroupDonasiTipe',
      'formGroupDonasiStatus',
      'formGroupDonasiKeterangan',
      'formGroupDonasiTanggal',
    ],
    nominalLabel: 'Total Infaq (Rp)',
    donaturLabel: 'Sumber',
    trend: '+9%',
    icon: 'fa-hand-holding-heart',
    title: 'Rincian Infaq',
    subtitle: 'Breakdown penerimaan infaq jemaah bulan ini (jumat & harian).',
    summaryLabel: 'Total Infaq Bulan Ini',
    btnLabel: 'Catat Infaq',
  },
  sedekah: {
    label: 'Sodaqoh',
    kategori: 'Sodaqoh',
    kategoris: ['Sodaqoh', 'Sodaqoh Dhuafa', 'Sodaqoh Anak Yatim', 'Sodaqoh Bencana'],
    kategoriOptions: ['Sodaqoh Dhuafa', 'Sodaqoh Anak Yatim', 'Sodaqoh Bencana'],
    kategoriLocked: false,
    filterLabel: 'Jenis Sodaqoh',
    filterOptions: ['Sodaqoh Dhuafa', 'Sodaqoh Anak Yatim', 'Sodaqoh Bencana'],
    hiddenFields: ['formGroupDonasiJenis', 'formGroupDonasiPeriode'],
    donaturLabel: 'Orang',
    trend: '+14%',
    icon: 'fa-heart',
    title: 'Rincian Sodaqoh',
    subtitle: 'Breakdown penerimaan sodaqoh jemaah bulan ini.',
    summaryLabel: 'Total Sodaqoh Bulan Ini',
    btnLabel: 'Catat Sodaqoh',
  },
  wakaf: {
    label: 'Waqaf',
    kategori: 'Wakaf',
    kategoris: ['Wakaf', 'Wakaf Uang', 'Wakaf Tanah', 'Wakaf Bangunan', 'Wakaf Al-Qur\'an'],
    kategoriOptions: ['Wakaf Uang', 'Wakaf Tanah', 'Wakaf Bangunan', 'Wakaf Al-Qur\'an'],
    kategoriLocked: false,
    filterLabel: 'Jenis Waqaf',
    filterOptions: ['Wakaf Uang', 'Wakaf Tanah', 'Wakaf Bangunan', 'Wakaf Al-Qur\'an'],
    hiddenFields: ['formGroupDonasiJenis', 'formGroupDonasiPeriode'],
    donaturLabel: 'Waqif',
    trend: '+4%',
    icon: 'fa-building-columns',
    title: 'Rincian Waqaf',
    subtitle: 'Breakdown penerimaan wakaf jemaah bulan ini -- Uang maupun Barang (tanah, bangunan, Al-Qur\'an, peralatan, dll).',
    summaryLabel: 'Total Waqaf Bulan Ini (Uang + Taksiran Barang)',
    btnLabel: 'Catat Waqaf',
  },
  donasi: {
    label: 'Donasi',
    kategori: 'Donasi',
    kategoris: ['Donasi', 'Donasi Bencana', 'Donasi Pendidikan', 'Donasi Kesehatan', 'Donasi Umum'],
    kategoriOptions: ['Donasi Bencana', 'Donasi Pendidikan', 'Donasi Kesehatan', 'Donasi Umum'],
    kategoriLocked: false,
    filterLabel: 'Jenis Donasi',
    filterOptions: ['Donasi Bencana', 'Donasi Pendidikan', 'Donasi Kesehatan', 'Donasi Umum'],
    hiddenFields: ['formGroupDonasiPeriode', 'formGroupDonasiJenis'],
    donaturLabel: 'Donatur',
    trend: '+32%',
    icon: 'fa-gift',
    title: 'Rincian Donasi',
    subtitle: 'Breakdown penerimaan donasi umum jemaah bulan ini (bencana, pendidikan, kesehatan, dll).',
    summaryLabel: 'Total Donasi Bulan Ini',
    btnLabel: 'Catat Donasi',
  },
}

const CHART_PALETTE = ['#0f766e', '#2f9e44', '#5aa9a3', '#94a3a1', '#c48a1c', '#7a3fc9', '#c94040', '#2b57c9']

let activeTab = 'ringkasan'
let editingDonasiId = null
let editingPesertaId = null
let editingSetoranId = null
let filteredDonasi = []
let tablePage = 1
const tablePageSize = 10

// data yang lagi "aktif dilihat": semua kalo Ringkasan, atau ke-filter sesuai kategori tab
function getScopedData() {
  const meta = CATEGORY_META[activeTab]
  if (!meta.kategoris) return dataDonasi
  return dataDonasi.filter((d) => meta.kategoris.includes(d.kategori))
}

// ============================================================
// 1. HEADER, SUMMARY, STAT CARDS (beda isi tergantung tab)
// ============================================================
function renderHeaderAndSummary() {
  const meta = CATEGORY_META[activeTab]
  const scoped = getScopedData()
  const total = scoped.reduce((sum, d) => sum + d.nominal, 0)

  const headerTitle = document.querySelector('#ziswafStandardView .page-title')
  const headerSubtitle = document.querySelector('#ziswafStandardView .page-subtitle')
  if (headerTitle) headerTitle.textContent = meta.title
  if (headerSubtitle) headerSubtitle.textContent = meta.subtitle
  document.getElementById('summaryLabel').textContent = meta.summaryLabel
  document.getElementById('totalDonasi').textContent = formatRupiah(total)
  document.getElementById('summaryTrendValue').textContent = meta.trend

  const thJenis = document.getElementById('thJenis')
  if (thJenis) thJenis.textContent = meta.filterLabel || 'Jenis Donasi'
  const thNominal = document.getElementById('thNominal')
  if (thNominal) thNominal.textContent = meta.nominalLabel ? meta.nominalLabel.replace(/ \(Rp\)$/, '') : 'Nominal / Taksiran'
  const thDonatur = document.getElementById('thDonatur')
  if (thDonatur) thDonatur.textContent = meta.donaturLabel || 'Donatur'

  const btn = document.getElementById('btnCatatDonasi')
  if (meta.btnLabel) {
    btn.style.display = ''
    btn.innerHTML = `<i class="fa-solid fa-plus"></i> ${meta.btnLabel}`
  } else {
    btn.style.display = 'none'
  }
}

function renderStatCards() {
  const container = document.getElementById('statCardsRow')
  const scoped = getScopedData()

  let groups
  if (activeTab === 'ringkasan') {
    // Ringkasan: tampilin total per KATEGORI (Infaq/Sodaqoh/Zakat/Wakaf/Donasi)
    groups = groupSum(dataDonasi, 'kategori')
  } else {
    // Tab kategori: tampilin total per JENIS di dalam kategori itu
    groups = groupSum(scoped, 'jenis')
  }

  const totalAll = groups.reduce((s, g) => s + g.total, 0)
  container.innerHTML = ''
  groups.forEach((g) => {
    const percent = totalAll > 0 ? Math.round((g.total / totalAll) * 100) : 0
    container.insertAdjacentHTML(
      'beforeend',
      `
      <button type="button" class="stat-card stat-card-clickable" data-jenis="${esc(g.key)}">
        <div class="stat-card-top">
          <span class="stat-label">${esc(g.key)}</span>
          <span class="stat-badge up">${percent}%</span>
        </div>
        <span class="stat-value">${formatRupiah(g.total)}</span>
        <span class="stat-card-action">${g.count} transaksi <i class="fa-solid fa-arrow-right"></i></span>
      </button>
    `,
    )
  })

  container.querySelectorAll('.stat-card-clickable').forEach((card) => {
    card.addEventListener('click', () => {
      const tabelEl = document.querySelector('.card.table-card')
      const jenisSelect = document.getElementById('jenisFilter')
      const kategoriKlik = card.dataset.jenis
      if (activeTab === 'ringkasan') {
        const tabKey = Object.keys(CATEGORY_META).find((k) =>
          (CATEGORY_META[k].kategoris || []).includes(kategoriKlik),
        )
        if (tabKey) {
          switchTab(tabKey)
          requestAnimationFrame(() => {
            tabelEl?.scrollIntoView({ behavior: 'smooth', block: 'start' })
          })
        }
      } else {
        jenisSelect.value = kategoriKlik
        syncCustomSelects()
        applyFilter()
        showToast(`Tabel difilter ke "${kategoriKlik}"`)
        requestAnimationFrame(() => {
          tabelEl?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        })
      }
    })
  })
}

function groupSum(data, field) {
  const map = {}
  data.forEach((d) => {
    if (!map[d[field]]) map[d[field]] = { key: d[field], total: 0, count: 0 }
    map[d[field]].total += d.nominal
    map[d[field]].count += 1
  })
  return Object.values(map).sort((a, b) => b.total - a.total)
}

// ============================================================
// 2. INSIGHT DONASI (beda isi tergantung tab)
// ============================================================
function renderInsight() {
  const scoped = getScopedData()
  const grid = document.getElementById('insightGrid')
  const total = scoped.reduce((s, d) => s + d.nominal, 0)
  const byJenis = groupSum(scoped, 'jenis')
  const terbesar = byJenis[0] ? byJenis[0].key : '-'
  const rataRata = scoped.length ? Math.round(total / scoped.length) : 0
  const pending = scoped.filter((d) => d.status === 'Pending').length

  document.getElementById('insightCardTitle').textContent =
    activeTab === 'ringkasan' ? 'Insight Donasi' : `Insight ${CATEGORY_META[activeTab].label}`

  grid.innerHTML = `
    <div class="insight-item">
      <div class="insight-icon"><i class="fa-solid fa-chart-line"></i></div>
      <div>
        <div class="insight-text-label">Tren Bulan Ini</div>
        <div class="insight-text-value">Naik ${CATEGORY_META[activeTab].trend.replace('+', '')} dari bulan lalu</div>
      </div>
    </div>
    <div class="insight-item">
      <div class="insight-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
      <div>
        <div class="insight-text-label">${activeTab === 'ringkasan' ? 'Kategori Terbesar' : 'Jenis Terbesar'}</div>
        <div class="insight-text-value">${esc(terbesar)}</div>
      </div>
    </div>
    <div class="insight-item">
      <div class="insight-icon"><i class="fa-solid fa-scale-balanced"></i></div>
      <div>
        <div class="insight-text-label">Rata-rata / Transaksi</div>
        <div class="insight-text-value">${formatRupiah(rataRata)}</div>
      </div>
    </div>
    <div class="insight-item">
      <div class="insight-icon"><i class="fa-solid fa-hourglass-half"></i></div>
      <div>
        <div class="insight-text-label">Transaksi Pending</div>
        <div class="insight-text-value">${pending} dari ${scoped.length} transaksi</div>
      </div>
    </div>
  `
}

// ============================================================
// 3. DOUGHNUT CHART + LEGEND (beda isi tergantung tab)
// ============================================================
let doughnutChartInstance = null

function renderDoughnut() {
  const scoped = getScopedData()
  const field = activeTab === 'ringkasan' ? 'kategori' : 'jenis'
  const groups = groupSum(scoped, field)
  const total = groups.reduce((s, g) => s + g.total, 0)

  document.getElementById('chartCardTitle').textContent =
    activeTab === 'ringkasan'
      ? 'Komposisi Donasi (Semua Kategori)'
      : `Komposisi Jenis ${CATEGORY_META[activeTab].label}`

  const legendEl = document.getElementById('doughnutLegend')
  legendEl.innerHTML = ''
  groups.forEach((g, i) => {
    const percent = total > 0 ? ((g.total / total) * 100).toFixed(0) : 0
    const color = CHART_PALETTE[i % CHART_PALETTE.length]
    legendEl.insertAdjacentHTML(
      'beforeend',
      `
      <div class="doughnut-legend-item">
        <div class="doughnut-legend-left">
          <span class="doughnut-legend-dot" style="background:${color}"></span>
          ${esc(g.key)}
        </div>
        <span class="doughnut-legend-percent">${percent}%</span>
      </div>
    `,
    )
  })

  const ctx = document.getElementById('donasiDoughnutChart')
  if (doughnutChartInstance) doughnutChartInstance.destroy()
  if (ctx) {
    doughnutChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: groups.map((g) => g.key),
        datasets: [
          {
            data: groups.map((g) => g.total),
            backgroundColor: groups.map((_, i) => CHART_PALETTE[i % CHART_PALETTE.length]),
            borderWidth: 2,
            borderColor: '#ffffff',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { display: false } },
      },
    })
  }
}

// ============================================================
// 4. DONASI TERBARU
// ============================================================
function renderDonorList() {
  const scoped = getScopedData()
  const list = document.getElementById('donorList')
  document.getElementById('donorListTitle').textContent =
    activeTab === 'ringkasan' ? 'Donasi Terbaru' : `${CATEGORY_META[activeTab].label} Terbaru`

  const sorted = [...scoped].sort((a, b) => parseTanggal(b.tanggal) - parseTanggal(a.tanggal)).slice(0, 5)

  list.innerHTML = ''
  if (sorted.length === 0) {
    list.innerHTML = `<li style="color:var(--text-muted); font-size:13px;">Belum ada data.</li>`
    return
  }
  sorted.forEach((item) => {
    list.insertAdjacentHTML(
      'beforeend',
      `
      <li class="donor-item">
        <div class="donor-avatar"><i class="fa-solid fa-user"></i></div>
        <div class="donor-info">
          <div class="donor-name">${esc(item.donatur)}</div>
          <div class="donor-meta">${esc(item.jenis)}</div>
        </div>
        <div class="donor-right">
          <div class="donor-amount">${formatRupiah(item.nominal)}</div>
          <div class="donor-time">${esc(item.tanggal)}</div>
        </div>
      </li>
    `,
    )
  })
}

function parseTanggal(str) {
  const [d, m, y] = str.split('/').map(Number)
  return new Date(y < 100 ? 2000 + y : y, m - 1, d)
}

function toDateInputValue(str) {
  if (!str) return ''
  const [d, m, y] = str.split('/').map(Number)
  return `${y < 100 ? 2000 + y : y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`
}

function formatPeriode(item) {
  if (item.tanggal_akhir && item.tanggal_akhir !== item.tanggal) {
    return `${item.tanggal} - ${item.tanggal_akhir}`
  }
  return item.tanggal
}

// ============================================================
// 5. FILTER DROPDOWN "Jenis Donasi"
// ============================================================
function renderJenisFilterOptions() {
  const select = document.getElementById('jenisFilter')
  const label = document.getElementById('jenisFilterLabel')
  const meta = CATEGORY_META[activeTab]
  const scoped = getScopedData()

  const filterLabel = meta.filterLabel || 'Jenis Donasi'
  const filterOptions = meta.filterOptions || [...new Set(scoped.map((d) => d.jenis))]

  if (label) label.textContent = filterLabel
  select.innerHTML =
    `<option value="">Semua Jenis</option>` +
    filterOptions.map((j) => `<option value="${j}">${j}</option>`).join('')

  syncCustomSelects()
}

// ============================================================
// 6. TABEL DONASI + FILTER
// ============================================================
function renderTable(allData) {
  const tbody = document.getElementById('donasiTableBody')
  tbody.innerHTML = ''

  const totalPages = Math.max(1, Math.ceil(allData.length / tablePageSize))
  if (tablePage > totalPages) tablePage = totalPages

  const start = (tablePage - 1) * tablePageSize
  const data = allData.slice(start, start + tablePageSize)

  if (allData.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:var(--text-muted);">Data tidak ditemukan</td></tr>`
    renderPager(allData.length, tablePage, totalPages)
    return
  }

  data.forEach((item) => {
    const statusClass = item.status === 'Berhasil' ? 'status-berhasil' : 'status-pending'
    tbody.insertAdjacentHTML(
      'beforeend',
      `
      <tr>
        <td>${esc(item.donatur)}</td>
        <td>${esc(item.kategori)}</td>
        <td>${esc(item.jenis)}</td>
        <td>${formatRupiah(item.nominal)}</td>
        <td><span class="status-badge ${statusClass}">${esc(item.status)}</span></td>
        <td>
          <div class="donasi-actions">
            <button class="icon-action-btn btn-view-donasi" title="Lihat Detail"><i class="fa-regular fa-eye"></i></button>
            <button class="icon-action-btn edit btn-edit-donasi" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <button class="icon-action-btn hapus btn-hapus-donasi" title="Hapus"><i class="fa-solid fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `,
    )
  })

  tbody
    .querySelectorAll('.btn-view-donasi')
    .forEach((btn, i) => btn.addEventListener('click', () => openDetailTransaksi(data[i])))
  tbody
    .querySelectorAll('.btn-edit-donasi')
    .forEach((btn, i) => btn.addEventListener('click', () => openDonasiModal(data[i])))

  tbody.querySelectorAll('.btn-hapus-donasi').forEach((btn, i) => {
    btn.addEventListener('click', () => {
      const item = data[i]
      openConfirmDelete({
        title: 'Hapus Transaksi?',
        message: `Yakin ingin menghapus transaksi ${item.donatur} (${formatRupiah(item.nominal)})?`,
        onConfirm: async () => {
          const csrf = document.querySelector('meta[name="csrf-token"]').content
          try {
            const res = await fetch(`/keuangan/infaq-sodaqoh/donasi/${item.id}`, {
              method: 'DELETE',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            })
            if (!res.ok) throw new Error('gagal hapus')

            const idx = dataDonasi.findIndex((d) => d.id === item.id)
            if (idx > -1) dataDonasi.splice(idx, 1)
            switchTab(activeTab)
            showToast(`Transaksi ${item.donatur} dihapus.`, 'fa-solid fa-trash')
          } catch (err) {
            showToast('Gagal menghapus transaksi.', 'fa-solid fa-triangle-exclamation')
            throw err
          }
        },
      })
    })
  })

  renderPager(allData.length, tablePage, totalPages)
}

function renderPager(total, page, totalPages) {
  const pager = document.getElementById('tablePager')
  if (!pager) return
  if (total === 0) {
    pager.innerHTML = ''
    return
  }
  pager.innerHTML = `
    <span class="table-pager-info">${total} data &middot; halaman ${page}/${totalPages}</span>
    <div class="table-pager-buttons">
      <button class="btn-outline btn-sm-outline" id="pagerPrev" ${page <= 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i> Sebelumnya</button>
      <button class="btn-outline btn-sm-outline" id="pagerNext" ${page >= totalPages ? 'disabled' : ''}>Berikutnya <i class="fa-solid fa-chevron-right"></i></button>
    </div>
  `
  document.getElementById('pagerPrev').addEventListener('click', () => {
    if (tablePage > 1) {
      tablePage--
      renderTable(filteredDonasi)
    }
  })
  document.getElementById('pagerNext').addEventListener('click', () => {
    if (tablePage < totalPages) {
      tablePage++
      renderTable(filteredDonasi)
    }
  })
}

function bulanKey(str) {
  if (!str) return ''
  const [d, m, y] = str.split('/').map(Number)
  return `${y < 100 ? 2000 + y : y}-${String(m).padStart(2, '0')}`
}

function renderPeriodeFilterOptions() {
  const select = document.getElementById('periodeFilter')
  const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
  const months = [...new Set(dataDonasi.map((d) => bulanKey(d.tanggal)))]
    .filter(Boolean)
    .sort((a, b) => (a < b ? 1 : -1))
  select.innerHTML =
    `<option value="">Semua Periode</option>` +
    months
      .map((m) => {
        const [y, mo] = m.split('-').map(Number)
        return `<option value="${m}">${bulan[mo - 1]} ${y}</option>`
      })
      .join('')
  syncCustomSelects()
}

// ---- modal detail transaksi (isinya termasuk Keterangan lengkap) ----
function openDetailTransaksi(item) {
  document.getElementById('dtDonatur').textContent = item.donatur
  document.getElementById('dtTanggal').textContent = formatPeriode(item)
  document.getElementById('dtKategori').textContent = item.kategori
  document.getElementById('dtJenis').textContent = item.jenis
  document.getElementById('dtTipe').textContent = item.tipe
  document.getElementById('dtNominal').textContent = formatRupiah(item.nominal)
  document.getElementById('dtMetode').textContent = item.metode
  document.getElementById('dtPetugas').textContent = item.petugas
  document.getElementById('dtStatus').textContent = item.status
  document.getElementById('dtKeterangan').textContent =
    item.keterangan && item.keterangan !== '-' ? item.keterangan : 'Tidak ada keterangan tambahan.'
  document.getElementById('detailTransaksiModalOverlay').classList.add('active')
}
function closeDetailTransaksi() {
  document.getElementById('detailTransaksiModalOverlay').classList.remove('active')
}

function applyFilter() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const jenis = document.getElementById('jenisFilter').value
  const metode = document.getElementById('metodeFilter').value
  const periode = document.getElementById('periodeFilter').value

  const scoped = getScopedData()
  filteredDonasi = scoped.filter((item) => {
    const matchSearch = item.donatur.toLowerCase().includes(keyword)
    const matchJenis = jenis === '' || item.jenis === jenis
    const matchMetode = metode === '' || item.metode === metode
    const matchPeriode = periode === '' || bulanKey(item.tanggal) === periode
    return matchSearch && matchJenis && matchMetode && matchPeriode
  })

  tablePage = 1
  renderTable(filteredDonasi)
}

// ---- rekap penerimaan per kategori (tab Ringkasan) ----
function renderRekapKategori() {
  const card = document.getElementById('rekapKategoriCard')
  if (!card) return

  const isRingkasan = activeTab === 'ringkasan'
  card.hidden = !isRingkasan
  if (!isRingkasan) return

  const meta = CATEGORY_META[activeTab]
  const kategoriList = meta.kategoris ? [...meta.kategoris] : [...new Set(dataDonasi.map((d) => d.kategori))]
  const rec = {}
  let grandTotal = 0
  let grandCount = 0

  dataDonasi.forEach((d) => {
    if (!kategoriList.includes(d.kategori)) return
    if (!rec[d.kategori]) rec[d.kategori] = { total: 0, count: 0 }
    const nominal = d.tipe === 'Barang' ? 0 : d.nominal
    rec[d.kategori].total += nominal
    rec[d.kategori].count++
    grandTotal += nominal
    grandCount++
  })

  const head = document.getElementById('rekapKategoriHead')
  const body = document.getElementById('rekapKategoriBody')

  if (kategoriList.length === 0 || grandCount === 0) {
    card.hidden = true
    return
  }

  head.innerHTML = `<tr><th>Kategori</th><th>Jumlah Transaksi</th><th>Total Nominal</th><th>Kontribusi</th></tr>`
  body.innerHTML = kategoriList
    .map((k) => {
      const r = rec[k]
      if (!r || r.count === 0) return ''
      const persen = grandTotal > 0 ? ((r.total / grandTotal) * 100).toFixed(1) : '0.0'
      return `
        <tr>
          <td>${esc(k)}</td>
          <td>${r.count} transaksi</td>
          <td><strong>${formatRupiah(r.total)}</strong></td>
          <td>
            <div class="rekap-persen-bar">
              <div class="rekap-persen-fill" style="width:${persen}%"></div>
              <span>${persen}%</span>
            </div>
          </td>
        </tr>`
    })
    .join('')
  body.insertAdjacentHTML(
    'beforeend',
    `<tr class="rekap-grand">
      <td><strong>Total</strong></td>
      <td><strong>${grandCount} transaksi</strong></td>
      <td><strong>${formatRupiah(grandTotal)}</strong></td>
      <td></td>
    </tr>`,
  )
}

// ---- ekspor CSV dari hasil filter aktif ----
function exportCSV() {
  if (!filteredDonasi.length) {
    showToast('Tidak ada data untuk diekspor.', 'error')
    return
  }

  const headers = ['Tanggal', 'Donatur', 'Kategori', 'Jenis', 'Tipe', 'Nominal', 'Metode', 'Petugas', 'Status', 'Keterangan']
  const rows = filteredDonasi.map((d) => [
    d.tanggal,
    d.donatur,
    d.kategori,
    d.jenis,
    d.tipe,
    d.tipe === 'Barang' ? `Barang (${d.keterangan || '-'})` : d.nominal,
    d.metode,
    d.petugas,
    d.status,
    d.keterangan || '-',
  ])

  const csv = [headers, ...rows]
    .map((row) =>
      row.map((cell) => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(','),
    )
    .join('\n')

  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `infaq-sodaqoh-${activeTab}-${new Date().toISOString().slice(0, 10)}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
  showToast(`Ekspor berhasil (${filteredDonasi.length} baris).`)
}

// ============================================================
// 7. GANTI TAB -- cabang ke view Qurban kalo tabnya "qurban"
// ============================================================
function switchTab(tabKey) {
  activeTab = tabKey

  document.querySelectorAll('.ziswaf-tab').forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.tab === tabKey)
  })

  const standardView = document.getElementById('ziswafStandardView')
  const qurbanView = document.getElementById('qurbanView')

  if (tabKey === 'qurban') {
    standardView.hidden = true
    qurbanView.hidden = false
    renderQurbanView()
    return
  }

  qurbanView.hidden = true
  standardView.hidden = false

  renderHeaderAndSummary()
  renderStatCards()
  renderInsight()
  renderDoughnut()
  renderDonorList()
  renderJenisFilterOptions()
  renderPeriodeFilterOptions()
  renderRekapKategori()

  document.getElementById('searchInput').value = ''
  document.getElementById('metodeFilter').value = ''
  syncCustomSelects()
  applyFilter()
}

// ============================================================================
// 8. TABUNGAN QURBAN -- data & view TERPISAH dari dataDonasi (bentuknya beda:
//    progres per peserta + riwayat cicilan, bukan transaksi flat)
// ============================================================================
const dataTabunganQurban = window.__QURBAN_DATA__ || []
const dataJamaahQurban = window.__JAMAAH_OPTIONS__ || []

let activeSetoranPesertaId = null
let expandedPesertaId = null

let pesertaMemberRows = []
let memberModalPesertaId = null

function totalTerkumpul(peserta) {
  return peserta.riwayat.reduce((s, r) => s + r.jumlah, 0)
}

function formatTanggalIndo(iso) {
  const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
  const [y, m, d] = iso.split('-').map(Number)
  return `${d} ${bulan[m - 1]} ${y}`
}

function renderQurbanView() {
  const totalTarget = dataTabunganQurban.reduce((s, p) => s + p.target, 0)
  const totalKekumpul = dataTabunganQurban.reduce((s, p) => s + totalTerkumpul(p), 0)
  const pesertaLunas = dataTabunganQurban.filter((p) => totalTerkumpul(p) >= p.target).length
  const pesertaAktif = dataTabunganQurban.length - pesertaLunas
  const totalSetoran = dataTabunganQurban.reduce((s, p) => s + p.riwayat.length, 0)
  const overallPersen = totalTarget > 0 ? Math.round((totalKekumpul / totalTarget) * 100) : 0
  const totalSisa = Math.max(0, totalTarget - totalKekumpul)

  document.getElementById('qurbanStatCards').innerHTML = `
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Total Terkumpul</span></div>
      <span class="stat-value">${formatRupiah(totalKekumpul)}</span>
      <div class="stat-card-sub">Sisa target ${formatRupiah(totalSisa)}</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Progres Keseluruhan</span></div>
      <span class="stat-value" style="color:var(--color-teal)">${overallPersen}%</span>
      <div class="stat-card-progress">
        <div class="stat-card-progress-fill" style="width:${overallPersen}%"></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Peserta Aktif Nabung</span></div>
      <span class="stat-value" style="color:var(--color-teal)">${pesertaAktif} Orang</span>
      <div class="stat-card-sub">Masih menabung</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Peserta Lunas</span></div>
      <span class="stat-value" style="color:var(--color-green)">${pesertaLunas} Orang</span>
      <div class="stat-card-sub">Target tercapai</div>
    </div>
  `

  const totalSetoranEl = document.getElementById('qurbanTotalSetoran')
  if (totalSetoranEl) {
    totalSetoranEl.textContent = dataTabunganQurban.length
      ? `Total ${totalSetoran} setoran dari ${dataTabunganQurban.length} peserta`
      : ''
  }

  renderPesertaList()
}

function renderPesertaList() {
  const container = document.getElementById('qurbanPesertaList')
  container.innerHTML = ''

  if (dataTabunganQurban.length === 0) {
    container.innerHTML = `<p style="color:var(--text-muted); font-size:13px;">Belum ada peserta tabungan qurban.</p>`
    return
  }

  dataTabunganQurban.forEach((peserta) => {
    const kekumpul = totalTerkumpul(peserta)
    const persen = Math.min(100, Math.round((kekumpul / peserta.target) * 100))
    const sisa = Math.max(0, peserta.target - kekumpul)
    const lunas = kekumpul >= peserta.target
    const isExpanded = expandedPesertaId === peserta.id
    const patungan = !!peserta.is_patungan
    const setoranTerakhir = [...peserta.riwayat]
      .sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal))[0]
    const terakhirText = setoranTerakhir ? formatTanggalIndo(setoranTerakhir.tanggal) : 'Belum ada'
    const paketText = patungan
      ? `Patungan Sapi &middot; mulai ${formatTanggalIndo(peserta.mulai)}`
      : `${esc(peserta.paket)} &middot; mulai ${formatTanggalIndo(peserta.mulai)}`
    const memberChips = patungan
      ? `
      <div class="qurban-member-chips">
        ${peserta.members.map(
          (m) => `
          <span class="qurban-member-chip">
            <i class="fa-solid fa-user"></i> ${esc(m.nama)}
            <button type="button" class="qurban-chip-hapus" data-peserta-id="${peserta.id}" data-member-id="${m.id}" title="Hapus anggota"><i class="fa-solid fa-xmark"></i></button>
          </span>
        `,
        ).join('')}
        ${peserta.members.length < 7
          ? `<button type="button" class="qurban-chip-tambah" data-id="${peserta.id}"><i class="fa-solid fa-plus"></i> Tambah Anggota (${peserta.members.length}/7)</button>`
          : ''}
      </div>
    `
      : ''

    container.insertAdjacentHTML(
      'beforeend',
      `
      <div class="qurban-peserta-card">
        <div class="qurban-peserta-top">
          <div class="qurban-peserta-info">
            <div class="qurban-peserta-avatar"><i class="fa-solid ${patungan ? 'fa-cow' : 'fa-user'}"></i></div>
            <div>
              <div class="qurban-peserta-nama">${patungan ? `Patungan Sapi` : esc(peserta.nama)}</div>
              <div class="qurban-peserta-paket">${paketText}</div>
              <div class="qurban-peserta-meta">
                ${patungan ? `<span class="qurban-meta-item"><i class="fa-solid fa-users"></i> ${peserta.members.length}/7 anggota</span>` : ''}
                <span class="qurban-meta-item"><i class="fa-solid fa-coins"></i> ${peserta.riwayat.length}&times; setoran</span>
                <span class="qurban-meta-item"><i class="fa-solid fa-calendar-check"></i> Terakhir: ${terakhirText}</span>
              </div>
            </div>
          </div>
          <div class="qurban-peserta-right">
            <span class="qurban-status-badge ${lunas ? 'lunas' : 'aktif'}">${lunas ? 'Lunas' : 'Masih Nabung'}</span>
            <span class="qurban-persen-pill ${lunas ? 'lunas' : ''}">${persen}%</span>
            <div class="qurban-peserta-ico-group">
              <button type="button" class="qurban-btn-edit-peserta" data-id="${peserta.id}" title="Ubah peserta">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button type="button" class="qurban-btn-hapus-peserta" data-id="${peserta.id}" title="Hapus peserta">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        </div>

        ${memberChips}

        <div class="qurban-progress-track">
          <div class="qurban-progress-fill" style="width:${persen}%"></div>
        </div>
        <div class="qurban-progress-info">
          <span><strong>${formatRupiah(kekumpul)}</strong> / ${formatRupiah(peserta.target)}</span>
          <span>sisa ${formatRupiah(sisa)}</span>
        </div>

        <div class="qurban-peserta-actions">
          <button type="button" class="btn-outline btn-sm-outline btn-catat-setoran" data-id="${peserta.id}" ${lunas ? 'disabled' : ''}>
            <i class="fa-solid fa-coins"></i> Catat Setoran
          </button>
          <button type="button" class="btn-outline btn-sm-outline btn-toggle-riwayat" data-id="${peserta.id}">
            <i class="fa-solid fa-clock-rotate-left"></i> ${isExpanded ? 'Sembunyikan Riwayat' : `Riwayat (${peserta.riwayat.length})`}
          </button>
        </div>

        ${isExpanded ? renderRiwayatSetoran(peserta) : ''}
      </div>
    `,
    )
  })

  container.querySelectorAll('.btn-catat-setoran').forEach((btn) => {
    btn.addEventListener('click', () => openSetoranModal(Number(btn.dataset.id)))
  })
  container.querySelectorAll('.btn-toggle-riwayat').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id)
      expandedPesertaId = expandedPesertaId === id ? null : id
      renderPesertaList()
    })
  })

  container.querySelectorAll('.btn-edit-setoran').forEach((btn) => {
    btn.addEventListener('click', () => {
      const pesertaId = Number(btn.dataset.pesertaId)
      const setoranId = Number(btn.dataset.id)
      const peserta = dataTabunganQurban.find((p) => p.id === pesertaId)
      const setoran = peserta?.riwayat.find((r) => r.id === setoranId)
      if (!peserta || !setoran) return
      openSetoranModal(pesertaId, setoran)
    })
  })

  container.querySelectorAll('.btn-hapus-setoran').forEach((btn) => {
    btn.addEventListener('click', () => {
      const pesertaId = Number(btn.dataset.pesertaId)
      const setoranId = Number(btn.dataset.id)
      const peserta = dataTabunganQurban.find((p) => p.id === pesertaId)
      const setoran = peserta?.riwayat.find((r) => r.id === setoranId)
      if (!peserta || !setoran) return

      openConfirmDelete({
        title: 'Hapus Setoran?',
        message: `Yakin ingin menghapus setoran ${formatRupiah(setoran.jumlah)} (${formatTanggalIndo(setoran.tanggal)}) dari ${peserta.nama}?`,
        onConfirm: async () => {
          const csrf = document.querySelector('meta[name="csrf-token"]').content
          try {
            const res = await fetch(`/keuangan/infaq-sodaqoh/setoran/${setoranId}`, {
              method: 'DELETE',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            })
            if (!res.ok) throw new Error('gagal hapus setoran')

            const p = dataTabunganQurban.find((x) => x.id === pesertaId)
            if (p) {
              const idx = p.riwayat.findIndex((r) => r.id === setoranId)
              if (idx > -1) p.riwayat.splice(idx, 1)
            }
            renderQurbanView()
            showToast('Setoran dihapus.', 'fa-solid fa-trash')
          } catch (err) {
            showToast('Gagal menghapus setoran.', 'fa-solid fa-triangle-exclamation')
            throw err
          }
        },
      })
    })
  })

  container.querySelectorAll('.qurban-chip-tambah').forEach((btn) => {
    btn.addEventListener('click', () => openMemberModal(Number(btn.dataset.id)))
  })

  container.querySelectorAll('.qurban-chip-hapus').forEach((btn) => {
    btn.addEventListener('click', () => {
      const pesertaId = Number(btn.dataset.pesertaId)
      const memberId = Number(btn.dataset.memberId)
      const peserta = dataTabunganQurban.find((p) => p.id === pesertaId)
      const member = peserta?.members.find((m) => m.id === memberId)
      if (!peserta || !member) return

      openConfirmDelete({
        title: 'Hapus Anggota?',
        message: `Yakin ingin menghapus ${member.nama} dari patungan ini?`,
        onConfirm: async () => {
          const csrf = document.querySelector('meta[name="csrf-token"]').content
          try {
            const res = await fetch(`/keuangan/infaq-sodaqoh/anggota/${memberId}`, {
              method: 'DELETE',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            })
            if (!res.ok) throw new Error('gagal hapus anggota')

            const p = dataTabunganQurban.find((x) => x.id === pesertaId)
            if (p) {
              const idx = p.members.findIndex((m) => m.id === memberId)
              if (idx > -1) p.members.splice(idx, 1)
            }
            renderQurbanView()
            showToast('Anggota dihapus.', 'fa-solid fa-trash')
          } catch (err) {
            showToast('Gagal menghapus anggota.', 'fa-solid fa-triangle-exclamation')
            throw err
          }
        },
      })
    })
  })

  container.querySelectorAll('.qurban-btn-edit-peserta').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id)
      const peserta = dataTabunganQurban.find((p) => p.id === id)
      if (!peserta) return
      openPesertaModal(peserta)
    })
  })

  container.querySelectorAll('.qurban-btn-hapus-peserta').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id)
      const peserta = dataTabunganQurban.find((p) => p.id === id)
      if (!peserta) return

      openConfirmDelete({
        title: 'Hapus Peserta?',
        message: `Yakin ingin menghapus ${peserta.nama}? Seluruh setoran & anggota patungannya ikut terhapus.`,
        onConfirm: async () => {
          const csrf = document.querySelector('meta[name="csrf-token"]').content
          try {
            const res = await fetch(`/keuangan/infaq-sodaqoh/peserta/${id}`, {
              method: 'DELETE',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            })
            if (!res.ok) throw new Error('gagal hapus peserta')

            const idx = dataTabunganQurban.findIndex((p) => p.id === id)
            if (idx > -1) dataTabunganQurban.splice(idx, 1)
            expandedPesertaId = null
            renderQurbanView()
            showToast('Peserta dihapus.', 'fa-solid fa-trash')
          } catch (err) {
            showToast('Gagal menghapus peserta.', 'fa-solid fa-triangle-exclamation')
            throw err
          }
        },
      })
    })
  })
}

function renderRiwayatSetoran(peserta) {
  const sorted = [...peserta.riwayat].sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal))
  const kekumpul = totalTerkumpul(peserta)
  const rows = sorted
    .map(
      (r) => `
      <tr>
        <td>${formatTanggalIndo(r.tanggal)}</td>
        <td>${esc(r.member_nama || (peserta.is_patungan ? '-' : peserta.nama))}</td>
        <td>${formatRupiah(r.jumlah)}</td>
        <td>${esc(r.metode || '-')}</td>
        <td>${esc(r.petugas)}</td>
        <td class="qurban-riwayat-aksi">
          <button type="button" class="btn-edit-setoran" data-peserta-id="${peserta.id}" data-id="${r.id}" title="Ubah setoran">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button type="button" class="btn-hapus-setoran" data-peserta-id="${peserta.id}" data-id="${r.id}" title="Hapus setoran">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>
    `,
    )
    .join('')

  const body = rows || `<tr><td colspan="6" class="qurban-riwayat-empty">Belum ada setoran.</td></tr>`

  const memberRecap = peserta.is_patungan
    ? `
    <div class="qurban-rekap-anggota">
      ${peserta.members.map((m) => {
        const total = peserta.riwayat
          .filter((r) => r.member_id === m.id)
          .reduce((s, r) => s + r.jumlah, 0)
        const sudah = total > 0
        return `
        <div class="qurban-rekap-item ${sudah ? 'sudah' : 'belum'}">
          <i class="fa-solid ${sudah ? 'fa-circle-check' : 'fa-circle-minus'}"></i>
          <span class="qurban-rekap-nama">${esc(m.nama)}</span>
          <span class="qurban-rekap-jumlah">${sudah ? formatRupiah(total) : 'Belum setor'}</span>
        </div>
      `
      }).join('')}
    </div>
  `
    : ''

  return `
    <div class="qurban-riwayat-box">
      <div class="qurban-riwayat-summary">
        <span><strong>${formatRupiah(kekumpul)}</strong> terkumpul</span>
        <span>${peserta.riwayat.length}&times; setoran</span>
        <span class="qurban-riwayat-sisa">sisa ${formatRupiah(Math.max(0, peserta.target - kekumpul))}</span>
      </div>
      ${memberRecap}
      <table class="qurban-riwayat-table">
        <thead>
          <tr><th>Tanggal</th><th>Penyetor</th><th>Jumlah</th><th>Metode</th><th>Dicatat oleh</th><th></th></tr>
        </thead>
        <tbody>${body}</tbody>
      </table>
    </div>
  `
}

// ---- modal tambah peserta ----
function renderMemberList() {
  const list = document.getElementById('memberList')
  list.innerHTML = pesertaMemberRows
    .map(
      (row, i) => `
      <div class="qurban-member-row">
        <input
          class="form-input"
          type="text"
          list="datalistJamaah"
          placeholder="Nama anggota (ketik / pilih dari Data Jemaah)"
          data-row="${i}"
          value="${esc(row.nama)}"
        >
        <button type="button" class="qurban-member-remove" data-row="${i}" title="Hapus baris"><i class="fa-solid fa-xmark"></i></button>
      </div>
    `,
    )
    .join('')

  list.querySelectorAll('input[data-row]').forEach((input) => {
    input.addEventListener('input', () => {
      pesertaMemberRows[Number(input.dataset.row)].nama = input.value
      updateMemberCount()
    })
  })
  list.querySelectorAll('.qurban-member-remove').forEach((btn) => {
    btn.addEventListener('click', () => {
      pesertaMemberRows.splice(Number(btn.dataset.row), 1)
      renderMemberList()
    })
  })
}

function updateMemberCount() {
  const filled = pesertaMemberRows.filter((r) => r.nama.trim()).length
  const el = document.getElementById('memberCount')
  if (el) el.textContent = `${filled}/7`
  const btn = document.getElementById('btnTambahMember')
  if (btn) btn.disabled = pesertaMemberRows.length >= 7
}

function isPatunganPaket() {
  return document.getElementById('inputPesertaPaket').value === 'Patungan Sapi'
}

function syncPesertaFormMode() {
  const patungan = isPatunganPaket()
  document.getElementById('formGroupPesertaNama').style.display = patungan ? 'none' : ''
  document.getElementById('memberSection').hidden = !patungan
  const prefix = editingPesertaId ? 'Ubah' : 'Tambah'
  document.getElementById('pesertaModalTitle').textContent = patungan
    ? `${prefix} Patungan Sapi`
    : `${prefix} Peserta Tabungan Qurban`
}

function openPesertaModal(item = null) {
  editingPesertaId = item ? item.id : null
  document.getElementById('inputPesertaPaket').value = item ? item.paket : 'Patungan Sapi'
  document.getElementById('inputPesertaNama').value = item && !item.is_patungan ? item.nama : ''
  document.getElementById('inputPesertaTarget').value = item ? item.target : ''
  document.getElementById('inputPesertaMulai').value = item
    ? item.mulai
    : new Date().toISOString().slice(0, 10)
  pesertaMemberRows = item?.is_patungan
    ? item.members.map((m) => ({ nama: m.nama, jamaah_id: m.jamaah_id }))
    : [{ nama: '' }]
  renderMemberList()
  syncPesertaFormMode()
  document.getElementById('pesertaModalOverlay').classList.add('active')
  syncCustomSelects()
}
function closePesertaModal() {
  editingPesertaId = null
  document.getElementById('pesertaModalOverlay').classList.remove('active')
}

async function savePeserta() {
  const paket = document.getElementById('inputPesertaPaket').value
  const target = Number(document.getElementById('inputPesertaTarget').value)
  const patungan = paket === 'Patungan Sapi'

  let payload
  if (patungan) {
    const members = pesertaMemberRows.map((r) => r.nama.trim()).filter(Boolean).map((nama) => {
      const matched = dataJamaahQurban.find((j) => j.nama.toLowerCase() === nama.toLowerCase())
      return { nama, jamaah_id: matched ? matched.id : null }
    })
    if (members.length < 1) {
      showToast('Isi minimal 1 anggota patungan.')
      return
    }
    payload = { nama: 'Patungan Sapi', paket, target, mulai: inputPesertaMulai(), members }
  } else {
    const nama = document.getElementById('inputPesertaNama').value.trim()
    if (!nama || !target) {
      showToast('Isi nama sama target dulu ya.')
      return
    }
    payload = { nama, paket, target, mulai: inputPesertaMulai() }
  }

  if (!target) {
    showToast('Isi target dulu ya.')
    return
  }

  try {
    const url = editingPesertaId
      ? `/keuangan/infaq-sodaqoh/peserta/${editingPesertaId}`
      : '/keuangan/infaq-sodaqoh/peserta'
    const res = await fetch(url, {
      method: editingPesertaId ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(payload),
    })
    if (!res.ok) {
      const errData = await res.json()
      const firstError = Object.values(errData.errors || {})[0]?.[0] || 'Gagal menyimpan peserta.'
      showToast(firstError)
      return
    }
    const newPeserta = await res.json()
    if (editingPesertaId) {
      const idx = dataTabunganQurban.findIndex((p) => p.id === editingPesertaId)
      if (idx > -1) dataTabunganQurban[idx] = newPeserta
    } else {
      dataTabunganQurban.push(newPeserta)
    }
    const wasEditing = !!editingPesertaId
    editingPesertaId = null
    closePesertaModal()
    renderQurbanView()
    showToast(wasEditing ? 'Peserta tabungan qurban diperbarui.' : 'Peserta tabungan qurban ditambahkan.')
  } catch (err) {
    showToast('Gagal menyimpan peserta, coba lagi.')
  }
}

function inputPesertaMulai() {
  return document.getElementById('inputPesertaMulai').value || new Date().toISOString().slice(0, 10)
}

// ---- modal tambah anggota patungan (isi slot sampai 7/7) ----
function openMemberModal(pesertaId) {
  const peserta = dataTabunganQurban.find((p) => p.id === pesertaId)
  if (!peserta) return
  if (peserta.members.length >= 7) {
    showToast('Anggota patungan sudah penuh (7/7).')
    return
  }
  memberModalPesertaId = pesertaId
  document.getElementById('inputMemberNama').value = ''
  document.getElementById('memberModalOverlay').classList.add('active')
  syncCustomSelects()
}
function closeMemberModal() {
  memberModalPesertaId = null
  document.getElementById('memberModalOverlay').classList.remove('active')
}

async function saveMember() {
  const nama = document.getElementById('inputMemberNama').value.trim()
  if (!nama) {
    showToast('Isi nama anggota dulu ya.')
    return
  }
  const matched = dataJamaahQurban.find((j) => j.nama.toLowerCase() === nama.toLowerCase())
  const payload = { nama, jamaah_id: matched ? matched.id : null }

  try {
    const res = await fetch(`/keuangan/infaq-sodaqoh/peserta/${memberModalPesertaId}/anggota`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(payload),
    })
    if (!res.ok) {
      const errData = await res.json()
      const firstError = Object.values(errData.errors || {})[0]?.[0] || 'Gagal menambah anggota.'
      showToast(firstError)
      return
    }
    const updated = await res.json()
    const idx = dataTabunganQurban.findIndex((p) => p.id === updated.id)
    if (idx > -1) dataTabunganQurban[idx] = updated
    closeMemberModal()
    renderQurbanView()
    showToast(`Anggota ${nama} ditambahkan.`)
  } catch (err) {
    showToast('Gagal menambah anggota, coba lagi.')
  }
}

// ---- modal catat setoran ----
function openSetoranModal(pesertaId, setoran = null) {
  editingSetoranId = setoran ? setoran.id : null
  activeSetoranPesertaId = pesertaId
  const peserta = dataTabunganQurban.find((p) => p.id === pesertaId)
  document.getElementById('setoranPesertaNama').textContent = peserta.nama
  document.getElementById('setoranModalTitle').textContent = editingSetoranId
    ? 'Ubah Setoran'
    : 'Catat Setoran'

  const memberGroup = document.getElementById('formGroupSetoranMember')
  const memberSelect = document.getElementById('inputSetoranMember')
  if (peserta.is_patungan) {
    memberSelect.innerHTML = `
      <option value="">Pilih penyetor</option>
      ${peserta.members.map((m) => `<option value="${m.id}">${esc(m.nama)}</option>`).join('')}
    `.trim()
    if (editingSetoranId) {
      const oldMemberId = setoran.member_id || ''
      memberSelect.value = String(oldMemberId)
    }
    memberGroup.style.display = ''
  } else {
    memberSelect.innerHTML = ''
    memberGroup.style.display = 'none'
  }

  document.getElementById('inputSetoranJumlah').value = editingSetoranId ? setoran.jumlah : ''
  document.getElementById('inputSetoranTanggal').value = editingSetoranId
    ? setoran.tanggal
    : new Date().toISOString().slice(0, 10)
  document.getElementById('inputSetoranMetode').value = editingSetoranId ? setoran.metode || 'Tunai' : 'Tunai'
  document.getElementById('inputSetoranPetugas').selectedIndex = 0
  if (editingSetoranId) {
    const petugasSelect = document.getElementById('inputSetoranPetugas')
    const opt = [...petugasSelect.options].find((o) => o.text === setoran.petugas)
    if (opt) petugasSelect.value = opt.value
  }
  document.getElementById('setoranModalOverlay').classList.add('active')
  syncCustomSelects()
}
function closeSetoranModal() {
  editingSetoranId = null
  document.getElementById('setoranModalOverlay').classList.remove('active')
}

async function saveSetoran() {
  const jumlah = Number(document.getElementById('inputSetoranJumlah').value)
  if (!jumlah || jumlah <= 0) {
    showToast('Isi jumlah setoran dulu ya.')
    return
  }

  const peserta = dataTabunganQurban.find((p) => p.id === activeSetoranPesertaId)
  const memberId = peserta?.is_patungan
    ? Number(document.getElementById('inputSetoranMember').value) || null
    : null
  if (peserta?.is_patungan && !memberId) {
    showToast('Pilih dulu siapa yang setor (anggota patungan).')
    return
  }

  const payload = {
    qurban_peserta_id: activeSetoranPesertaId,
    qurban_peserta_member_id: memberId,
    tanggal: document.getElementById('inputSetoranTanggal').value || new Date().toISOString().slice(0, 10),
    jumlah,
    metode: document.getElementById('inputSetoranMetode').value,
    petugas: document.getElementById('inputSetoranPetugas').value,
  }

  try {
    const url = editingSetoranId
      ? `/keuangan/infaq-sodaqoh/setoran/${editingSetoranId}`
      : '/keuangan/infaq-sodaqoh/setoran'
    const res = await fetch(url, {
      method: editingSetoranId ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(payload),
    })
    if (!res.ok) throw new Error('gagal simpan')
    const newSetoran = await res.json()
    const peserta = dataTabunganQurban.find((p) => p.id === newSetoran.peserta_id)
    if (editingSetoranId) {
      const idx = peserta.riwayat.findIndex((r) => r.id === editingSetoranId)
      if (idx > -1) peserta.riwayat[idx] = newSetoran
    } else {
      peserta.riwayat.push(newSetoran)
    }
    const wasEditing = !!editingSetoranId
    closeSetoranModal()
    renderQurbanView()
    const penyetor = newSetoran.member_nama ? ` dari ${newSetoran.member_nama}` : ''
    showToast(wasEditing
      ? `Setoran ${formatRupiah(jumlah)}${penyetor} diperbarui.`
      : `Setoran ${formatRupiah(jumlah)}${penyetor} tercatat.`)
  } catch (err) {
    showToast('Gagal menyimpan setoran, coba lagi.')
  }
}

// ============================================================
// 9. MODAL CATAT DONASI -- form menyesuaikan tab aktif
// ============================================================
function openDonasiModal(item = null) {
  const meta = CATEGORY_META[activeTab]
  const isEdit = !!item
  editingDonasiId = isEdit ? item.id : null

  document.getElementById('donasiModalTitle').textContent = isEdit
    ? `Ubah ${meta.label}`
    : `Catat ${meta.label}`

  const kategoriSelect = document.getElementById('inputDonasiKategori')
  kategoriSelect.innerHTML = meta.kategoriOptions.map((k) => `<option value="${esc(k)}">${esc(k)}</option>`).join('')
  if (isEdit && !meta.kategoriOptions.includes(item.kategori)) {
    kategoriSelect.insertAdjacentHTML('afterbegin', `<option value="${esc(item.kategori)}">${esc(item.kategori)}</option>`)
  }
  kategoriSelect.value = isEdit ? item.kategori : meta.kategoriOptions[0]
  kategoriSelect.disabled = !!meta.kategoriLocked

  const formGroupIds = [
    'formGroupDonasiDonatur',
    'formGroupDonasiKategori',
    'formGroupDonasiTipe',
    'formGroupDonasiJenis',
    'formGroupDonasiNominal',
    'formGroupDonasiTanggal',
    'formGroupDonasiPeriode',
    'formGroupDonasiMetode',
    'formGroupDonasiPetugas',
    'formGroupDonasiStatus',
    'formGroupDonasiKeterangan',
  ]
  formGroupIds.forEach((id) => {
    const el = document.getElementById(id)
    if (el) el.style.display = meta.hiddenFields.includes(id) ? 'none' : ''
  })

  document.getElementById('donasiModalBox').classList.toggle('infaq-form', activeTab === 'infaq')

  const nominalLabel = document.getElementById('formLabelDonasiNominal')
  if (nominalLabel) nominalLabel.textContent = meta.nominalLabel || 'Nominal / Taksiran (Rp)'

  if (isEdit) {
    document.getElementById('inputDonasiDonatur').value = item.donatur
    document.getElementById('inputDonasiTipe').value = item.tipe
    document.getElementById('inputDonasiJenis').value = item.jenis
    document.getElementById('inputDonasiNominal').value = item.nominal
    document.getElementById('inputDonasiTanggal').value = toDateInputValue(item.tanggal)
    document.getElementById('inputDonasiDari').value = toDateInputValue(item.tanggal)
    document.getElementById('inputDonasiSampai').value = item.tanggal_akhir
      ? toDateInputValue(item.tanggal_akhir)
      : ''
    document.getElementById('inputDonasiMetode').value = item.metode === '-' ? 'Tunai' : item.metode
    document.getElementById('inputDonasiPetugas').value = item.petugas
    document.getElementById('inputDonasiStatus').value = item.status
    document.getElementById('inputDonasiKeterangan').value = item.keterangan === '-' ? '' : item.keterangan
  } else {
    document.getElementById('inputDonasiDonatur').value = ''
    document.getElementById('inputDonasiTipe').value = 'Uang'
    document.getElementById('inputDonasiJenis').value = ''
    document.getElementById('inputDonasiNominal').value = ''
    document.getElementById('inputDonasiTanggal').value = new Date().toISOString().slice(0, 10)
    document.getElementById('inputDonasiDari').value = new Date().toISOString().slice(0, 10)
    document.getElementById('inputDonasiSampai').value = ''
    document.getElementById('inputDonasiMetode').value = 'Tunai'
    document.getElementById('inputDonasiPetugas').selectedIndex = 0
    document.getElementById('inputDonasiStatus').value = 'Berhasil'
    document.getElementById('inputDonasiKeterangan').value = ''
  }

  document.getElementById('donasiModalOverlay').classList.add('active')
  syncCustomSelects()
}

// ============================================================
// 10. INISIALISASI
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  populatePetugasOptions()

  document.querySelectorAll('.ziswaf-tab').forEach((btn) => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab))
  })

  document.getElementById('searchInput').addEventListener('input', applyFilter)
  document.getElementById('jenisFilter').addEventListener('change', applyFilter)
  document.getElementById('metodeFilter').addEventListener('change', applyFilter)
  document.getElementById('periodeFilter').addEventListener('change', applyFilter)

  document.getElementById('btnCatatDonasi').addEventListener('click', () => openDonasiModal())

  document.getElementById('donasiModalCloseBtn').addEventListener('click', () => {
    document.getElementById('donasiModalOverlay').classList.remove('active')
  })
  document.getElementById('donasiModalCancelBtn').addEventListener('click', () => {
    document.getElementById('donasiModalOverlay').classList.remove('active')
  })

  document.getElementById('donasiModalSaveBtn').addEventListener('click', async () => {
    const meta = CATEGORY_META[activeTab]
    const isInfaq = activeTab === 'infaq'
    const donaturField = document.getElementById('inputDonasiDonatur')
    const donaturValue =
      meta.hiddenFields && meta.hiddenFields.includes('formGroupDonasiDonatur')
        ? 'Kotak Infaq'
        : donaturField.value

    const payload = {
      donatur: donaturValue,
      kategori: document.getElementById('inputDonasiKategori').value,
      jenis: document.getElementById('inputDonasiKategori').value,
      tipe: document.getElementById('inputDonasiTipe').value,
      nominal: document.getElementById('inputDonasiNominal').value,
      tanggal: isInfaq
        ? document.getElementById('inputDonasiDari').value
        : document.getElementById('inputDonasiTanggal').value,
      tanggal_akhir: isInfaq ? document.getElementById('inputDonasiSampai').value || null : null,
      metode: document.getElementById('inputDonasiMetode').value,
      petugas: document.getElementById('inputDonasiPetugas').value,
      status: document.getElementById('inputDonasiStatus').value,
      keterangan: document.getElementById('inputDonasiKeterangan').value,
    }

    if (isInfaq) {
      const dariVal = document.getElementById('inputDonasiDari').value
      const sampaiVal = document.getElementById('inputDonasiSampai').value
      if (sampaiVal && dariVal && sampaiVal < dariVal) {
        showToast('Tanggal "sampai" tidak boleh sebelum tanggal "dari".', 'error')
        return
      }
      if (!dariVal) {
        showToast('Periode infaq wajib diisi tanggal "dari".', 'error')
        return
      }
    }

    try {
      const url = editingDonasiId
        ? `/keuangan/infaq-sodaqoh/donasi/${editingDonasiId}`
        : '/keuangan/infaq-sodaqoh/donasi'
      const res = await fetch(url, {
        method: editingDonasiId ? 'PUT' : 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
      })
      if (!res.ok) {
        const errData = await res.json()
        const firstError = Object.values(errData.errors || {})[0]?.[0] || 'Gagal menyimpan donasi.'
        showToast(firstError)
        return
      }
      const saved = await res.json()
      if (editingDonasiId) {
        const idx = dataDonasi.findIndex((d) => d.id === editingDonasiId)
        if (idx > -1) dataDonasi[idx] = saved
      } else {
        dataDonasi.unshift(saved)
      }
      const wasEditing = !!editingDonasiId
      editingDonasiId = null
      document.getElementById('donasiModalOverlay').classList.remove('active')
      switchTab(activeTab)
      showToast(wasEditing ? 'Donasi berhasil diperbarui.' : 'Donasi berhasil dicatat.')
    } catch (err) {
      showToast('Terjadi kesalahan saat menyimpan donasi.')
    }
  })
  
  document.getElementById('btnExport').addEventListener('click', exportCSV)

  // Qurban: tombol & modal
  const datalistJamaah = document.getElementById('datalistJamaah')
  if (datalistJamaah) {
    datalistJamaah.innerHTML = dataJamaahQurban.map((j) => `<option value="${esc(j.nama)}">`).join('')
  }

  document.getElementById('btnTambahPeserta').addEventListener('click', openPesertaModal)
  document.getElementById('inputPesertaPaket').addEventListener('change', syncPesertaFormMode)
  document.getElementById('btnTambahMember').addEventListener('click', () => {
    if (pesertaMemberRows.length >= 7) return
    pesertaMemberRows.push({ nama: '' })
    renderMemberList()
  })
  document.getElementById('pesertaModalCloseBtn').addEventListener('click', closePesertaModal)
  document.getElementById('pesertaModalCancelBtn').addEventListener('click', closePesertaModal)
  document.getElementById('pesertaModalSaveBtn').addEventListener('click', savePeserta)
  document.getElementById('pesertaModalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'pesertaModalOverlay') closePesertaModal()
  })

  document.getElementById('memberModalCloseBtn').addEventListener('click', closeMemberModal)
  document.getElementById('memberModalCancelBtn').addEventListener('click', closeMemberModal)
  document.getElementById('memberModalSaveBtn').addEventListener('click', saveMember)
  document.getElementById('memberModalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'memberModalOverlay') closeMemberModal()
  })

  document.getElementById('setoranModalCloseBtn').addEventListener('click', closeSetoranModal)
  document.getElementById('setoranModalCancelBtn').addEventListener('click', closeSetoranModal)
  document.getElementById('setoranModalSaveBtn').addEventListener('click', saveSetoran)
  document.getElementById('setoranModalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'setoranModalOverlay') closeSetoranModal()
  })

  // Modal detail transaksi
  document.getElementById('detailTransaksiCloseBtn').addEventListener('click', closeDetailTransaksi)
  document.getElementById('detailTransaksiCloseBtn2').addEventListener('click', closeDetailTransaksi)
  document.getElementById('detailTransaksiModalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'detailTransaksiModalOverlay') closeDetailTransaksi()
  })

  switchTab('ringkasan')
})