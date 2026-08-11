document.addEventListener('DOMContentLoaded', function () {
  const pengumumanData = window.__PENGUMUMAN_DATA__ || []
  const pengumumanRoutes = window.__PENGUMUMAN_ROUTES__ || {}

  // --- 1. LOGIC MODAL DETAIL ---
  const modal = document.getElementById('detailModal')

  // Buka modal detail & isi data dari pengumuman yang diklik
  window.openModal = function (id) {
    const item = pengumumanData.find((p) => p.id === Number(id))
    if (!item) return

    document.getElementById('modalDetailTitle').textContent = item.judul
    document.getElementById('modalDetailSubtitle').textContent = `${item.tanggal}${item.kategori ? ' · ' + item.kategori : ''}`
    document.getElementById('modalDetailImgTitle').textContent = item.judul
    document.getElementById('modalDetailIsi').textContent = item.isi

    const tagsEl = document.getElementById('modalDetailTags')
    tagsEl.innerHTML = ''
    if (item.kategori) {
      const tag = document.createElement('span')
      tag.className = 'tag'
      tag.textContent = item.kategori
      tagsEl.appendChild(tag)
    }
    if (item.status) {
      const tag = document.createElement('span')
      tag.className = 'tag' + (item.status === 'Aktif' ? ' tag-penting' : '')
      tag.textContent = item.status
      tagsEl.appendChild(tag)
    }

    modal.classList.add('active')
    document.body.style.overflow = 'hidden'
  }

  // Fungsi untuk menutup modal (dipanggil dari HTML via onclick)
  window.closeModal = function () {
    modal.classList.remove('active')
    document.body.style.overflow = ''
  }

  // Menutup modal jika user klik di area luar modal (background hitam)
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      closeModal()
    }
  })

  // --- 2. LOGIC MODAL BUAT / EDIT PENGUMUMAN ---
  const formModal = document.getElementById('formModal')
  const formTitle = document.getElementById('formModalTitle')
  const formMethod = document.getElementById('formMethod')
  const pengumumanForm = document.getElementById('pengumumanForm')
  const btnBuat = document.getElementById('btnBuatPengumuman')

  // Reset form ke mode "buat"
  window.openCreateModal = function () {
    formTitle.textContent = 'Buat Pengumuman'
    formMethod.value = 'POST'
    pengumumanForm.action = pengumumanRoutes.store || '/pengumuman'
    document.getElementById('inputJudul').value = ''
    document.getElementById('inputKategori').value = 'Umum'
    document.getElementById('inputStatus').value = 'Aktif'
    document.getElementById('inputTanggal').value = new Date().toISOString().slice(0, 10)
    document.getElementById('inputIsi').value = ''
    document.getElementById('formSubmitBtn').innerHTML = '<i class="fa-solid fa-check"></i> Simpan Pengumuman'
    formModal.classList.add('active')
    document.body.style.overflow = 'hidden'
  }

  // Isi form dari data pengumuman yang mau diedit
  window.openEditModal = function (id) {
    const item = pengumumanData.find((p) => p.id === Number(id))
    if (!item) return

    formTitle.textContent = 'Edit Pengumuman'
    formMethod.value = 'PUT'
    pengumumanForm.action = (pengumumanRoutes.update || '/pengumuman/__ID__').replace('__ID__', id)
    document.getElementById('inputJudul').value = item.judul
    document.getElementById('inputKategori').value = item.kategori || 'Umum'
    document.getElementById('inputStatus').value = item.status
    document.getElementById('inputTanggal').value = item.tanggalRaw || item.tanggal
    document.getElementById('inputIsi').value = item.isi
    document.getElementById('formSubmitBtn').innerHTML = '<i class="fa-solid fa-check"></i> Simpan Perubahan'
    formModal.classList.add('active')
    document.body.style.overflow = 'hidden'
  }

  window.closeFormModal = function () {
    formModal.classList.remove('active')
    document.body.style.overflow = ''
  }

  btnBuat?.addEventListener('click', openCreateModal)
  formModal.addEventListener('click', function (e) {
    if (e.target === formModal) closeFormModal()
  })
  // --- 3. LOGIC KONFIRMASI HAPUS (pakai dialog global) ---
  window.openDeleteModal = function (id) {
    const item = pengumumanData.find((p) => p.id === Number(id))
    if (!item) return

    openConfirmDelete({
      title: 'Hapus Pengumuman?',
      message: `Yakin ingin menghapus pengumuman "${item.judul}"? Tindakan ini tidak bisa dibatalkan.`,
      onConfirm: async () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content
        const url = (pengumumanRoutes.destroy || '/pengumuman/__ID__').replace('__ID__', item.id)

        try {
          const res = await fetch(url, {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
          })
          if (!res.ok) throw new Error('gagal hapus')

          // Hapus dari data lokal
          const idx = pengumumanData.findIndex((p) => p.id === item.id)
          if (idx > -1) pengumumanData.splice(idx, 1)

          // Hapus elemen list dari DOM
          const itemEl = document.querySelector(`[data-pengumuman-id="${item.id}"]`)
          itemEl?.remove()

          // Update stat cards
          const updateStat = (id, val) => {
            const el = document.getElementById(id)
            if (el) el.textContent = val
          }
          updateStat('statTotal', pengumumanData.length)
          updateStat('statAktif', pengumumanData.filter((p) => p.status === 'Aktif').length)
          updateStat('statTerjadwal', pengumumanData.filter((p) => p.status === 'Terjadwal').length)
          updateStat('statArsip', pengumumanData.filter((p) => p.status === 'Arsip').length)

          showToast('Pengumuman berhasil dihapus.', 'fa-solid fa-trash')
        } catch (err) {
          showToast('Gagal menghapus pengumuman.', 'fa-solid fa-triangle-exclamation')
          throw err
        }
      },
    })
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeModal()
      closeFormModal()
    }
  })

  // --- 4. LOGIC SEARCH / FILTER ---
  const searchInput = document.getElementById('searchPengumuman')
  const kategoriSelect = document.getElementById('kategoriFilter')
  const statusSelect = document.getElementById('statusFilter')
  const resetBtn = document.getElementById('btnResetFilter')
  const listItems = document.querySelectorAll('.pengumuman-item')

  function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase()
    const kategori = kategoriSelect.value
    const status = statusSelect.value

    listItems.forEach((item) => {
      const title = item.querySelector('h4')?.textContent.toLowerCase() || ''
      const itemCategory = item.dataset.category || ''
      const itemStatus = item.dataset.status || ''

      const matchSearch = title.includes(searchTerm)
      const matchKategori = !kategori || itemCategory.includes(kategori)
      const matchStatus = !status || itemStatus.includes(status)

      item.style.display = matchSearch && matchKategori && matchStatus ? 'flex' : 'none'
    })
  }

  searchInput.addEventListener('input', applyFilters)
  kategoriSelect.addEventListener('change', applyFilters)
  statusSelect.addEventListener('change', applyFilters)

  // Reset semua filter
  resetBtn?.addEventListener('click', () => {
    searchInput.value = ''
    kategoriSelect.value = ''
    statusSelect.value = ''
    applyFilters()
  })

  // Filter via klik kartu statistik
  window.filterPengumuman = function (filter) {
    const key = String(filter).toLowerCase()
    listItems.forEach((item) => {
      const status = item.dataset.status || ''
      if (filter === 'semua') {
        item.style.display = 'flex'
      } else {
        item.style.display = status.includes(key) ? 'flex' : 'none'
      }
    })
  }
})
