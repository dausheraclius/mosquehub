const daftarJamaahRelawan = window.__DAFTAR_JAMAAH_RELAWAN__ || []
let kegiatanRelawan = window.__KEGIATAN_RELAWAN__ || []

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

document.getElementById('saveRelawanBtn').addEventListener('click', async () => {
  const kegiatan = kegiatanRelawan.find((k) => k.id === activeKegiatanId)
  const relawanTerpilih = daftarJamaahRelawan.filter((j) => tempSelectedPhones.includes(j.telepon))

  try {
    const res = await fetch(`/relawan/${activeKegiatanId}`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ relawan: relawanTerpilih }),
    })
    if (!res.ok) throw new Error('gagal simpan')
    const updated = await res.json()

    kegiatan.relawan = updated.relawan
    document.getElementById('relawanModal').classList.remove('active')
    renderGrid()
  } catch (err) {
    showToast('Gagal menyimpan relawan, coba lagi.')
  }
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
