const daftarJamaah = window.__DAFTAR_JAMAAH__ || []

function personMeta(p) {
  return [p.email, p.hp].filter(Boolean).join(' | ')
}

function getNamaJabatan() {
  return window.__JABATAN_LIST__ || []
}

// ============ HIERARKI (struktur atasan-bawahan) ============
// Format: { [namaJabatan]: namaAtasan | null }
function getHierarki() {
  return window.__HIERARKI__ || {}
}

async function simpanHierarki(hierarki) {
  window.__HIERARKI__ = hierarki
  // Cari 1 pasangan nama+parent yang paling baru berubah dibanding sebelumnya udah susah dilacak di sini,
  // jadi kita kirim ulang tiap kali dipanggil dari titik yang emang ubah 1 jabatan aja (lihat 8f & drag-drop).
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
  await fetch('/kepengurusan/penempatan', {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify({ penempatan }),
  })
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

// ============ RENDER ORG CHART ============
function getChildrenOf(nama) {
  const hierarki = getHierarki()
  return Object.keys(hierarki).filter((n) => hierarki[n] === nama)
}

function hasChildren(nama) {
  return getChildrenOf(nama).length > 0
}

function personLabel(nama) {
  const item = jabatanData.find((j) => j.nama === nama)
  return item?.person?.nama || 'Belum Diisi'
}

function nodeBoxHtml(nama) {
  return `
    <div class="org-node" data-nama="${nama}">
      <div class="org-avatar" draggable="false"><i class="fa-solid fa-user"></i></div>
      <div class="org-node-info" draggable="false">
        <span class="org-role" draggable="false">${nama}</span>
        <span class="org-name" draggable="false">${personLabel(nama)}</span>
      </div>
      <button class="org-node-add org-node-add--side" data-nama="${nama}" draggable="false" title="Tambah jabatan anak"><i class="fa-solid fa-plus"></i></button>
      <button class="org-node-add org-node-add--bottom" data-nama="${nama}" draggable="false" title="Tambah jabatan anak"><i class="fa-solid fa-plus"></i></button>
    </div>
  `
}

function renderPairBox(members) {
  if (members.length === 0) return ''
  return `
    <div class="org-pair-box">
      ${members.map((n) => `
        <div class="org-pair-row">
          <div class="org-avatar" draggable="false"><i class="fa-solid fa-user"></i></div>
          <div class="org-node-info" draggable="false">
            <span class="org-role" draggable="false">${n}</span>
            <span class="org-name" draggable="false">${personLabel(n)}</span>
          </div>
        </div>
      `).join('')}
    </div>
  `
}

function renderDashedListBox(leafNames) {
  return `
    <div class="org-dashed-list-box">
      ${leafNames.map((n) => `
        <div class="org-dashed-list-item">
          <span class="org-dashed-list-name">${n}</span>
          <span class="org-dashed-list-person">${personLabel(n)}</span>
        </div>
      `).join('')}
    </div>
  `
}

function renderBelowHtml(nama) {
  const children = getChildrenOf(nama)
  if (children.length === 0) return ''

  const branchChildren = children.filter(hasChildren)
  const leafChildren = children.filter((c) => !hasChildren(c))

  // Semua anak leaf (gak punya anak lagi) -> 1 kotak list putus-putus
  if (branchChildren.length === 0) {
    return `
      <div class="org-connector-line"></div>
      ${renderDashedListBox(children)}
    `
  }

  // Ada campuran leaf + branch -> leaf dibelah 2 jadi lengan kiri-kanan, branch jadi baris di bawah
  let armsHtml = ''
  if (leafChildren.length > 0) {
    const half = Math.ceil(leafChildren.length / 2)
    const leftMembers = leafChildren.slice(0, half)
    const rightMembers = leafChildren.slice(half)
    armsHtml = `
      <div class="org-connector-line"></div>
      <div class="org-arms-row">
        <div class="org-arms-side">${renderPairBox(leftMembers)}</div>
        <div class="org-arms-spacer"></div>
        <div class="org-arms-side">${renderPairBox(rightMembers)}</div>
      </div>
    `
  }

  const branchHtml = `
    <div class="org-connector-line"></div>
    <div class="org-children-row">
      ${branchChildren.map((c) => `<div class="org-node-wrapper">${nodeBoxHtml(c)}${renderBelowHtml(c)}</div>`).join('')}
    </div>
  `

  return armsHtml + branchHtml
}

function syncDragAttrs() {
  document.querySelectorAll('.org-node').forEach((node) => {
    node.draggable = isEditMode
  })
  const hint = document.querySelector('.org-drop-hint')
  if (hint) hint.style.display = isEditMode ? 'block' : 'none'
}

function renderOrgChart() {
  const hierarki = getHierarki()
  const roots = Object.keys(hierarki).filter((nama) => hierarki[nama] === null)
  const wrapper = document.getElementById('orgChartWrapper')

  if (roots.length === 0) {
    // Safety fallback: jangan sampe chart ilang semua. Coba pake jabatan pertama sebagai root.
    const namaList = getNamaJabatan()
    if (namaList.length > 0) {
      hierarki[namaList[0]] = null
      simpanHierarki(hierarki)
      // Panggil renderOrgChart lagi — rekursif sekali aja biar aman
      renderOrgChart()
    } else {
      wrapper.innerHTML = `<p style="text-align:center;color:var(--text-muted);font-size:12.5px;padding:30px 0;">Belum ada jabatan — tambah dulu dari menu <strong>Umum → Jabatan</strong>.</p>`
    }
    return
  }

  wrapper.innerHTML = `
    <div class="org-tree-root${isEditMode ? ' is-editing' : ''}">${roots
      .map((nama) => `<div class="org-node-wrapper">${nodeBoxHtml(nama)}${renderBelowHtml(nama)}</div>`)
      .join('<div style="width:20px;"></div>')}</div>
    <div class="org-drop-hint"><i class="fa-solid fa-arrows-up-down"></i> Seret jabatan ke node lain untuk ubah hierarki</div>
  `
  syncDragAttrs()
  syncAddButtons()
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
        return `<option value="${n}" ${selected} ${disabled}>${n}</option>`
      })
      .join('')

    return `
      <div class="struktur-row">
        <span class="struktur-row-name">${nama}</span>
        <select class="struktur-select" data-nama="${nama}">
          <option value="" ${hierarki[nama] === null ? 'selected' : ''}>— Level Teratas —</option>
          ${options}
        </select>
      </div>
    `
  }).join('')

  container.querySelectorAll('.struktur-select').forEach((sel) => {
    sel.addEventListener('change', async (e) => {
      const nama = e.target.dataset.nama
      const newParent = e.target.value === '' ? null : e.target.value
      const hierarki = getHierarki()
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
          <span class="jabatan-name">${item.nama}</span>
          <span class="status-badge ${isEmpty ? 'status-empty' : 'status-filled'}">${namaOrang}</span>
        </div>
      `)
      return
    }

    const triggerContent = item.person
      ? `
        <div class="jabatan-select-person">
          <div class="jabatan-select-avatar"><i class="fa-solid fa-user"></i></div>
          <div class="jabatan-select-info">
            <span class="jabatan-select-name">${item.person.nama}</span>
            <span class="jabatan-select-meta">${personMeta(item.person)}</span>
          </div>
        </div>
      `
      : `<span class="jabatan-select-placeholder">Pilih Jemaah</span>`

    container.insertAdjacentHTML('beforeend', `
      <div class="jabatan-row">
        <span class="jabatan-name">${item.nama}</span>
        <div class="jabatan-select" data-nama="${item.nama}">
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
          <span class="jabatan-dropdown-name">${p.nama}</span>
          <span class="jabatan-dropdown-meta">${personMeta(p)}</span>
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
  syncDragAttrs()
  syncAddButtons()
  renderJabatanList()
})

document.getElementById('resetBtn').addEventListener('click', () => {
  if (confirm('Reset semua perubahan yang belum disimpan?')) {
    jabatanData = bangunJabatanData()
    renderJabatanList()
    renderOrgChart()
    renderStrukturList()
  }
})

document.getElementById('simpanBtn').addEventListener('click', () => {
  const penempatan = {}
  jabatanData.forEach((j) => { if (j.person) penempatan[j.nama] = j.person.id })
  simpanPenempatan(penempatan)
  updateRingkasan()
  showToast('Perubahan kepengurusan berhasil disimpan.')
})

// ============ TOMBOL + TAMBAH JABATAN ANAK ============
const dndWrapper = document.getElementById('orgChartWrapper')

function syncAddButtons() {
  document.querySelectorAll('.org-node-add').forEach((btn) => {
    btn.classList.toggle('visible', isEditMode)
  })
  // Toggle class is-editing di tree root
  const root = document.querySelector('.org-tree-root')
  if (root) root.classList.toggle('is-editing', isEditMode)
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

// ============ DRAG & DROP — atur hierarki via seret node di bagan ============

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