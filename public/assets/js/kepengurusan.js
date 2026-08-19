const daftarJamaah = window.__DAFTAR_JAMAAH__ || []

function personMeta(p) {
  return [p.email, p.hp].filter(Boolean).join(' | ')
}

function getNamaJabatan() {
  return window.__JABATAN_LIST__ || []
}

// ============ HIERARKI (struktur atasan-bawahan) ============
// Format: { [namaJabatan]: namaAtasan | null }
// Hierarki menjawab "siapa parent dari jabatan ini?" — terpisah dari posisi visual.
function getHierarki() {
  return window.__HIERARKI__ || {}
}

// Snapshot hierarki dari server — dipakai tombol Reset.
const serverHierarki = JSON.parse(JSON.stringify(getHierarki()))

function simpanHierarki(hierarki) {
  window.__HIERARKI__ = hierarki
}

// Cek: apakah `calonAtasan` adalah keturunan dari `nama`? (cegah struktur muter/cycle)
function isDescendant(hierarki, nama, calonAtasan) {
  let current = calonAtasan
  while (current) {
    if (current === nama) return true
    current = hierarki[current]
  }
  return false
}

function getPenempatan() {
  return window.__PENEMPATAN__ || {}
}

async function simpanPenempatan(penempatan) {
  const res = await fetch('/kepengurusan/penempatan', {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ penempatan }),
  })
  if (!res.ok) throw new Error('Gagal menyimpan penempatan')
  window.__PENEMPATAN__ = penempatan
}

function bangunJabatanData() {
  const namaList = getNamaJabatan()
  const penempatan = getPenempatan()

  return namaList.map((nama) => {
    const id = penempatan[nama] || null
    const person = id ? daftarJamaah.find((p) => p.id === Number(id)) || null : null
    return { nama, person }
  })
}

let jabatanData = bangunJabatanData()
let isEditMode = false

// ============ POSISI VISUAL (x/y) — terpisah dari hierarki ============
// Format: { [namaJabatan]: { x, y } }
// Posisi menjawab "di koordinat mana node ditampilkan?" dan hanya berubah
// lewat drag (edit mode). Disimpan ke server saat tombol Simpan ditekan.
const serverPositions = JSON.parse(JSON.stringify(window.__POSISI_ORG__ || {}))
// Muat posisi tersimpan dari server — tanpa ini semua node dihitung ulang
// pakai layout default dan hasil geseran hilang saat halaman dimuat ulang.
let positions = JSON.parse(JSON.stringify(serverPositions))

// Dimensi & jarak default (samakan dengan CSS .org-node)
const NODE_W = 170
const NODE_H = 56
const PAD_X = 60
const PAD_Y = 60
const H_SPACING = 230
const V_SPACING = 150

function getPositions() {
  return positions
}

// Generate layout default berbasis hierarki: parent di tengah anak-anaknya,
// tiap level turun satu baris. Hanya dipakai untuk jabatan yang belum punya
// posisi — posisi yang sudah ada (server / hasil drag) tidak pernah ditimpa.
function computeDefaultLayout() {
  const hierarki = getHierarki()
  const namaList = getNamaJabatan()
  const inList = (n) => !!n && namaList.includes(n)
  const childrenOf = (n) => namaList.filter((c) => hierarki[c] === n)
  // Root = parent null ATAU parent yang sudah tidak ada di daftar (aman dari node hilang)
  const roots = namaList.filter((n) => !inList(hierarki[n]))

  // Depth tiap node, cycle-safe
  const depth = {}
  const computeDepth = (n, seen = {}) => {
    if (depth[n] !== undefined) return depth[n]
    if (seen[n]) return 0
    seen[n] = true
    const parent = hierarki[n]
    depth[n] = inList(parent) ? computeDepth(parent, seen) + 1 : 0
    return depth[n]
  }
  namaList.forEach((n) => computeDepth(n))

  // Slot x: in-order DFS per subtree; parent diposisikan di tengah anak-anaknya
  const slot = {}
  const assigned = new Set()
  let cursor = 0
  const assign = (n) => {
    if (assigned.has(n)) return slot[n] !== undefined ? slot[n] : cursor++
    assigned.add(n)
    const kids = childrenOf(n)
    if (kids.length === 0) {
      slot[n] = cursor++
    } else {
      const childSlots = kids.map((k) => assign(k))
      slot[n] = (Math.min(...childSlots) + Math.max(...childSlots)) / 2
    }
    return slot[n]
  }
  roots.forEach((r) => assign(r))
  namaList.forEach((n) => {
    if (slot[n] === undefined) slot[n] = cursor++
  })

  const layout = {}
  namaList.forEach((n) => {
    layout[n] = {
      x: Math.round(PAD_X + slot[n] * H_SPACING),
      y: Math.round(PAD_Y + depth[n] * V_SPACING),
    }
  })
  return layout
}

// Pastikan semua jabatan punya posisi sebelum render.
function ensureDefaultPositions() {
  const namaList = getNamaJabatan()
  const layout = computeDefaultLayout()

  namaList.forEach((nama) => {
    if (!positions[nama]) positions[nama] = layout[nama]
  })

  // Buang posisi jabatan yang sudah dihapus dari daftar
  Object.keys(positions).forEach((nama) => {
    if (!namaList.includes(nama)) delete positions[nama]
  })
}

async function simpanPositionsServer() {
  const res = await fetch('/kepengurusan/jabatan/positions', {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ positions }),
  })
  if (!res.ok) throw new Error('Gagal menyimpan posisi')
}

// ============ RENDER ORG CHART (canvas + posisi absolut) ============
function personLabel(nama) {
  const item = jabatanData.find((j) => j.nama === nama)
  return item?.person?.nama || 'Belum Diisi'
}

function nodeBoxHtml(nama) {
  const p = positions[nama] || { x: PAD_X, y: PAD_Y }
  return `
    <div class="org-node" data-nama="${esc(nama)}" style="left:${p.x}px;top:${p.y}px">
      <div class="org-avatar" draggable="false" title="Seret ikon ini ke node lain untuk mengubah atasan"><i class="fa-solid fa-user"></i></div>
      <div class="org-node-info" draggable="false">
        <span class="org-role" draggable="false">${esc(nama)}</span>
        <span class="org-name" draggable="false">${esc(personLabel(nama))}</span>
      </div>
      <button class="org-node-add org-node-add--side" data-nama="${esc(nama)}" draggable="false" title="Tambah jabatan anak"><i class="fa-solid fa-plus"></i></button>
      <button class="org-node-add org-node-add--bottom" data-nama="${esc(nama)}" draggable="false" title="Tambah jabatan anak"><i class="fa-solid fa-plus"></i></button>
    </div>
  `
}

// Map nama jabatan -> elemen node (buat update posisi tanpa query selector rumit)
let nodeMap = {}

function renderOrgChart() {
  const namaList = getNamaJabatan()
  const wrapper = document.getElementById('orgChartWrapper')

  if (namaList.length === 0) {
    wrapper.innerHTML = `<p style="text-align:center;color:var(--text-muted);font-size:12.5px;padding:30px 0;">Belum ada jabatan — tambah dulu dari menu <strong>Umum → Jabatan</strong>.</p>`
    nodeMap = {}
    return
  }

  ensureDefaultPositions()

  // Ukuran canvas mengikuti posisi node paling jauh — node tidak pernah terpotong
  let maxX = PAD_X
  let maxY = PAD_Y
  namaList.forEach((nama) => {
    const p = positions[nama]
    maxX = Math.max(maxX, p.x + NODE_W)
    maxY = Math.max(maxY, p.y + NODE_H)
  })
  const canvasW = maxX + PAD_X
  const canvasH = maxY + PAD_Y

  wrapper.innerHTML = `
    <div class="org-chart-canvas${isEditMode ? ' is-editing' : ''}" id="orgChartCanvas" style="width:${canvasW}px;height:${canvasH}px">
      ${namaList.map((nama) => nodeBoxHtml(nama)).join('')}
      <svg class="org-connector-svg" aria-hidden="true"></svg>
    </div>
    <div class="org-drop-hint" style="display:${isEditMode ? 'block' : 'none'}">
      <i class="fa-solid fa-arrows-up-down"></i> Geser node untuk mengatur posisi. Seret <i class="fa-solid fa-user"></i> ke node lain untuk mengubah atasan.
    </div>
  `

  nodeMap = {}
  wrapper.querySelectorAll('.org-node').forEach((node) => {
    nodeMap[node.dataset.nama] = node
  })
  syncDragAttrs()
  syncAddButtons()
  renderConnectors()
}

// ============ CONNECTOR DINAMIS (SVG parent → child) ============
// Garis dirender berdasarkan pasangan parent-child dari hierarki, bukan urutan
// DOM. Setiap node bergeser, connector ikut diperbarui (lihat setNodePosition).
function renderConnectors() {
  const canvas = document.getElementById('orgChartCanvas')
  const svg = canvas?.querySelector('.org-connector-svg')
  if (!canvas || !svg) return

  const hierarki = getHierarki()
  const namaList = getNamaJabatan()
  const paths = []

  namaList.forEach((nama) => {
    const parent = hierarki[nama]
    if (!parent || !namaList.includes(parent)) return
    const child = positions[nama]
    const par = positions[parent]
    if (!child || !par) return

    // Titik sambung: tengah-bawah parent → tengah-atas child (bentuk siku)
    const x1 = par.x + NODE_W / 2
    const y1 = par.y + NODE_H
    const x2 = child.x + NODE_W / 2
    const y2 = child.y
    const midY = y1 + (y2 - y1) / 2

    paths.push(`<path class="org-connector-path" d="M ${x1} ${y1} L ${x1} ${midY} L ${x2} ${midY} L ${x2} ${y2}"/>`)
  })

  svg.innerHTML = paths.join('')
}

let connectorRaf = null

// Throttle render connector saat drag berlangsung (via requestAnimationFrame)
function updateConnectorsSoon() {
  if (connectorRaf) return
  connectorRaf = requestAnimationFrame(() => {
    connectorRaf = null
    renderConnectors()
  })
}

// Update posisi node + canvas (node jangan sampai terpotong) + connector
function setNodePosition(nama, x, y) {
  positions[nama] = { x: Math.max(0, Math.round(x)), y: Math.max(0, Math.round(y)) }
  const node = nodeMap[nama]
  if (!node) return
  node.style.left = positions[nama].x + 'px'
  node.style.top = positions[nama].y + 'px'

  const canvas = document.getElementById('orgChartCanvas')
  if (canvas) {
    const needW = positions[nama].x + NODE_W + PAD_X
    const needH = positions[nama].y + NODE_H + PAD_Y
    if (needW > canvas.offsetWidth) canvas.style.width = needW + 'px'
    if (needH > canvas.offsetHeight) canvas.style.height = needH + 'px'
  }
  updateConnectorsSoon()
}

function syncDragAttrs() {
  document.querySelectorAll('.org-node').forEach((node) => {
    const avatar = node.querySelector('.org-avatar')
    if (avatar) avatar.draggable = isEditMode
  })
  const canvas = document.getElementById('orgChartCanvas')
  if (canvas) canvas.classList.toggle('is-editing', isEditMode)
  const hint = document.querySelector('.org-drop-hint')
  if (hint) hint.style.display = isEditMode ? 'block' : 'none'
}

// ============ RENDER STRUKTUR EDITOR (kanan, dropdown atasan) ============
function renderStrukturList() {
  const namaList = getNamaJabatan()
  const hierarki = getHierarki()
  const container = document.getElementById('strukturList')

  container.innerHTML = namaList.map((nama) => {
    const options = namaList
      .filter((n) => n !== nama)
      .map((n) => {
        const disabled = isDescendant(hierarki, nama, n) ? 'disabled' : ''
        const selected = hierarki[nama] === n ? 'selected' : ''
        return `<option value="${esc(n)}" ${selected} ${disabled}>${esc(n)}</option>`
      })
      .join('')

    return `
      <div class="struktur-row">
        <span class="struktur-row-name">${esc(nama)}</span>
        <select class="struktur-select" data-nama="${esc(nama)}">
          <option value="" ${hierarki[nama] === null ? 'selected' : ''}>— Level Teratas —</option>
          ${options}
        </select>
      </div>
    `
  }).join('')

  container.querySelectorAll('.struktur-select').forEach((sel) => {
    sel.addEventListener('change', async (e) => {
      const nama = sel.dataset.nama
      const newParent = sel.value === '' ? null : sel.value
      const hierarki = getHierarki()

      // Validasi ulang: tolak kalau bakal bikin hierarki muter (mis. lewat custom select)
      if (newParent && isDescendant(hierarki, nama, newParent)) {
        showToast('Gak bisa: bakal bikin hierarki muter.', 'fa-solid fa-triangle-exclamation')
        renderStrukturList()
        return
      }

      hierarki[nama] = newParent
      simpanHierarki(hierarki)
      renderOrgChart()
      renderStrukturList()

      await fetch('/kepengurusan/jabatan/parent', {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ nama, parent_nama: newParent }),
      })
    })
  })
}

// --- Render List Jabatan (view-only vs edit mode) ---
function renderJabatanList() {
  const container = document.getElementById('jabatanList')
  container.innerHTML = ''

  jabatanData.forEach((item) => {
    if (!isEditMode) {
      const namaOrang = item.person ? item.person.nama : 'Belum Diisi'
      const isEmpty = !item.person
      container.insertAdjacentHTML('beforeend', `
        <div class="jabatan-row">
          <span class="jabatan-name">${esc(item.nama)}</span>
          <span class="status-badge ${isEmpty ? 'status-empty' : 'status-filled'}">${esc(namaOrang)}</span>
        </div>
      `)
      return
    }

    const triggerContent = item.person
      ? `
        <div class="jabatan-select-person">
          <div class="jabatan-select-avatar"><i class="fa-solid fa-user"></i></div>
          <div class="jabatan-select-info">
            <span class="jabatan-select-name">${esc(item.person.nama)}</span>
            <span class="jabatan-select-meta">${esc(personMeta(item.person))}</span>
          </div>
        </div>
      `
      : `<span class="jabatan-select-placeholder">Pilih Jemaah</span>`

    container.insertAdjacentHTML('beforeend', `
      <div class="jabatan-row">
        <span class="jabatan-name">${esc(item.nama)}</span>
        <div class="jabatan-select" data-nama="${esc(item.nama)}">
          <div class="jabatan-select-trigger">
            ${triggerContent}
            <i class="fa-solid fa-chevron-down" style="font-size:10px; color:var(--text-muted); flex-shrink:0;"></i>
          </div>
          <div class="jabatan-dropdown">
            <div class="jabatan-dropdown-search">
              <input type="text" placeholder="Search jamaah...">
            </div>
            <div class="jabatan-dropdown-items"></div>
          </div>
        </div>
      </div>
    `)
  })

  if (isEditMode) attachDropdownEvents()
  document.getElementById('jabatanFooterActions').style.display = isEditMode ? 'flex' : 'none'
}

function renderDropdownItems(dropdownEl, namaJabatan, keyword = '') {
  const itemsContainer = dropdownEl.querySelector('.jabatan-dropdown-items')
  const currentItem = jabatanData.find((j) => j.nama === namaJabatan)
  const filtered = daftarJamaah.filter((p) => p.nama.toLowerCase().includes(keyword.toLowerCase()))

  if (filtered.length === 0) {
    itemsContainer.innerHTML = `<div class="jabatan-dropdown-empty">Jemaah tidak ditemukan</div>`
    return
  }

  itemsContainer.innerHTML = filtered.map((p) => {
    const isSelected = currentItem.person?.id === p.id
    return `
      <div class="jabatan-dropdown-item ${isSelected ? 'selected' : ''}" data-id="${p.id}">
        <div class="jabatan-dropdown-avatar"><i class="fa-solid fa-user"></i></div>
        <div class="jabatan-dropdown-info">
          <span class="jabatan-dropdown-name">${esc(p.nama)}</span>
          <span class="jabatan-dropdown-meta">${esc(personMeta(p))}</span>
        </div>
        ${isSelected ? '<i class="fa-solid fa-check" style="color:var(--color-green); margin-left:auto;"></i>' : ''}
      </div>
    `
  }).join('')

  itemsContainer.querySelectorAll('.jabatan-dropdown-item').forEach((el) => {
    el.addEventListener('click', () => {
      const id = el.dataset.id
      const person = daftarJamaah.find((p) => p.id === Number(id))
      const jabatan = jabatanData.find((j) => j.nama === namaJabatan)
      jabatan.person = person
      dropdownEl.classList.remove('show')
      renderJabatanList()
      renderOrgChart()
    })
  })
}

function attachDropdownEvents() {
  document.querySelectorAll('.jabatan-select').forEach((selectEl) => {
    const namaJabatan = selectEl.dataset.nama
    const trigger = selectEl.querySelector('.jabatan-select-trigger')
    const dropdown = selectEl.querySelector('.jabatan-dropdown')
    const searchInput = dropdown.querySelector('input')

    trigger.addEventListener('click', (e) => {
      e.stopPropagation()
      document.querySelectorAll('.jabatan-dropdown.show').forEach((d) => { if (d !== dropdown) d.classList.remove('show') })
      dropdown.classList.toggle('show')
      if (dropdown.classList.contains('show')) {
        renderDropdownItems(dropdown, namaJabatan)
        searchInput.value = ''
        searchInput.focus()
      }
    })

    searchInput.addEventListener('input', (e) => renderDropdownItems(dropdown, namaJabatan, e.target.value))
    dropdown.addEventListener('click', (e) => e.stopPropagation())
  })
}

document.addEventListener('click', () => {
  document.querySelectorAll('.jabatan-dropdown.show').forEach((d) => d.classList.remove('show'))
})

// --- Toggle Edit Mode ---
document.getElementById('toggleEditBtn').addEventListener('click', () => {
  isEditMode = !isEditMode
  const btn = document.getElementById('toggleEditBtn')
  btn.innerHTML = isEditMode
    ? `<i class="fa-solid fa-xmark"></i> Selesai Edit`
    : `<i class="fa-solid fa-pen"></i> Edit Kepengurusan`

  document.getElementById('strukturSection').style.display = isEditMode ? 'block' : 'none'
  if (isEditMode) renderStrukturList()
  renderOrgChart()
  renderJabatanList()
})

// Reset = kembalikan semua perubahan yang BELUM disimpan ke data terakhir dari server.
// Tidak pernah menghapus data database.
document.getElementById('resetBtn').addEventListener('click', () => {
  if (confirm('Reset semua perubahan yang belum disimpan?')) {
    jabatanData = bangunJabatanData()
    window.__HIERARKI__ = JSON.parse(JSON.stringify(serverHierarki))
    positions = {}
    Object.assign(positions, JSON.parse(JSON.stringify(serverPositions)))
    renderJabatanList()
    renderOrgChart()
    renderStrukturList()
  }
})

// Simpan = penempatan jamaah + posisi node (hierarki sudah tersimpan saat diubah)
document.getElementById('simpanBtn').addEventListener('click', async () => {
  const penempatan = {}
  jabatanData.forEach((j) => { if (j.person) penempatan[j.nama] = j.person.id })

  const btn = document.getElementById('simpanBtn')
  btn.disabled = true
  try {
    await simpanPenempatan(penempatan)
    await simpanPositionsServer()
    // Snapshot terbaru dari server — jadi Reset berikutnya kembali ke data ini
    Object.keys(serverPositions).forEach((k) => delete serverPositions[k])
    Object.assign(serverPositions, JSON.parse(JSON.stringify(positions)))
    updateRingkasan()
    showToast('Perubahan kepengurusan berhasil disimpan.')
  } catch (err) {
    showToast('Gagal menyimpan perubahan. Coba lagi.', 'fa-solid fa-triangle-exclamation')
  } finally {
    btn.disabled = false
  }
})

// ============ TOMBOL + TAMBAH JABATAN ANAK ============
const dndWrapper = document.getElementById('orgChartWrapper')

function syncAddButtons() {
  document.querySelectorAll('.org-node-add').forEach((btn) => {
    btn.classList.toggle('visible', isEditMode)
  })
}

// ============ MODAL TAMBAH JABATAN ============
let addJabatanParent = null

const addJabatanModal = document.getElementById('addJabatanModal')
const addJabatanInput = document.getElementById('addJabatanInput')
const addJabatanHint = document.getElementById('addJabatanHint')

function openAddJabatanModal(parentNama) {
  addJabatanParent = parentNama
  addJabatanInput.value = ''
  addJabatanInput.classList.remove('input-error')
  addJabatanHint.textContent = `Jabatan baru akan menjadi anak dari "${parentNama}"`
  addJabatanHint.style.color = 'var(--text-muted)'
  addJabatanModal.classList.add('show')
  setTimeout(() => addJabatanInput.focus(), 100)
}

function closeAddJabatanModal() {
  addJabatanModal.classList.remove('show')
  addJabatanParent = null
}

async function confirmAddJabatan() {
  const parentNama = addJabatanParent
  if (!parentNama) return

  const childNama = addJabatanInput.value
  if (!childNama || !childNama.trim()) {
    addJabatanInput.classList.add('input-error')
    addJabatanHint.textContent = 'Nama jabatan wajib diisi.'
    addJabatanHint.style.color = 'var(--color-red)'
    addJabatanInput.focus()
    return
  }

  const trimmed = childNama.trim()

  // Cek duplikasi nama
  const namaList = getNamaJabatan()
  if (namaList.includes(trimmed)) {
    addJabatanInput.classList.add('input-error')
    addJabatanHint.textContent = `Jabatan "${trimmed}" udah ada.`
    addJabatanHint.style.color = 'var(--color-red)'
    addJabatanInput.focus()
    return
  }

  // Tambah ke master list jabatan
  const res = await fetch('/kepengurusan/jabatan', {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ nama: trimmed, parent_nama: parentNama }),
  })
  if (!res.ok) {
    const err = await res.json()
    addJabatanInput.classList.add('input-error')
    addJabatanHint.textContent = err.message || 'Gagal menambah jabatan.'
    addJabatanHint.style.color = 'var(--color-red)'
    return
  }

  // Tambah ke master list jabatan (di memory)
  namaList.push(trimmed)
  window.__JABATAN_LIST__ = namaList

  // Set hierarki: anak dari parentNama
  const hierarki = getHierarki()
  hierarki[trimmed] = parentNama
  window.__HIERARKI__ = hierarki

  // Refresh data jabatanData
  jabatanData = bangunJabatanData()

  // Posisi default di dekat parent — jangan sampai node baru numpuk di pojok (0,0)
  const parentPos = positions[parentNama]
  positions[trimmed] = parentPos
    ? { x: parentPos.x + 90, y: parentPos.y + V_SPACING }
    : computeDefaultLayout()[trimmed] || { x: PAD_X, y: PAD_Y }

  // Re-render semua
  renderOrgChart()
  renderJabatanList()
  if (isEditMode) renderStrukturList()
  updateRingkasan()

  closeAddJabatanModal()
  showToast(`"${trimmed}" ditambahkan sebagai anak "${parentNama}"`)
}

// Event listener buka modal
function showAddJabatanModal(e) {
  const addBtn = e.target.closest('.org-node-add')
  if (!addBtn) return
  const parentNama = addBtn.dataset.nama
  openAddJabatanModal(parentNama)
}

// Enter di input = submit
addJabatanInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault()
    confirmAddJabatan()
  }
})

// Tombol modal
document.getElementById('confirmAddJabatanBtn').addEventListener('click', confirmAddJabatan)
document.getElementById('cancelAddJabatanBtn').addEventListener('click', closeAddJabatanModal)
document.getElementById('closeAddJabatanModal').addEventListener('click', closeAddJabatanModal)

// Click outside modal buat close
addJabatanModal.addEventListener('click', (e) => {
  if (e.target === addJabatanModal) closeAddJabatanModal()
})

// Reset error state pas user ngetik
addJabatanInput.addEventListener('input', () => {
  addJabatanInput.classList.remove('input-error')
  addJabatanHint.textContent = addJabatanParent
    ? `Jabatan baru akan menjadi anak dari "${addJabatanParent}"`
    : ''
  addJabatanHint.style.color = 'var(--text-muted)'
})

// Click tombol + di node pake event delegation
dndWrapper.addEventListener('click', showAddJabatanModal)

// ============ RINGKASAN ATAS (angka asli, bukan hardcode) ============
function updateRingkasan() {
  const namaList = getNamaJabatan()
  const penempatan = getPenempatan()
  const total = namaList.length
  const terisi = Object.values(penempatan || {}).filter((v) => v).length
  const kosong = Math.max(0, total - terisi)

  const setVal = (id, val) => {
    const el = document.getElementById(id)
    if (el) el.textContent = val
  }

  setVal('jumlahPengurusValue', terisi)
  setVal('jabatanTerisiValue', terisi)
  setVal('jabatanKosongValue', kosong)
  const terisiBadge = document.getElementById('jabatanTerisiBadge')
  if (terisiBadge) terisiBadge.textContent = `+${terisi} Terisi`
  const kosongBadge = document.getElementById('jabatanKosongBadge')
  if (kosongBadge) kosongBadge.textContent = `+${kosong} Kosong`
}

renderOrgChart()
renderJabatanList()
updateRingkasan()

// ============ DRAG POSISI VISUAL (edit mode) ============
// Drag biasa (body node) = mengubah posisi x/y saja — TIDAK mengubah parent.
// Drag hierarki tetap ada: seret ikon avatar ke node lain (lihat bagian bawah).
let dragState = null

dndWrapper.addEventListener('pointerdown', (e) => {
  if (!isEditMode) return
  if (e.pointerType === 'mouse' && e.button !== 0) return
  // Avatar = handle ubah hierarki (HTML5 drag), tombol + = tambah jabatan
  if (e.target.closest('.org-avatar')) return
  if (e.target.closest('.org-node-add')) return

  const node = e.target.closest('.org-node')
  if (!node) return
  const nama = node.dataset.nama
  const p = positions[nama]
  if (!p) return

  dragState = {
    nama,
    pointerId: e.pointerId,
    startX: e.clientX,
    startY: e.clientY,
    origX: p.x,
    origY: p.y,
    moved: false,
  }
  node.classList.add('moving')
})

// Listener global (dipasang sekali) — drag tidak pernah terduplikasi walau
// chart dirender ulang berkali-kali.
document.addEventListener('pointermove', (e) => {
  if (!dragState || e.pointerId !== dragState.pointerId) return
  const dx = e.clientX - dragState.startX
  const dy = e.clientY - dragState.startY
  if (!dragState.moved && Math.abs(dx) + Math.abs(dy) < 4) return
  dragState.moved = true
  setNodePosition(dragState.nama, dragState.origX + dx, dragState.origY + dy)
})

function endPositionDrag(e) {
  if (!dragState || (e && e.pointerId !== dragState.pointerId)) return
  const node = dragState.moved ? nodeMap[dragState.nama] : null
  if (node) node.classList.remove('moving')
  dragState = null
}

document.addEventListener('pointerup', endPositionDrag)
document.addEventListener('pointercancel', endPositionDrag)

// ============ DRAG & DROP — atur hierarki via seret AVATAR node di bagan ============

// Hapus class drag dari semua node
function clearDragState() {
  dndWrapper.querySelectorAll('.org-node').forEach((n) => n.classList.remove('dragging', 'drag-over'))
  dndWrapper.classList.remove('drag-over-root')
}

dndWrapper.addEventListener('dragstart', (e) => {
  const node = e.target.closest('.org-node')
  if (!node || !isEditMode) return
  const nama = node.querySelector('.org-role').textContent
  e.dataTransfer.setData('text/plain', nama)
  e.dataTransfer.effectAllowed = 'move'
  node.classList.add('dragging')
})

dndWrapper.addEventListener('dragend', clearDragState)

dndWrapper.addEventListener('dragover', (e) => {
  if (!isEditMode) return
  // Wajib preventDefault biar drop event bisa di-trigger
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'

  const targetNode = e.target.closest('.org-node')
  dndWrapper.querySelectorAll('.org-node').forEach((n) => n.classList.remove('drag-over'))
  dndWrapper.classList.remove('drag-over-root')

  if (targetNode && !targetNode.classList.contains('dragging')) {
    targetNode.classList.add('drag-over')
  } else {
    dndWrapper.classList.add('drag-over-root')
  }
})

dndWrapper.addEventListener('dragleave', (e) => {
  const targetNode = e.target.closest('.org-node')
  if (targetNode) targetNode.classList.remove('drag-over')
})

dndWrapper.addEventListener('drop', (e) => {
  if (!isEditMode) return
  e.preventDefault()

  const draggedNama = e.dataTransfer.getData('text/plain')
  if (!draggedNama) return

  // Validasi: pastikan jabatan yang di-drag masih ada di master list
  const semuaJabatan = getNamaJabatan()
  if (!semuaJabatan.includes(draggedNama)) {
    showToast(`Jabatan "${draggedNama}" udah gak ada.`, 'fa-solid fa-triangle-exclamation')
    clearDragState()
    return
  }

  const hierarki = getHierarki()
  const targetNode = e.target.closest('.org-node')

  if (targetNode) {
    const targetNama = targetNode.querySelector('.org-role').textContent

    // Validasi: pastikan target ada di master list juga
    if (!semuaJabatan.includes(targetNama)) {
      clearDragState()
      return
    }

    // Gak bisa drop ke diri sendiri
    if (draggedNama === targetNama) {
      clearDragState()
      return
    }

    // Cegah circular dependency (gak bisa drop ke anak/cucu sendiri)
    if (isDescendant(hierarki, draggedNama, targetNama)) {
      showToast('Gak bisa: bakal bikin hierarki muter.', 'fa-solid fa-triangle-exclamation')
      clearDragState()
      return
    }

    hierarki[draggedNama] = targetNama
    simpanHierarki(hierarki)
    renderOrgChart()
    if (isEditMode) renderStrukturList()
    showToast(`"${draggedNama}" sekarang di bawah "${targetNama}"`)

    fetch('/kepengurusan/jabatan/parent', {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ nama: draggedNama, parent_nama: targetNama }),
    })
  } else {
    // Drop ke root area — jadi level teratas
    hierarki[draggedNama] = null
    simpanHierarki(hierarki)
    renderOrgChart()
    if (isEditMode) renderStrukturList()
    showToast(`"${draggedNama}" dipindah ke level teratas`)

    fetch('/kepengurusan/jabatan/parent', {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ nama: draggedNama, parent_nama: null }),
    })
  }

  clearDragState()
})
