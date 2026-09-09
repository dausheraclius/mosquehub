// profil-masjid.js
// Semua interaksi halaman Profil Masjid, termasuk penyimpanan profil dan media
// melalui endpoint backend.

function initProfilMasjid() {
  const form = document.getElementById('profilForm')
  const editBtn = document.getElementById('btnEditProfile')
  const cancelBtn = document.getElementById('btnCancel')
  const resetBtn = document.getElementById('btnReset')
  const saveBtn = document.getElementById('btnSaveChanges')
  const fields = form.querySelectorAll('input, select, textarea')

  // simpan snapshot awal, dipake buat "Cancel"
  let snapshot = getFormSnapshot()

  function getFormSnapshot() {
    const data = {}
    fields.forEach((f) => (data[f.id || f.name] = f.value))
    return data
  }

  function applySnapshot(data) {
    fields.forEach((f) => {
      const key = f.id || f.name
      if (key in data) f.value = data[key]
    })
    if (typeof syncCustomSelects === 'function') syncCustomSelects()
  }

  function setEditing(isEditing) {
    fields.forEach((f) => (f.disabled = !isEditing))
    form.classList.toggle('is-editing', isEditing)
    if (typeof syncCustomSelects === 'function') syncCustomSelects()
    editBtn.innerHTML = isEditing
      ? '<i class="fa-solid fa-xmark"></i> Batal Edit'
      : '<i class="fa-solid fa-pen"></i> Edit Profil'
  }

  // mulai dalam mode "view" (semua field disabled)
  setEditing(false)

  editBtn.addEventListener('click', () => {
    const nowEditing = !form.classList.contains('is-editing')
    if (nowEditing) snapshot = getFormSnapshot()
    setEditing(nowEditing)
  })

  cancelBtn.addEventListener('click', () => {
    applySnapshot(snapshot)
    setEditing(false)
    showToast('Perubahan dibatalkan', 'fa-solid fa-rotate-left')
  })

  resetBtn.addEventListener('click', () => {
    if (!confirm('Kosongin semua isian di form ini?')) return
    fields.forEach((f) => {
      if (f.tagName === 'SELECT') f.selectedIndex = 0
      else f.value = ''
    })
    showToast('Form dikosongin', 'fa-solid fa-eraser')
  })

  saveBtn.addEventListener('click', async () => {
    const formData = new FormData(form)

    try {
      const res = await fetch(adminUrl('/pengaturan/profil-masjid'), {
        method: 'POST',
        headers: getHeaders({ multipart: true }),
        body: formData,
      })
      if (!res.ok) {
        showToast('Gagal menyimpan, cek isian form.', 'fa-solid fa-triangle-exclamation')
        return
      }
      snapshot = getFormSnapshot()
      setEditing(false)
      updateNamaTampil(formData.get('name'), formData.get('short_name'))

      // Kalau ada logo baru dipilih, tampilkan langsung di header tanpa reload
      const logoFile = document.getElementById('logoInput').files[0]
      if (logoFile) setHeaderLogoImage(URL.createObjectURL(logoFile))

      showToast('Perubahan tersimpan.')
    } catch (err) {
      showToast('Terjadi kesalahan saat menyimpan.', 'fa-solid fa-triangle-exclamation')
    }
  })

  // Perbarui nama masjid di header & banner halaman ini tanpa reload.
  function updateNamaTampil(nama, shortName) {
    const namaStr = (nama || '').toString().trim()
    const shortStr = (shortName || '').toString().trim()

    // Nama di header (semua halaman admin)
    const headerName = document.querySelector('.header-institution-name')
    if (headerName && namaStr) headerName.textContent = namaStr

    // Nama + nama singkat di banner halaman Profil Masjid
    const bannerName = document.querySelector('.profile-banner-name')
    if (bannerName && namaStr) {
      const textNode = bannerName.childNodes[0]
      if (textNode && textNode.nodeType === Node.TEXT_NODE) textNode.nodeValue = namaStr
    }
    const shortLine = document.querySelector('.profile-banner-line')
    if (shortLine) shortLine.textContent = shortStr || '-'
  }

  // ---- logo header: tampilkan gambar / kembalikan ke ikon default ----
  function setHeaderLogoImage(src) {
    const wrap = document.getElementById('headerInstitutionLogo')
    if (!wrap) return
    wrap.innerHTML = `<img src="${src}" alt="Logo Masjid">`
  }

  function setHeaderLogoIcon() {
    const wrap = document.getElementById('headerInstitutionLogo')
    if (!wrap) return
    wrap.innerHTML = '<i class="fa-solid fa-mosque"></i>'
  }

  // Hapus logo: kembali ke ikon default di kotak logo & header
  const hapusLogoBtn = document.getElementById('btnHapusLogo')
  hapusLogoBtn?.addEventListener('click', () => {
    openConfirmDelete({
      title: 'Hapus Logo?',
      message: 'Yakin ingin menghapus logo masjid? Tampilan akan kembali ke ikon default.',
      confirmText: 'Hapus Logo',
      onConfirm: async () => {
        try {
          const res = await fetch(adminUrl('/pengaturan/profil-masjid/logo'), {
            method: 'DELETE',
            headers: getHeaders(),
          })
          if (!res.ok) throw new Error('gagal hapus logo')

          const logoBox = document.getElementById('logoBox')
          if (logoBox) logoBox.innerHTML = '<i class="fa-solid fa-mosque"></i>'
          setHeaderLogoIcon()
          document.getElementById('logoInput').value = ''
          hapusLogoBtn.remove()
          showToast('Logo dihapus, kembali ke ikon default.', 'fa-solid fa-trash')
        } catch (err) {
          showToast('Gagal menghapus logo.', 'fa-solid fa-triangle-exclamation')
        }
      },
    })
  })

  // ---- upload logo: klik box -> pilih gambar -> preview ----
  const logoInput = document.getElementById('logoInput')
  const logoBox = document.getElementById('logoBox')
  logoBox.addEventListener('click', () => logoInput.click())
  logoInput.addEventListener('change', () => {
    const file = logoInput.files[0]
    if (!file) return
    const url = URL.createObjectURL(file)
    logoBox.innerHTML = `<img src="${url}" alt="Logo Masjid">`
    showToast('Logo diganti (preview lokal doang)', 'fa-solid fa-image')
  })

  // ---- official identity boxes (stempel/kop surat/ttd): sama, klik = upload ----
  document.querySelectorAll('.identity-box[data-upload]').forEach((box) => {
    const input = box.querySelector('input[type=file]')
    box.addEventListener('click', () => input.click())
    input.addEventListener('change', () => {
      const file = input.files[0]
      if (!file) return
      if (file.type.startsWith('image/')) {
        const url = URL.createObjectURL(file)
        box.innerHTML = `<img src="${url}" alt="Dokumen" style="width:100%;height:100%;object-fit:contain">`
      } else {
        const icon = file.type === 'application/pdf' ? 'fa-file-pdf' : 'fa-file-lines'
        const label = file.name.length > 20 ? file.name.slice(0, 17) + '...' : file.name
        box.innerHTML = `
          <div style="display:flex;flex-direction:column;align-items:center;gap:4px;font-size:12px;color:var(--text-muted)">
            <i class="fa-regular ${icon}" style="font-size:20px"></i>
            <span>${label}</span>
          </div>
        `
      }
      showToast('File tersimpan di preview')
    })
  })

}

document.addEventListener('DOMContentLoaded', initProfilMasjid)
