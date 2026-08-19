// ============================
// DATA JAMAAH - Logic Halaman
// ============================

// Data jemaah diambil dari backend (window.__JAMAAH_DATA__)
const dataJamaah = window.__JAMAAH_DATA__ || []

const tableBody = document.getElementById('jamaahTableBody')
const paginationInfo = document.getElementById('paginationInfo')
const pageNumbers = document.getElementById('pageNumbers')
const prevPageBtn = document.getElementById('prevPageBtn')
const nextPageBtn = document.getElementById('nextPageBtn')

const PAGE_SIZE = 10
let currentPage = 1
let currentData = [...dataJamaah]
let editingJamaahId = null

// Peta kelas badge status
function statusClassOf(status) {
  switch (status) {
    case 'Aktif':
      return 'status-aktif'
    case 'Tidak Aktif':
      return 'status-nonaktif'
    case 'Pindah':
      return 'status-pindah'
    case 'Wafat':
      return 'status-wafat'
    default:
      return ''
  }
}

// Render baris ke tabel (hanya untuk halaman aktif)
function renderTable(data) {
  tableBody.innerHTML = ''

  if (data.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Data tidak ditemukan</td></tr>`
    return
  }

  const start = (currentPage - 1) * PAGE_SIZE
  const pageItems = data.slice(start, start + PAGE_SIZE)

  pageItems.forEach((item) => {
    const statusClass = statusClassOf(item.status)
    const avatar = item.foto
      ? `<img src="${esc(item.foto)}" alt="Foto ${esc(item.nama)}">`
      : `<i class="fa-solid fa-user"></i>`

    const row = `
      <tr>
        <td><div class="table-avatar-jamaah">${avatar}</div></td>
        <td><span class="jamaah-name">${esc(item.nama)}</span></td>
        <td>${esc(item.email)}</td>
        <td>${esc(item.hp)}</td>
        <td>${esc(item.gender)}</td>
        <td><span class="status-badge ${statusClass}">${esc(item.status)}</span></td>
        <td>
          <div class="table-actions">
            <button class="icon-action-btn btn-detail-jamaah" data-id="${item.id}" title="Lihat Detail"><i class="fa-regular fa-eye"></i></button>
            <button class="icon-action-btn edit btn-edit-jamaah" data-id="${item.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <button class="icon-action-btn hapus btn-delete-jamaah" data-id="${item.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `
    tableBody.insertAdjacentHTML('beforeend', row)
  })

  renderPagination(data.length)
}

// Hitung & tampilkan statistik kartu dari data terkini
function renderStats() {
  const total = dataJamaah.length
  const laki = dataJamaah.filter((d) => d.gender.startsWith('Laki-laki')).length
  const perempuan = dataJamaah.filter((d) => d.gender.startsWith('Perempuan')).length
  const aktif = dataJamaah.filter((d) => d.status === 'Aktif').length

  document.getElementById('statTotal').textContent = total.toLocaleString('id-ID')
  document.getElementById('statLaki').textContent = laki.toLocaleString('id-ID')
  document.getElementById('statPerempuan').textContent = perempuan.toLocaleString('id-ID')
  document.getElementById('statAktif').textContent = aktif.toLocaleString('id-ID')
}

// Render kontrol pagination berdasarkan jumlah data hasil filter
function renderPagination(totalItems) {
  const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE))

  if (currentPage > totalPages) currentPage = totalPages

  const start = totalItems === 0 ? 0 : (currentPage - 1) * PAGE_SIZE + 1
  const end = Math.min(currentPage * PAGE_SIZE, totalItems)
  paginationInfo.textContent = `Menampilkan ${start}-${end} dari ${totalItems} data`

  pageNumbers.innerHTML = ''
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button')
    btn.className = 'pagination-btn' + (i === currentPage ? ' active' : '')
    btn.type = 'button'
    btn.textContent = i
    btn.addEventListener('click', () => {
      currentPage = i
      renderTable(currentData)
    })
    pageNumbers.appendChild(btn)
  }

  prevPageBtn.disabled = currentPage === 1
  nextPageBtn.disabled = currentPage === totalPages
}

// Filter + sort gabungan: search + status + jenis kelamin + urutkan
function applyFilter() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const status = document.getElementById('statusFilter').value
  const gender = document.getElementById('genderFilter').value
  const sort = document.getElementById('sortFilter').value

  let filtered = dataJamaah.filter((item) => {
    const matchSearch = item.nama.toLowerCase().includes(keyword)
    const matchStatus = status === '' || item.status === status
    const matchGender = gender === '' || item.gender.startsWith(gender)
    return matchSearch && matchStatus && matchGender
  })

  filtered.sort((a, b) => {
    switch (sort) {
      case 'nama-desc':
        return b.nama.localeCompare(a.nama)
      case 'bergabung-baru':
        return (b.tanggalBergabungIso || '').localeCompare(a.tanggalBergabungIso || '')
      case 'bergabung-lama':
        return (a.tanggalBergabungIso || '').localeCompare(b.tanggalBergabungIso || '')
      case 'nama-asc':
      default:
        return a.nama.localeCompare(b.nama)
    }
  })

  currentData = filtered
  currentPage = 1
  renderTable(currentData)
  renderStats()
}

// Pasang event listener filter
document.getElementById('searchInput').addEventListener('input', applyFilter)
document.getElementById('statusFilter').addEventListener('change', applyFilter)
document.getElementById('genderFilter').addEventListener('change', applyFilter)
document.getElementById('sortFilter').addEventListener('change', applyFilter)

// Navigasi pagination Previous / Next
prevPageBtn.addEventListener('click', () => {
  if (currentPage > 1) {
    currentPage--
    renderTable(currentData)
  }
})

nextPageBtn.addEventListener('click', () => {
  const totalPages = Math.max(1, Math.ceil(currentData.length / PAGE_SIZE))
  if (currentPage < totalPages) {
    currentPage++
    renderTable(currentData)
  }
})

// ============================
// DETAIL JAMAAH MODAL
// ============================
const detailModalOverlay = document.getElementById('detailModalOverlay')

function openDetailModal(id) {
  const item = dataJamaah.find((d) => d.id === Number(id))
  if (!item) return

  document.getElementById('detailName').textContent = item.nama

  const badge = document.getElementById('detailStatusBadge')
  badge.textContent = item.status
  badge.className = 'status-badge ' + statusClassOf(item.status)

  document.getElementById('detailGender').textContent = item.gender
  document.getElementById('detailTtl').textContent = `${item.tempatLahir}, ${item.tanggalLahir}`
  document.getElementById('detailHp').textContent = item.hp
  document.getElementById('detailEmail').textContent = item.email
  document.getElementById('detailAlamat').textContent = item.alamat
  document.getElementById('detailPekerjaan').textContent = item.pekerjaan
  document.getElementById('detailNikah').textContent = item.statusPernikahan
  document.getElementById('detailBergabung').textContent = item.tanggalBergabung
  document.getElementById('detailCatatan').textContent =
    item.catatan && item.catatan !== '-' ? item.catatan : 'Tidak ada catatan tambahan.'

  const detailAvatar = document.getElementById('detailAvatar')
  detailAvatar.innerHTML = item.foto
    ? `<img src="${item.foto}" alt="Foto ${item.nama}">`
    : `<i class="fa-solid fa-user"></i>`

  document.getElementById('openEditFromDetailBtn').dataset.id = item.id

  detailModalOverlay.classList.add('active')
}

function closeDetailModal() {
  detailModalOverlay.classList.remove('active')
}

// Event delegation untuk tombol Detail/Edit/Hapus di tabel (baris dirender ulang tiap filter/pagination)
tableBody.addEventListener('click', (e) => {
  const detailBtn = e.target.closest('.btn-detail-jamaah')
  if (detailBtn) {
    openDetailModal(detailBtn.dataset.id)
    return
  }
  const editBtn = e.target.closest('.btn-edit-jamaah')
  if (editBtn) {
    openEditModal(editBtn.dataset.id)
    return
  }
  const deleteBtn = e.target.closest('.btn-delete-jamaah')
  if (deleteBtn) {
    openDeleteModal(deleteBtn.dataset.id)
    return
  }
})

document.getElementById('closeDetailModalX').addEventListener('click', closeDetailModal)
document.getElementById('closeDetailModalBtn').addEventListener('click', closeDetailModal)

document.getElementById('openEditFromDetailBtn').addEventListener('click', (e) => {
  const id = e.currentTarget.dataset.id
  if (!id) return
  closeDetailModal()
  openEditModal(id)
})

detailModalOverlay.addEventListener('click', (e) => {
  if (e.target === detailModalOverlay) closeDetailModal()
})

// ============================
// HAPUS JAMAAH (pakai dialog konfirmasi global)
// ============================
function openDeleteModal(id) {
  const item = dataJamaah.find((d) => d.id === Number(id))
  if (!item) return

  openConfirmDelete({
    title: 'Hapus Jemaah?',
    message: `Yakin ingin menghapus jemaah "${item.nama}"? Tindakan ini tidak bisa dibatalkan.`,
    onConfirm: async () => {

      try {
        const res = await fetch(`/data-jamaah/${item.id}`, {
          method: 'DELETE',
          headers: getHeaders(),
        })
        if (!res.ok) throw new Error('gagal hapus jemaah')

        const idx = dataJamaah.findIndex((d) => d.id === item.id)
        if (idx > -1) dataJamaah.splice(idx, 1)
        applyFilter()
        showToast('Jemaah berhasil dihapus.', 'fa-solid fa-trash')
      } catch (err) {
        showToast('Gagal menghapus jemaah.', 'fa-solid fa-triangle-exclamation')
        throw err
      }
    },
  })
}

// ============================
// TAMBAH JAMAAH MODAL (FORM) - Tersambung ke backend
// ============================
const addModalOverlay = document.getElementById('addModalOverlay')
const addJamaahForm = document.getElementById('addJamaahForm')
const avatarUploadInput = document.getElementById('avatarUploadInput')
const avatarUploadPreview = document.getElementById('avatarUploadPreview')

function openAddModal() {
  editingJamaahId = null
  document.getElementById('addModalTitle').textContent = 'Tambah Jemaah Baru'
  document.getElementById('addModalSubmitBtn').innerHTML = '<i class="fa-solid fa-check"></i> Simpan Jemaah'
  avatarUploadPreview.innerHTML = `<i class="fa-solid fa-user"></i>`
  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  document.body.classList.add('modal-open')
  addModalOverlay.classList.add('active')
}

function openEditModal(id) {
  const item = dataJamaah.find((d) => d.id === Number(id))
  if (!item) return

  editingJamaahId = Number(id)
  document.getElementById('addModalTitle').textContent = 'Edit Jemaah'
  document.getElementById('addModalSubmitBtn').innerHTML = '<i class="fa-solid fa-check"></i> Simpan Perubahan'

  document.getElementById('addNama').value = item.nama
  document.getElementById('addGender').value = item.gender.startsWith('Laki-laki') ? 'Laki-laki' : 'Perempuan'
  document.getElementById('addTempatLahir').value = item.tempatLahir || ''
  document.getElementById('addTanggalLahir').value = item.tanggalLahirIso || ''
  document.getElementById('addHp').value = item.hp || ''
  document.getElementById('addEmail').value = item.email || ''
  document.getElementById('addAlamat').value = item.alamat || ''
  document.getElementById('addPekerjaan').value = item.pekerjaan || ''
  document.getElementById('addNikah').value = item.statusPernikahan || 'Belum Menikah'
  document.getElementById('addStatusJamaah').value = item.status || 'Aktif'
  document.getElementById('addTanggalBergabung').value = item.tanggalBergabungIso || ''
  document.getElementById('addCatatan').value = item.catatan && item.catatan !== '-' ? item.catatan : ''

  avatarUploadPreview.innerHTML = item.foto
    ? `<img src="${esc(item.foto)}" alt="Foto ${esc(item.nama)}">`
    : `<i class="fa-solid fa-user"></i>`

  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  document.body.classList.add('modal-open')
  addModalOverlay.classList.add('active')
}

function closeAddModal() {
  addModalOverlay.classList.remove('active')
  document.body.classList.remove('modal-open')
  addJamaahForm.reset()
  avatarUploadPreview.innerHTML = `<i class="fa-solid fa-user"></i>`
}

document.getElementById('openAddJamaahBtn').addEventListener('click', openAddModal)
document.getElementById('closeAddModalX').addEventListener('click', closeAddModal)
document.getElementById('cancelAddModalBtn').addEventListener('click', closeAddModal)

addModalOverlay.addEventListener('click', (e) => {
  if (e.target === addModalOverlay) closeAddModal()
})

// Preview foto profil langsung di browser (tanpa upload ke server)
avatarUploadInput.addEventListener('change', (e) => {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (ev) => {
    avatarUploadPreview.innerHTML = `<img src="${ev.target.result}" alt="Preview foto">`
  }
  reader.readAsDataURL(file)
})

// Submit form: create (POST) atau update (_method PUT) ke database via AJAX,
// pakai FormData biar foto profil ikut terupload.
addJamaahForm.addEventListener('submit', async (e) => {
  e.preventDefault()

  const wasEditing = !!editingJamaahId
  const url = editingJamaahId ? `/data-jamaah/${editingJamaahId}` : '/data-jamaah'

  const formData = new FormData()
  formData.append('nama', document.getElementById('addNama').value)
  formData.append('jenis_kelamin', document.getElementById('addGender').value)
  formData.append('tempat_lahir', document.getElementById('addTempatLahir').value)
  formData.append('tanggal_lahir', document.getElementById('addTanggalLahir').value)
  formData.append('no_hp', document.getElementById('addHp').value)
  formData.append('email', document.getElementById('addEmail').value)
  formData.append('alamat', document.getElementById('addAlamat').value)
  formData.append('pekerjaan', document.getElementById('addPekerjaan').value)
  formData.append('status_pernikahan', document.getElementById('addNikah').value)
  formData.append('status_jamaah', document.getElementById('addStatusJamaah').value)
  formData.append('tanggal_bergabung', document.getElementById('addTanggalBergabung').value)
  formData.append('catatan', document.getElementById('addCatatan').value)

  if (wasEditing) formData.append('_method', 'PUT')

  if (avatarUploadInput.files[0]) formData.append('foto', avatarUploadInput.files[0])

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
      },
      body: formData,
    })

    if (!res.ok) {
      await showFetchError(res, 'Gagal menyimpan data.')
      return
    }

    const saved = await res.json()
    if (wasEditing) {
      const idx = dataJamaah.findIndex((d) => d.id === editingJamaahId)
      if (idx > -1) dataJamaah[idx] = saved
    } else {
      dataJamaah.push(saved)
    }
    editingJamaahId = null
    applyFilter()
    closeAddModal()
    showToast(wasEditing ? 'Jemaah berhasil diperbarui.' : 'Jemaah berhasil ditambahkan.')
  } catch (err) {
    showToast('Terjadi kesalahan saat menyimpan data.', 'fa-solid fa-triangle-exclamation')
  }
})
// Escape menutup modal manapun yang sedang aktif
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return
  if (addModalOverlay.classList.contains('active')) closeAddModal()
  if (detailModalOverlay.classList.contains('active')) closeDetailModal()
})

// Tombol Impor / Ekspor
const importJamaahInput = document.getElementById('importJamaahInput')

document.getElementById('exportBtn').addEventListener('click', () => {
  const rows = [
    ['Nama', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'No HP', 'Email', 'Alamat', 'Pekerjaan', 'Status Pernikahan', 'Status Jemaah', 'Tanggal Bergabung', 'Catatan'],
  ]
  dataJamaah.forEach((item) => {
    rows.push([
      item.nama,
      item.gender,
      item.tempatLahir || '',
      item.tanggalLahirIso || '',
      item.hp || '',
      item.email || '',
      item.alamat || '',
      item.pekerjaan || '',
      item.statusPernikahan || '',
      item.status,
      item.tanggalBergabungIso || '',
      item.catatan && item.catatan !== '-' ? item.catatan : '',
    ])
  })

  const csv = rows
    .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
    .join('\n')
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `data-jemaah-${new Date().toISOString().slice(0, 10)}.csv`
  link.click()
  URL.revokeObjectURL(link.href)
  showToast('Data jemaah diekspor ke CSV.', 'fa-solid fa-download')
})

document.getElementById('importBtn').addEventListener('click', () => importJamaahInput.click())

importJamaahInput.addEventListener('change', async () => {
  const file = importJamaahInput.files[0]
  if (!file) return

  const fd = new FormData()
  fd.append('file', file)

  try {
    const res = await fetch('/data-jamaah/impor', {
      method: 'POST',
      headers: getHeaders({ multipart: true }),
      body: fd,
    })
    const data = await res.json().catch(() => ({}))
    if (!res.ok) {
      showToast(data.message || 'Gagal mengimpor file.', 'fa-solid fa-triangle-exclamation')
      return
    }
    const skipCount = (data.skipped || []).length
    showToast(
      skipCount ? `${data.imported} jemaah diimpor, ${skipCount} dilewati.` : `${data.imported} jemaah berhasil diimpor.`,
      'fa-solid fa-file-import',
    )
    if (data.imported > 0) setTimeout(() => location.reload(), 900)
  } catch (err) {
    showToast('Terjadi kesalahan saat impor.', 'fa-solid fa-triangle-exclamation')
  } finally {
    importJamaahInput.value = ''
  }
})

// Render pertama kali pas halaman dibuka
renderTable(currentData)
renderStats()
