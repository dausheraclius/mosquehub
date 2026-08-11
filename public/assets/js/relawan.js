// --- Data dummy jemaah (sumbernya dari Data Jemaah, ini cuma cerminannya) ---
const daftarJamaahRelawan = [
  { nama: 'M. Reza', telepon: '0812-3456-7890' },
  { nama: 'Fatimah', telepon: '0812-3456-7891' },
  { nama: 'S. Abdullah', telepon: '0812-3456-7892' },
  { nama: 'Ust. Hakim', telepon: '0812-3456-7893' },
  { nama: 'Kirei', telepon: '0812-3456-7894' },
  { nama: 'Sandi', telepon: '0812-3456-7895' },
  { nama: 'Eman', telepon: '0812-3456-7896' },
  { nama: 'Enjang', telepon: '0812-3456-7897' },
]

// --- Data dummy kegiatan relawan ---
let kegiatanRelawan = [
  {
    id: 1,
    nama: 'Kajian Subuh Rutin',
    icon: 'fa-hand',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Akan Datang',
    relawan: [daftarJamaahRelawan[0], daftarJamaahRelawan[1]],
  },
  {
    id: 2,
    nama: 'Pengajian Tahsin & Quran',
    icon: 'fa-book-open',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Akan Datang',
    relawan: [daftarJamaahRelawan[2]],
  },
  {
    id: 3,
    nama: 'Kerja Bakti Bulanan',
    icon: 'fa-broom',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Berlangsung',
    relawan: daftarJamaahRelawan.slice(0, 5),
  },
  {
    id: 4,
    nama: 'Rapat Koordinasi YMBPK',
    icon: 'fa-users',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Selesai',
    relawan: daftarJamaahRelawan.slice(0, 3),
  },
  {
    id: 5,
    nama: 'Penjajian Tahsin & Quran',
    icon: 'fa-hand',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Akan Datang',
    relawan: [],
  },
  {
    id: 6,
    nama: 'Rapat Koordinasi YMBPK',
    icon: 'fa-users',
    tanggal: '16 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Akan Datang',
    relawan: [daftarJamaahRelawan[3]],
  },
  {
    id: 7,
    nama: 'Rapat Koordinasi YMBPK',
    icon: 'fa-users',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Berlangsung',
    relawan: daftarJamaahRelawan.slice(0, 6),
  },
  {
    id: 8,
    nama: 'Persiapan Idul Adha',
    icon: 'fa-mosque',
    tanggal: '15 Jun 2026',
    lokasi: 'YMBPK Baiturrahim - Ruang Utama',
    slotMax: 20,
    status: 'Selesai',
    relawan: daftarJamaahRelawan,
  },
]

const statusClassMap = {
  'Akan Datang': 'status-akan-datang',
  Berlangsung: 'status-berlangsung',
  Selesai: 'status-selesai',
}
let currentPage = 1
const perPage = 8
let activeKegiatanId = null
let tempSelectedPhones = []

function getFilteredKegiatan() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const status = document.getElementById('statusFilter').value
  return kegiatanRelawan.filter((k) => {
    const matchSearch = k.nama.toLowerCase().includes(keyword)
    const matchStatus = status === '' || k.status === status
    return matchSearch && matchStatus
  })
}

function renderGrid() {
  const filtered = getFilteredKegiatan()
  const totalPages = Math.max(1, Math.ceil(filtered.length / perPage))
  if (currentPage > totalPages) currentPage = totalPages

  const start = (currentPage - 1) * perPage
  const pageItems = filtered.slice(start, start + perPage)

  const grid = document.getElementById('relawanGrid')
  grid.innerHTML = ''

  if (pageItems.length === 0) {
    grid.innerHTML = `<p style="grid-column:1/-1; text-align:center; color:var(--text-muted); padding:30px 0;">Tidak ada kegiatan ditemukan</p>`
  }

  pageItems.forEach((k) => {
    grid.insertAdjacentHTML(
      'beforeend',
      `
      <div class="relawan-card">
        <div class="relawan-icon"><i class="fa-solid ${k.icon}"></i></div>
        <div class="relawan-title">${k.nama}</div>
        <div class="relawan-meta-item"><i class="fa-regular fa-calendar"></i> ${k.tanggal}</div>
        <div class="relawan-meta-item"><i class="fa-solid fa-location-dot"></i> ${k.lokasi}</div>
        <div class="relawan-meta-item"><i class="fa-solid fa-users"></i> ${k.relawan.length} / ${k.slotMax} Terdaftar</div>
        <span class="status-badge ${statusClassMap[k.status]} relawan-status">${k.status}</span>
        <button class="btn btn-primary btn-kelola-relawan" data-id="${k.id}">Kelola Relawan</button>
      </div>
    `,
    )
  })

  renderPagination(totalPages)

  document.querySelectorAll('.btn-kelola-relawan').forEach((btn) => {
    btn.addEventListener('click', () => openRelawanModal(parseInt(btn.dataset.id)))
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

// --- Modal Kelola Relawan ---
function openRelawanModal(id) {
  activeKegiatanId = id
  const kegiatan = kegiatanRelawan.find((k) => k.id === id)
  tempSelectedPhones = kegiatan.relawan.map((r) => r.telepon)

  document.getElementById('relawanModalTitle').textContent = `Kelola Relawan - ${kegiatan.nama}`
  document.getElementById('slotMax').textContent = kegiatan.slotMax
  document.getElementById('jamaahSearchInput').value = ''

  renderJamaahList()
  document.getElementById('relawanModal').classList.add('active')
}

function renderJamaahList(keyword = '') {
  const list = document.getElementById('relawanJamaahList')
  const filtered = daftarJamaahRelawan.filter((j) => j.nama.toLowerCase().includes(keyword.toLowerCase()))

  list.innerHTML = filtered
    .map((j) => {
      const checked = tempSelectedPhones.includes(j.telepon)
      return `
      <label class="relawan-jamaah-item">
        <input type="checkbox" data-telepon="${j.telepon}" ${checked ? 'checked' : ''}>
        <div class="relawan-jamaah-avatar"><i class="fa-solid fa-user"></i></div>
        <div class="relawan-jamaah-info">
          <div class="relawan-jamaah-name">${j.nama}</div>
          <div class="relawan-jamaah-meta">${j.telepon}</div>
        </div>
      </label>
    `
    })
    .join('')

  list.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
    cb.addEventListener('change', (e) => {
      const telepon = e.target.dataset.telepon
      if (e.target.checked) {
        tempSelectedPhones.push(telepon)
      } else {
        tempSelectedPhones = tempSelectedPhones.filter((t) => t !== telepon)
      }
      document.getElementById('slotCount').textContent = tempSelectedPhones.length
    })
  })

  document.getElementById('slotCount').textContent = tempSelectedPhones.length
}

document.getElementById('jamaahSearchInput').addEventListener('input', (e) => renderJamaahList(e.target.value))

document.getElementById('tambahRelawanBtn').addEventListener('click', () => {
  document.getElementById('relawanTambahForm').classList.toggle('show')
})

document.getElementById('batalTambahRelawanBtn').addEventListener('click', () => {
  document.getElementById('relawanTambahForm').classList.remove('show')
})

document.getElementById('simpanRelawanBaruBtn').addEventListener('click', () => {
  const nama = document.getElementById('relawanNamaInput').value.trim()
  if (!nama) return
  const telepon = document.getElementById('relawanTeleponInput').value.trim() || '-'
  const exists = daftarJamaahRelawan.some((j) => j.telepon === telepon)
  if (exists && telepon !== '-') return
  const baru = { nama, telepon }
  daftarJamaahRelawan.push(baru)
  tempSelectedPhones.push(telepon)
  document.getElementById('relawanNamaInput').value = ''
  document.getElementById('relawanTeleponInput').value = ''
  document.getElementById('relawanTambahForm').classList.remove('show')
  renderJamaahList(document.getElementById('jamaahSearchInput').value)
})

document.getElementById('closeRelawanModal').addEventListener('click', () => {
  document.getElementById('relawanModal').classList.remove('active')
})
document.getElementById('cancelRelawanBtn').addEventListener('click', () => {
  document.getElementById('relawanModal').classList.remove('active')
})

document.getElementById('saveRelawanBtn').addEventListener('click', () => {
  const kegiatan = kegiatanRelawan.find((k) => k.id === activeKegiatanId)
  kegiatan.relawan = daftarJamaahRelawan.filter((j) => tempSelectedPhones.includes(j.telepon))
  document.getElementById('relawanModal').classList.remove('active')
  renderGrid()
})

document.querySelectorAll('.modal-overlay').forEach((overlay) => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('active')
  })
})

// --- Init ---
document.getElementById('searchInput').addEventListener('input', () => {
  currentPage = 1
  renderGrid()
})
document.getElementById('statusFilter').addEventListener('change', () => {
  currentPage = 1
  renderGrid()
})

renderGrid()
