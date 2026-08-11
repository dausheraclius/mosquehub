const JABATAN_STORAGE_KEY = 'mosquehub-jabatan-list'
let lastSavedSnapshot = null

function getJabatanDariDom() {
  const tbody = document.querySelector('.jabatan-table tbody')
  if (!tbody) return []
  const namaList = []
  tbody.querySelectorAll('tr').forEach((row) => {
    const input = row.querySelector('.jabatan-input')
    if (input && input.value.trim()) {
      namaList.push(input.value.trim())
    }
  })
  return namaList
}

function simpanJabatanKeStorage() {
  const currentData = getJabatanDariDom()
  const currentSnapshot = JSON.stringify(currentData)

  if (currentSnapshot === lastSavedSnapshot) return

  const stored = localStorage.getItem(JABATAN_STORAGE_KEY)
  if (stored && stored !== lastSavedSnapshot) {
    showToast('Data diubah di tab lain. Reload...', 'fa-solid fa-triangle-exclamation')
    setTimeout(() => location.reload(), 800)
    return
  }

  localStorage.setItem(JABATAN_STORAGE_KEY, currentSnapshot)
  lastSavedSnapshot = currentSnapshot
}

function buatBarisJabatan(nama, index) {
  const tr = document.createElement('tr')
  tr.innerHTML = `
    <td>${index + 1}</td>
    <td>
      <input type="text" class="jabatan-input" value="${nama}" />
    </td>
    <td>
      <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
      <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
    </td>
  `
  tr.querySelector('.jabatan-input').addEventListener('input', () => simpanJabatanKeStorage())
  tr.querySelector('.icon-action-btn.hapus').addEventListener('click', () => {
    const tbody = tr.closest('tbody')
    tr.remove()
    if (tbody) {
      tbody.querySelectorAll('tr').forEach((r, idx) => {
        r.children[0].textContent = idx + 1
      })
    }
    simpanJabatanKeStorage()
    showToast('Jabatan berhasil dihapus', 'fa-solid fa-trash')
  })
  tr.querySelector('.icon-action-btn.edit').addEventListener('click', () => {
    simpanJabatanKeStorage()
    showToast('Jabatan tersimpan', 'fa-solid fa-check')
  })
  return tr
}

function muatJabatanDariStorage() {
  const saved = localStorage.getItem(JABATAN_STORAGE_KEY)
  const tbody = document.querySelector('.jabatan-table tbody')
  if (!tbody) return

  if (!saved) {
    simpanJabatanKeStorage()
    return
  }

  const namaList = JSON.parse(saved)
  tbody.innerHTML = ''
  namaList.forEach((nama, i) => {
    tbody.appendChild(buatBarisJabatan(nama, i))
  })
  lastSavedSnapshot = JSON.stringify(namaList)
}

function initUmum() {
  muatJabatanDariStorage()

  window.addEventListener('storage', (e) => {
    if (e.key === JABATAN_STORAGE_KEY && e.newValue !== lastSavedSnapshot) {
      showToast('Data jabatan diubah di tab lain. Reload...', 'fa-solid fa-triangle-exclamation')
      setTimeout(() => location.reload(), 800)
    }
  })

  document.querySelectorAll('.btn-jabatan-tambah').forEach((btn) => {
    btn.addEventListener('click', () => {
      const panel = btn.closest('.jabatan-panel')
      const tbody = panel.querySelector('.jabatan-table tbody')
      const tr = buatBarisJabatan('', tbody.querySelectorAll('tr').length)
      tbody.appendChild(tr)
      const newInput = tr.querySelector('.jabatan-input')
      newInput.placeholder = 'Nama jabatan baru...'
      newInput.focus()
      showToast('Baris baru ditambahkan', 'fa-solid fa-plus')
    })
  })

  const navItems = document.querySelectorAll('.settings-nav-item')
  navItems.forEach((item) => {
    item.addEventListener('click', () => {
      navItems.forEach((n) => n.classList.remove('active'))
      item.classList.add('active')

      document.querySelectorAll('.settings-panel').forEach((panel) => {
        panel.classList.toggle('active', panel.id === item.dataset.target)
      })
    })
  })

  document.querySelectorAll('.settings-save').forEach((btn) => {
    btn.addEventListener('click', () => {
        showToast(`${btn.dataset.panelSave} tersimpan (dummy, belum ke server)`)
    })
  })

  const darkToggleSettings = document.getElementById('darkModeToggleSettings')
  if (darkToggleSettings) {
    darkToggleSettings.checked = document.body.classList.contains('dark-mode')

    darkToggleSettings.addEventListener('change', () => {
      const isDark = darkToggleSettings.checked
      document.body.classList.toggle('dark-mode', isDark)
      localStorage.setItem('mosquehub-dark-mode', isDark)

      const topbarBtn = document.getElementById('darkModeToggle')
      if (topbarBtn) {
        const icon = topbarBtn.querySelector('i')
        if (icon) {
          icon.classList.toggle('fa-moon', !isDark)
          icon.classList.toggle('fa-sun', isDark)
        }
        topbarBtn.title = isDark ? 'Mode Terang' : 'Mode Gelap'
      }

      showToast(isDark ? 'Dark mode aktif' : 'Dark mode nonaktif')
    })
  }

  document.querySelectorAll('.theme-swatch').forEach((swatch) => {
    swatch.addEventListener('click', () => {
      document.querySelectorAll('.theme-swatch').forEach((s) => s.classList.remove('active'))
      swatch.classList.add('active')
      showToast('Warna tema dipilih (preview belum diterapin ke seluruh app)')
    })
  })

  const FONT_SIZE_MAP = { small: '13px', medium: '14px', large: '16px' }
  function applyFontSize(size) {
    document.body.style.fontSize = FONT_SIZE_MAP[size] || '14px'
    localStorage.setItem('mosquehub-font-size', size)
  }
  document.querySelectorAll('.radio-pill').forEach((pill) => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('.radio-pill').forEach((p) => p.classList.remove('active'))
      pill.classList.add('active')
      applyFontSize(pill.dataset.size)
    })
  })
  const savedSize = localStorage.getItem('mosquehub-font-size') || 'medium'
  document.querySelector(`.radio-pill[data-size="${savedSize}"]`)?.classList.add('active')
  applyFontSize(savedSize)

  document.getElementById('btnChangePassword')?.addEventListener('click', () => {
    const oldPass = document.getElementById('oldPassword').value
    const newPass = document.getElementById('newPassword').value
    const confirmPass = document.getElementById('confirmPassword').value

    if (!oldPass || !newPass || !confirmPass) {
      showToast('Isi semua kolom password dulu', 'fa-solid fa-triangle-exclamation')
      return
    }
    if (newPass !== confirmPass) {
      showToast('Password baru gak sama sama konfirmasinya', 'fa-solid fa-triangle-exclamation')
      return
    }
    showToast('Password berhasil diganti (dummy)')
    document.getElementById('oldPassword').value = ''
    document.getElementById('newPassword').value = ''
    document.getElementById('confirmPassword').value = ''
  })

  document.querySelectorAll('[data-logout-session]').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.closest('.session-item').remove()
      showToast('Sesi berhasil di-logout')
    })
  })

  document.getElementById('btnBackupNow')?.addEventListener('click', () => {
    showToast('Backup dimulai... (dummy)', 'fa-solid fa-cloud-arrow-up')
  })
  document.getElementById('btnExportData')?.addEventListener('click', () => {
    showToast('Export data CSV disiapkan (dummy)', 'fa-solid fa-file-export')
  })
  const importInput = document.getElementById('importFileInput')
  document.getElementById('btnImportData')?.addEventListener('click', () => importInput.click())
  importInput?.addEventListener('change', () => {
    if (importInput.files[0]) {
      showToast(`File "${importInput.files[0].name}" siap di-import (dummy)`, 'fa-solid fa-file-import')
    }
  })

  document.getElementById('btnResetJabatan')?.addEventListener('click', () => {
    if (!confirm('Reset semua data jabatan, hierarki, dan penempatan ke default?')) return
    localStorage.removeItem('mosquehub-jabatan-list')
    localStorage.removeItem('mosquehub-kepengurusan-hierarki')
    localStorage.removeItem('mosquehub-kepengurusan-penempatan')
    muatJabatanDariStorage()
    showToast('Data jabatan & kepengurusan direset ke default', 'fa-solid fa-rotate-left')
  })
}

document.addEventListener('DOMContentLoaded', initUmum)
