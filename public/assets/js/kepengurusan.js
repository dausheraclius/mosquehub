const JABATAN_STORAGE_KEY = 'mosquehub-jabatan-list'
const PENEMPATAN_STORAGE_KEY = 'mosquehub-kepengurusan-penempatan'
const HIERARKI_STORAGE_KEY = 'mosquehub-kepengurusan-hierarki'

// --- Data dummy jamaah yang bisa dipilih ---
const daftarJamaah = [
  { nama: 'Ust. Daus Morgan', email: 'ust.daus@email.com', hp: '0812xxxxxxx' },
  { nama: 'Ust. Hakim', email: 'hakim.m@email.com', hp: '0813xxxxxxx' },
  { nama: 'M. Reza', email: 'reza.s@email.com', hp: '0814xxxxxxx' },
  { nama: 'Fatimah', email: 'fatimah@email.com', hp: '0815xxxxxxx' },
  { nama: 'S. Abdullah', email: 'abdullah@email.com', hp: '0816xxxxxxx' },
]

function getDefaultNamaJabatan() {
  return ['Ketua YMBPK', 'Wakil Ketua YMBPK', 'Sekretaris YMBPK', 'Bendahara YMBPK', 'Sie Pendidikan', 'Sie Pembangunan']
}

function getNamaJabatan() {
  const saved = localStorage.getItem(JABATAN_STORAGE_KEY)
  if (saved) {
    const parsed = JSON.parse(saved)
    if (Array.isArray(parsed)) return parsed
  }
  return getDefaultNamaJabatan()
}

// ============ HIERARKI (struktur atasan-bawahan) ============
// Format: { [namaJabatan]: namaAtasan | null }
function getHierarki() {
  const namaList = getNamaJabatan()
  const saved = localStorage.getItem(HIERARKI_STORAGE_KEY)
  let hierarki = saved ? JSON.parse(saved) : {}

  let changed = false

  // Buang entri jabatan yang udah dihapus dari master list (menu Umum)
  Object.keys(hierarki).forEach((nama) => {
    if (!namaList.includes(nama)) {
      delete hierarki[nama]
      changed = true
    }
  })

  // Self-healing: pastikan tiap nama jabatan yang ada di master list punya entri.
  // Jabatan baru otomatis jadi anak dari jabatan pertama (kecuali dia sendiri jabatan pertama).
  namaList.forEach((nama, idx) => {
    if (!(nama in hierarki)) {
      hierarki[nama] = idx === 0 ? null : namaList[0]
      changed = true
    }
  })

  // Bersihin referensi parent yang nganggur (parent-nya udah dihapus)
  namaList.forEach((nama) => {
    const parent = hierarki[nama]
    if (parent !== null && !namaList.includes(parent)) {
      hierarki[nama] = null
      changed = true
    }
  })

  if (changed) {
    simpanHierarki(hierarki)
    bersihkanPenempatan(namaList)
  }
  return hierarki
}

function bersihkanPenempatan(namaList) {
  const penempatan = getPenempatan()
  if (!penempatan) return
  let changed = false
  Object.keys(penempatan).forEach((nama) => {
    if (!namaList.includes(nama)) {
      delete penempatan[nama]
      changed = true
    }
  })
  if (changed) simpanPenempatan(penempatan)
}

function simpanHierarki(hierarki) {
  localStorage.setItem(HIERARKI_STORAGE_KEY, JSON.stringify(hierarki))
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
  const saved = localStorage.getItem(PENEMPATAN_STORAGE_KEY)
  if (saved) return JSON.parse(saved)
  return null
}

function simpanPenempatan(penempatan) {
  localStorage.setItem(PENEMPATAN_STORAGE_KEY, JSON.stringify(penempatan))
}

function bangunJabatanData() {
  const namaList = getNamaJabatan()
  let penempatan = getPenempatan()
  if (!penempatan) {
    penempatan = {}
    simpanPenempatan(penempatan)
  }

  return namaList.map((nama) => {
    const email = penempatan[nama] || null
    const person = email ? daftarJamaah.find((p) => p.email === email) || null : null
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
    sel.addEventListener('change', (e) => {
      const nama = e.target.dataset.nama
      const newParent = e.target.value === '' ? null : e.target.value
      const hierarki = getHierarki()
      hierarki[nama] = newParent
      simpanHierarki(hierarki)
      renderOrgChart()
      renderStrukturList() // re-render biar opsi disabled/selected ke-update
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
            <span class="jabatan-select-meta">${item.person.email} | ${item.person.hp}</span>
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
    const isSelected = currentItem.person?.email === p.email
    return `
      <div class="jabatan-dropdown-item ${isSelected ? 'selected' : ''}" data-email="${p.email}">
        <div class="jabatan-dropdown-avatar"><i class="fa-solid fa-user"></i></div>
        <div class="jabatan-dropdown-info">
          <span class="jabatan-dropdown-name">${p.nama}</span>
          <span class="jabatan-dropdown-meta">${p.email} | ${p.hp}</span>
        </div>
        ${isSelected ? '<i class="fa-solid fa-check" style="color:var(--color-green); margin-left:auto;"></i>' : ''}
      </div>
    `
  }).join('')

  itemsContainer.querySelectorAll('.jabatan-dropdown-item').forEach((el) => {
    el.addEventListener('click', () => {
      const email = el.dataset.email
      const person = daftarJamaah.find((p) => p.email === email)
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
  jabatanData.forEach((j) => { if (j.person) penempatan[j.nama] = j.person.email })
  simpanPenempatan(penempatan)
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

function confirmAddJabatan() {
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
  namaList.push(trimmed)
  localStorage.setItem(JABATAN_STORAGE_KEY, JSON.stringify(namaList))

  // Set hierarki: anak dari parentNama
  const hierarki = getHierarki()
  hierarki[trimmed] = parentNama
  simpanHierarki(hierarki)

  // Refresh data jabatanData
  jabatanData = bangunJabatanData()

  // Re-render semua
  renderOrgChart()
  renderJabatanList()
  if (isEditMode) renderStrukturList()

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

renderOrgChart()
renderJabatanList()

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
  } else {
    // Drop ke root area — jadi level teratas
    hierarki[draggedNama] = null
    simpanHierarki(hierarki)
    renderOrgChart()
    if (isEditMode) renderStrukturList()
    showToast(`"${draggedNama}" dipindah ke level teratas`)
  }

  clearDragState()
})