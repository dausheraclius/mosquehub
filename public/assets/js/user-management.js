function initUserManagement() {
  const overlay = document.getElementById('userModalOverlay')
  const modalTitle = document.getElementById('userModalTitle')
  const roleSelect = document.getElementById('userRole')
  const customRoleField = document.getElementById('customRoleField')

  let editingId = null
  let editingRole = null

  function openModal(title) {
    modalTitle.textContent = title
    document.getElementById('userName').value = ''
    document.getElementById('userEmail').value = ''
    document.getElementById('userPhone').value = ''
    document.getElementById('userPassword').value = ''
    document.getElementById('customRoleName').value = ''
    roleSelect.value = 'Ketua YMBPK'
    customRoleField.style.display = 'none'
    document.getElementById('passwordField').style.display = ''
    document.getElementById('userStatusToggle').checked = true
    resetAccessChecklist()
    if (typeof syncCustomSelects === 'function') syncCustomSelects()
    overlay.classList.add('show')
    editingId = null
  }
  function closeModal() {
    overlay.classList.remove('show')
    editingId = null
  }

  function fillModalForEdit(row) {
    modalTitle.textContent = `Edit Pengguna - ${row.children[2].textContent.trim()}`
    document.getElementById('userName').value = row.children[2].textContent.trim()
    document.getElementById('userEmail').value = row.children[3].textContent.trim()
    document.getElementById('userPhone').value = row.dataset.phone || ''
    document.getElementById('userPassword').value = ''
    document.getElementById('passwordField').style.display = 'none'
    editingRole = row.dataset.role
    if ([...roleSelect.options].some((o) => o.value === String(editingRole))) {
      roleSelect.value = String(editingRole)
    } else {
      roleSelect.value = '__custom__'
      document.getElementById('customRoleName').value = String(editingRole)
      customRoleField.style.display = 'flex'
    }
    const statusToggle = row.querySelector('.status-toggle')
    document.getElementById('userStatusToggle').checked = statusToggle.checked
    let perms = []
    try {
      perms = JSON.parse(row.dataset.permissions || '[]')
    } catch (e) {
      perms = []
    }
    applyPermissions(perms)
    if (typeof syncCustomSelects === 'function') syncCustomSelects()
    overlay.classList.add('show')
  }

  function resetAccessChecklist() {
    document.querySelectorAll('#userModalOverlay .access-check').forEach((c) => {
      c.checked = false
      c.indeterminate = false
    })
  }

  // Kunci hak akses: data-group untuk menu utama, teks label untuk submenu
  function accessKeyOf(input) {
    if (input.dataset.group) return input.dataset.group
    const label = input.closest('label')
    return label ? label.textContent.trim().replace(/\s+/g, ' ') : ''
  }

  function collectPermissions() {
    const perms = []
    document.querySelectorAll('#userModalOverlay .access-check:checked').forEach((c) => {
      const key = accessKeyOf(c)
      if (key && !perms.includes(key)) perms.push(key)
    })
    return perms
  }

  function syncGroupParents() {
    document.querySelectorAll('#userModalOverlay .group-toggle').forEach((parent) => {
      const group = parent.dataset.group
      const children = document.querySelectorAll(`[data-children-of="${group}"] .access-check`)
      const checkedCount = Array.from(children).filter((c) => c.checked).length
      parent.checked = checkedCount === children.length
      parent.indeterminate = checkedCount > 0 && checkedCount < children.length
    })
  }

  function applyPermissions(perms) {
    resetAccessChecklist()
    if (!Array.isArray(perms)) return
    document.querySelectorAll('#userModalOverlay .access-check').forEach((c) => {
      if (perms.includes(accessKeyOf(c))) c.checked = true
    })
    syncGroupParents()
  }

  document.getElementById('btnTambahUser').addEventListener('click', () => openModal('Tambah User'))

  const resetOverlay = document.getElementById('resetPasswordOverlay')
  let resetUserId = null
  let resetUserName = ''

  function openResetModal(id) {
    resetUserId = id
    resetOverlay.classList.add('show')
    document.getElementById('resetUserName').value = resetUserName
    document.getElementById('resetUserPassword').value = ''
    document.getElementById('resetUserPassword').focus()
  }
  function closeResetModal() {
    resetOverlay.classList.remove('show')
  }
  document.getElementById('resetCloseBtn').addEventListener('click', closeResetModal)
  document.getElementById('resetCancelBtn').addEventListener('click', closeResetModal)
  resetOverlay.addEventListener('click', (e) => {
    if (e.target === resetOverlay) closeResetModal()
  })
  document.getElementById('resetSaveBtn').addEventListener('click', async () => {
    const password = document.getElementById('resetUserPassword').value
    if (password.length < 8) {
      showToast('Password minimal 8 karakter.', 'fa-solid fa-triangle-exclamation')
      return
    }
    const res = await fetch(adminUrl(`/pengaturan/user-management/${resetUserId}/reset-password`), {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify({ password }),
    })
    closeResetModal()
    showToast(res.ok ? `Password ${resetUserName} berhasil direset.` : 'Gagal reset password.', 'fa-solid fa-key')
  })

  document.getElementById('userTableBody').addEventListener('click', async (e) => {
    const row = e.target.closest('tr')
    if (!row) return

    if (e.target.closest('.btn-edit-user')) {
      editingId = row.dataset.id
      fillModalForEdit(row)
      return
    }

    if (e.target.closest('.btn-hapus-user')) {
      const nama = row.children[2].textContent.trim()
      const userId = row.dataset.id
      openConfirmDelete({
        title: 'Hapus User?',
        message: `Yakin ingin menghapus user "${nama}"? Tindakan ini tidak bisa dibatalkan.`,
        onConfirm: async () => {
          const res = await fetch(adminUrl(`/pengaturan/user-management/${userId}`), {
            method: 'DELETE',
            headers: getHeaders(),
          })
          if (res.ok) {
            row.remove()
            showToast(`User "${nama}" dihapus.`, 'fa-solid fa-trash')
          } else {
            const err = await res.json().catch(() => ({}))
            showToast(err.message || 'Gagal menghapus user.', 'fa-solid fa-triangle-exclamation')
          }
        },
      })
      return
    }

    if (e.target.closest('.btn-reset-password')) {
      resetUserName = row.children[2].textContent.trim()
      openResetModal(row.dataset.id)
      return
    }
  })

  document.getElementById('userTableBody').addEventListener('change', async (e) => {
    const toggle = e.target.closest('.status-toggle')
    if (!toggle) return
    const row = toggle.closest('tr')
    const nama = row.children[2].textContent.trim()
    const status = toggle.checked ? 'aktif' : 'nonaktif'

    const res = await fetch(adminUrl(`/pengaturan/user-management/${row.dataset.id}/status`), {
      method: 'PATCH',
      headers: getHeaders(),
      body: JSON.stringify({ status }),
    })
    if (res.ok) {
      row.dataset.status = status
      applyUserTableTools()
      showToast(status === 'aktif' ? `${nama} diaktifkan` : `${nama} dinonaktifkan`)
    } else {
      toggle.checked = !toggle.checked
      const err = await res.json().catch(() => ({}))
      showToast(err.message || 'Gagal mengubah status.', 'fa-solid fa-triangle-exclamation')
    }
  })

  document.getElementById('modalCloseBtn').addEventListener('click', closeModal)
  document.getElementById('modalCancelBtn').addEventListener('click', closeModal)
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal()
  })

  roleSelect.addEventListener('change', () => {
    customRoleField.style.display = roleSelect.value === '__custom__' ? 'flex' : 'none'
  })

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

  document.getElementById('modalSaveBtn').addEventListener('click', async () => {
    const nama = document.getElementById('userName').value.trim()
    if (!nama) {
      showToast('Isi nama dulu ya', 'fa-solid fa-triangle-exclamation')
      return
    }

    let role = roleSelect.value
    if (role === '__custom__') role = document.getElementById('customRoleName').value.trim()
    if (!role) {
      showToast('Role harus diisi', 'fa-solid fa-triangle-exclamation')
      return
    }

    const payload = {
      name: nama,
      email: document.getElementById('userEmail').value.trim(),
      phone: document.getElementById('userPhone').value.trim(),
      role,
      status: document.getElementById('userStatusToggle').checked ? 'aktif' : 'nonaktif',
      permissions: collectPermissions(),
    }

    const url = adminUrl(editingId ? `/pengaturan/user-management/${editingId}` : '/pengaturan/user-management')
    const method = editingId ? 'PUT' : 'POST'

    if (!editingId) {
      const password = document.getElementById('userPassword').value
      if (!password || password.length < 8) {
        showToast('Password minimal 8 karakter.', 'fa-solid fa-triangle-exclamation')
        return
      }
      payload.password = password
    }

    try {
      const res = await fetch(url, {
        method,
        headers: getHeaders(),
        body: JSON.stringify(payload),
      })
      if (!res.ok) {
        await showFetchError(res, 'Gagal menyimpan user.')
        return
      }
      showToast(editingId ? 'User berhasil diperbarui.' : `User "${nama}" tersimpan.`)
      closeModal()
      location.reload()
    } catch (err) {
      showToast('Terjadi kesalahan saat menyimpan user.', 'fa-solid fa-triangle-exclamation')
    }
  })

  // Cari + Urutkan + Saring tabel "Daftar Akun"
  const userSearch = document.getElementById('userSearch')
  const userSortSelect = document.getElementById('sortFilter')
  const userStatusSelect = document.getElementById('statusFilter')
  const userTableBody = document.getElementById('userTableBody')

  function applyUserTableTools() {
    if (!userSearch || !userTableBody) return
    const q = userSearch.value.toLowerCase()
    const status = userStatusSelect ? userStatusSelect.value : ''
    const sort = userSortSelect ? userSortSelect.value : 'nama-asc'

    const rows = Array.from(userTableBody.querySelectorAll('tr'))

    // Filter: kata kunci (nama/email) + status
    rows.forEach((row) => {
      const nama = (row.children[2]?.textContent || '').toLowerCase()
      const email = (row.children[3]?.textContent || '').toLowerCase()
      const rowStatus = row.dataset.status || ''
      const matchSearch = nama.includes(q) || email.includes(q)
      const matchStatus = !status || rowStatus === status
      row.style.display = matchSearch && matchStatus ? '' : 'none'
    })

    // Urutkan baris yang terlihat
    const visible = rows.filter((row) => row.style.display !== 'none')
    visible.sort((a, b) => {
      const nameA = (a.children[2]?.textContent || '').toLowerCase()
      const nameB = (b.children[2]?.textContent || '').toLowerCase()
      const tsA = Number(a.dataset.lastlogin || 0)
      const tsB = Number(b.dataset.lastlogin || 0)
      switch (sort) {
        case 'nama-desc':
          return nameB.localeCompare(nameA, 'id')
        case 'login-baru':
          return tsB - tsA
        case 'login-lama':
          return tsA - tsB
        default:
          return nameA.localeCompare(nameB, 'id')
      }
    })
    visible.forEach((row) => userTableBody.appendChild(row))
  }

  userSearch?.addEventListener('input', applyUserTableTools)
  userSortSelect?.addEventListener('change', applyUserTableTools)
  userStatusSelect?.addEventListener('change', applyUserTableTools)
}

document.addEventListener('DOMContentLoaded', initUserManagement)