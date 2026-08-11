// profil-masjid.js
// Semua interaksi di halaman Profil Masjid. Belum nyambung ke backend/API
// beneran -- tujuannya cuma biar tombol2 keliatan 'hidup' pas dites.

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
  }

  function setEditing(isEditing) {
    fields.forEach((f) => (f.disabled = !isEditing))
    form.classList.toggle('is-editing', isEditing)
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

  saveBtn.addEventListener('click', () => {
    snapshot = getFormSnapshot()
    setEditing(false)
    showToast('Perubahan tersimpan (dummy, belum ke server)')
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

  // ---- management info: pilih pengurus dari data jamaah (dummy list di <option>) ----
  document.querySelectorAll('.mgmt-select-input').forEach((select) => {
    select.addEventListener('change', () => {
      showToast(`Pengurus diperbarui: ${select.selectedOptions[0].textContent}`)
    })
  })
}

document.addEventListener('DOMContentLoaded', initProfilMasjid)
