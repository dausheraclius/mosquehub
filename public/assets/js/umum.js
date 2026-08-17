function renumberAndRemove(tr) {
  const tbody = tr.closest('tbody')
  tr.remove()
  if (tbody) {
    tbody.querySelectorAll('tr').forEach((r, idx) => {
      r.children[0].textContent = idx + 1
    })
  }
}

function bindBarisJabatan(tr) {

  tr.querySelector('.icon-action-btn.hapus').addEventListener('click', async () => {
    const namaSekarang = tr.querySelector('.jabatan-input').dataset.original
    if (!namaSekarang) {
      renumberAndRemove(tr)
      return
    }
    if (!confirm(`Hapus jabatan "${namaSekarang}"?`)) return

    await fetch('/kepengurusan/jabatan', {
      method: 'DELETE',
      headers: getHeaders(),
      body: JSON.stringify({ nama: namaSekarang }),
    })
    renumberAndRemove(tr)
    showToast('Jabatan berhasil dihapus', 'fa-solid fa-trash')
  })

  tr.querySelector('.icon-action-btn.edit').addEventListener('click', async () => {
    const input = tr.querySelector('.jabatan-input')
    const namaLama = input.dataset.original
    const namaBaru = input.value.trim()
    if (!namaBaru) return

    if (!namaLama) {
      const res = await fetch('/kepengurusan/jabatan', {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ nama: namaBaru, parent_nama: null }),
      })
      if (!res.ok) {
        const err = await res.json()
        showToast(err.message || 'Gagal menambah jabatan.', 'fa-solid fa-triangle-exclamation')
        return
      }
    } else if (namaBaru !== namaLama) {
      const res = await fetch('/kepengurusan/jabatan/rename', {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ nama_lama: namaLama, nama_baru: namaBaru }),
      })
      if (!res.ok) {
        const err = await res.json()
        showToast(err.message || 'Gagal ganti nama jabatan.', 'fa-solid fa-triangle-exclamation')
        return
      }
    }

    input.dataset.original = namaBaru
    showToast('Jabatan tersimpan', 'fa-solid fa-check')
  })

  return tr
}

function buatBarisJabatan(nama, index) {
  const tr = document.createElement('tr')
  tr.innerHTML = `
    <td>${index + 1}</td>
    <td>
      <input type="text" class="jabatan-input" value="${nama}" data-original="${nama}" />
    </td>
    <td>
      <button class="icon-action-btn edit"><i class="fa-solid fa-check"></i></button>
      <button class="icon-action-btn hapus"><i class="fa-solid fa-trash"></i></button>
    </td>
  `
  return bindBarisJabatan(tr)
}

function initUmum() {
  document.querySelectorAll('.jabatan-table tbody tr').forEach((tr) => {
    if (!tr.querySelector('.jabatan-input')) return
    bindBarisJabatan(tr)
  })

  document.querySelectorAll('.btn-jabatan-tambah').forEach((btn) => {
    btn.addEventListener('click', () => {
      const panel = btn.closest('.jabatan-panel')
      const tbody = panel.querySelector('.jabatan-table tbody')
      const tr = buatBarisJabatan('', tbody.querySelectorAll('tr').length)
      tbody.appendChild(tr)
      const newInput = tr.querySelector('.jabatan-input')
      newInput.dataset.original = ''
      newInput.placeholder = 'Nama jabatan baru...'
      newInput.focus()
      showToast('Baris baru ditambahkan — isi nama terus klik ✓ buat simpan', 'fa-solid fa-plus')
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
    btn.addEventListener('click', async () => {
      let url = null
      let payload = {}

      if (btn.dataset.panelSave === 'Profil Aplikasi') {
        url = '/pengaturan/umum/profil-aplikasi'
        payload = {
          app_name: document.getElementById('appName').value,
          timezone: document.getElementById('appTimezone').value,
          date_format: document.getElementById('appDateFormat').value,
        }
      } else if (btn.dataset.panelSave === 'Notifikasi WhatsApp') {
        const toggles = document.querySelectorAll('#panelNotif .switch input')
        url = '/pengaturan/umum/notifikasi'
        payload = {
          wa_gateway_number: document.getElementById('waAdminNumber').value,
          notif_infaq_bulanan: toggles[0].checked,
          notif_agenda_kegiatan: toggles[1].checked,
          notif_jamaah_baru: toggles[2].checked,
          notif_laporan_mingguan: toggles[3].checked,
        }
      } else if (btn.dataset.panelSave === 'Jadwal Sholat') {
        const panel = document.getElementById('panelJadwalSholat')
        url = '/pengaturan/umum/jadwal-sholat'
        payload = {}
        ;['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'].forEach((key) => {
          const jam = panel.querySelector(`select.time-select[data-sholat="${key}"][data-unit="jam"]`)?.value
          const menit = panel.querySelector(`select.time-select[data-sholat="${key}"][data-unit="menit"]`)?.value
          payload['sholat_' + key] = `${jam}:${menit}`
          const tampil = panel.querySelector(`input.sholat-visible[data-sholat="${key}"]`)?.checked
          payload['tampil_sholat_' + key] = tampil ?? true
        })
      } else {
        showToast(`${btn.dataset.panelSave} tersimpan (dummy, belum ke server)`)
        return
      }

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: getHeaders(),
          body: JSON.stringify(payload),
        })
        if (!res.ok) throw new Error('gagal')
        showToast(`${btn.dataset.panelSave} tersimpan.`)
      } catch (err) {
        showToast('Gagal menyimpan, coba lagi.', 'fa-solid fa-triangle-exclamation')
      }
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

      document.dispatchEvent(new CustomEvent('mosquehub:darkmode', { detail: { dark: isDark } }))
      showToast(isDark ? 'Dark mode aktif' : 'Dark mode nonaktif')
    })
  }

  function applyFontSize(size) {
    const s = ['small', 'medium', 'large'].includes(size) ? size : 'medium'
    document.body.classList.remove('font-size-small', 'font-size-medium', 'font-size-large')
    document.body.classList.add('font-size-' + s)
    localStorage.setItem('mosquehub-font-size', s)
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

  document.getElementById('btnChangePassword')?.addEventListener('click', async () => {
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

    try {
      const res = await fetch('/pengaturan/umum/password', {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({ old_password: oldPass, new_password: newPass, new_password_confirmation: confirmPass }),
      })
      if (!res.ok) {
        const err = await res.json().catch(() => ({}))
        showToast(err.message || 'Gagal mengganti password.', 'fa-solid fa-triangle-exclamation')
        return
      }
      showToast('Password berhasil diganti.')
      document.getElementById('oldPassword').value = ''
      document.getElementById('newPassword').value = ''
      document.getElementById('confirmPassword').value = ''
    } catch (err) {
      showToast('Terjadi kesalahan saat mengganti password.', 'fa-solid fa-triangle-exclamation')
    }
  })

  // Backup & Data — semua aksi tersambung ke backend
  function downloadUrl(url, label) {
    window.downloadUrl(url)
    showToast(label, 'fa-solid fa-download')
  }

  document.getElementById('btnBackupNow')?.addEventListener('click', () => {
    downloadUrl('/pengaturan/umum/backup/ekspor', 'Backup sedang diunduh...')
  })
  document.getElementById('btnExportData')?.addEventListener('click', () => {
    downloadUrl('/pengaturan/umum/backup/ekspor', 'Data diekspor (JSON).')
  })

  const importInput = document.getElementById('importFileInput')
  document.getElementById('btnImportData')?.addEventListener('click', () => importInput.click())
  importInput?.addEventListener('change', async () => {
    const file = importInput.files[0]
    if (!file) {
      return
    }
    const fd = new FormData()
    fd.append('file', file)
    try {
      const res = await fetch('/pengaturan/umum/backup/impor', {
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
        skipCount
          ? `${data.imported} jamaah diimpor, ${skipCount} dilewati.`
          : `${data.imported} jamaah berhasil diimpor.`,
        'fa-solid fa-file-import',
      )
    } catch (err) {
      showToast('Terjadi kesalahan saat impor.', 'fa-solid fa-triangle-exclamation')
    } finally {
      importInput.value = ''
    }
  })

  // Setelan backup otomatis & frekuensi disimpan langsung saat diubah
  async function simpanBackupSetting() {
    try {
      const res = await fetch('/pengaturan/umum/backup', {
        method: 'POST',
        headers: getHeaders(),
        body: JSON.stringify({
          backup_otomatis: document.getElementById('backupOtomatisToggle').checked,
          backup_frekuensi: document.getElementById('backupFreq').value,
        }),
      })
      if (res.ok) showToast('Setelan backup tersimpan.')
    } catch (err) {
      showToast('Gagal menyimpan setelan backup.', 'fa-solid fa-triangle-exclamation')
    }
  }
  document.getElementById('backupOtomatisToggle')?.addEventListener('change', simpanBackupSetting)
  document.getElementById('backupFreq')?.addEventListener('change', simpanBackupSetting)

  document.getElementById('btnResetJabatan')?.addEventListener('click', async () => {
    if (!confirm('Reset semua data jabatan, hierarki, dan penempatan ke default?')) return
    await fetch('/kepengurusan/jabatan/reset', {
      method: 'POST',
      headers: getHeaders(),
    })
    showToast('Data jabatan & kepengurusan direset ke default', 'fa-solid fa-rotate-left')
    setTimeout(() => location.reload(), 800)
  })
}

document.addEventListener('DOMContentLoaded', initUmum)
