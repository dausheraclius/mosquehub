// user-management.js
// Semua interaksi masih dummy (belum ke backend), buat demo tampilan aja.

function initUserManagement() {
  const overlay = document.getElementById('userModalOverlay')
  const modalTitle = document.getElementById('userModalTitle')
  const roleSelect = document.getElementById('userRole')
  const customRoleField = document.getElementById('customRoleField')

  function openModal(title) {
    modalTitle.textContent = title
    document.getElementById('userName').value = ''
    document.getElementById('userContact').value = ''
    document.getElementById('customRoleName').value = ''
    roleSelect.value = 'Petugas Zakat'
    customRoleField.style.display = 'none'
    overlay.classList.add('show')
  }
  function closeModal() {
    overlay.classList.remove('show')
  }

  document.getElementById('btnTambahUser').addEventListener('click', () => openModal('Tambah User'))
  document.querySelectorAll('.btn-edit-user').forEach((btn) => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr')
      const nama = row.children[2].textContent.trim()
      openModal(`Edit Pengguna - ${nama}`)
    })
  })

  document.getElementById('modalCloseBtn').addEventListener('click', closeModal)
  document.getElementById('modalCancelBtn').addEventListener('click', closeModal)
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal()
  })

  // ---- role custom: munculin input nama role baru kalo pilih "+ Buat Role Baru" ----
  roleSelect.addEventListener('change', () => {
    customRoleField.style.display = roleSelect.value === '__custom__' ? 'flex' : 'none'
  })

  // ---- checklist akses menu: centang parent -> ikut kecentang semua child ----
  document.querySelectorAll('.group-toggle').forEach((parent) => {
    const group = parent.dataset.group
    const children = document.querySelectorAll(`[data-children-of="${group}"] .access-check`)

    parent.addEventListener('change', () => {
      children.forEach((c) => (c.checked = parent.checked))
    })

    children.forEach((child) => {
      child.addEventListener('change', () => {
        const checkedCount = Array.from(children).filter((c) => c.checked).length
        parent.checked = checkedCount === children.length
        parent.indeterminate = checkedCount > 0 && checkedCount < children.length
      })
    })
  })

  // ---- simpan user (dummy) ----
  document.getElementById('modalSaveBtn').addEventListener('click', () => {
    const nama = document.getElementById('userName').value.trim()
    if (!nama) {
      showToast('Isi nama dulu ya', 'fa-solid fa-triangle-exclamation')
      return
    }
    showToast(`User "${nama}" tersimpan (dummy, belum ke server)`)
    closeModal()
  })

  // ---- toggle status aktif/nonaktif di tabel ----
  document.querySelectorAll('.status-toggle').forEach((toggle) => {
    toggle.addEventListener('change', () => {
      const row = toggle.closest('tr')
      const nama = row.children[2].textContent.trim()
      showToast(toggle.checked ? `${nama} diaktifin` : `${nama} dinonaktifin`)
    })
  })

  // ---- reset password (dummy) ----
  document.querySelectorAll('.btn-reset-password').forEach((btn) => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr')
      const email = row.children[3].textContent.trim()
      showToast(`Link reset password dikirim ke ${email} (dummy)`, 'fa-solid fa-envelope')
    })
  })

  // ---- search filter (client-side, beneran jalan) ----
  document.getElementById('userSearch').addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase()
    document.querySelectorAll('#userTableBody tr').forEach((row) => {
      const nama = row.children[2].textContent.toLowerCase()
      const email = row.children[3].textContent.toLowerCase()
      row.style.display = nama.includes(q) || email.includes(q) ? '' : 'none'
    })
  })
}

document.addEventListener('DOMContentLoaded', initUserManagement)
