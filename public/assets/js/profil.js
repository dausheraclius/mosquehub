function initProfil() {
  const headers = getHeaders()

  // Navigasi panel kiri
  const navItems = document.querySelectorAll('#settingsNav .settings-nav-item')
  navItems.forEach((btn) => {
    btn.addEventListener('click', () => {
      navItems.forEach((b) => b.classList.remove('active'))
      btn.classList.add('active')
      document.querySelectorAll('.settings-panel').forEach((p) => p.classList.remove('active'))
      document.getElementById(btn.dataset.target)?.classList.add('active')
    })
  })

  // Simpan data akun
  document.getElementById('btnSaveProfil').addEventListener('click', async () => {
    const name = document.getElementById('profilName').value.trim()
    const email = document.getElementById('profilEmail').value.trim()
    if (!name || !email) {
      showToast('Nama dan email wajib diisi.', 'fa-solid fa-triangle-exclamation')
      return
    }

    try {
      const res = await fetch(adminUrl('/profil'), {
        method: 'PUT',
        headers,
        body: JSON.stringify({
          name,
          email,
          phone: document.getElementById('profilPhone').value.trim(),
        }),
      })

      if (!res.ok) {
        await showFetchError(res, 'Gagal menyimpan data.')
        return
      }

      showToast('Profil berhasil diperbarui.', 'fa-solid fa-circle-check')
      setTimeout(() => location.reload(), 800)
    } catch (err) {
      showToast('Terjadi kesalahan saat menyimpan profil.', 'fa-solid fa-triangle-exclamation')
    }
  })

  // Ganti password
  document.getElementById('btnChangePassword').addEventListener('click', async () => {
    const oldPassword = document.getElementById('profilOldPassword').value
    const newPassword = document.getElementById('profilNewPassword').value
    const confirmPassword = document.getElementById('profilConfirmPassword').value

    if (newPassword.length < 8) {
      showToast('Password baru minimal 8 karakter.', 'fa-solid fa-triangle-exclamation')
      return
    }
    if (newPassword !== confirmPassword) {
      showToast('Konfirmasi password tidak cocok.', 'fa-solid fa-triangle-exclamation')
      return
    }

    try {
      const res = await fetch(adminUrl('/profil/password'), {
        method: 'POST',
        headers,
        body: JSON.stringify({
          old_password: oldPassword,
          new_password: newPassword,
          new_password_confirmation: confirmPassword,
        }),
      })

      if (!res.ok) {
        await showFetchError(res, 'Gagal mengubah password.')
        return
      }

      document.getElementById('profilOldPassword').value = ''
      document.getElementById('profilNewPassword').value = ''
      document.getElementById('profilConfirmPassword').value = ''
      showToast('Password berhasil diubah.', 'fa-solid fa-key')
    } catch (err) {
      showToast('Terjadi kesalahan saat mengubah password.', 'fa-solid fa-triangle-exclamation')
    }
  })

  // Kirim ulang email verifikasi
  const btnResend = document.getElementById('btnResendVerification')
  btnResend?.addEventListener('click', async () => {
    try {
      const res = await fetch(adminUrl('/email/verification-notification'), {
        method: 'POST',
        headers,
      })

      if (!res.ok) {
        const err = await res.json()
        showToast(err.message || 'Gagal mengirim email verifikasi.', 'fa-solid fa-triangle-exclamation')
        return
      }

      btnResend.innerHTML = '<i class="fa-solid fa-circle-check"></i> Email Terkirim'
      btnResend.disabled = true
      showToast('Email verifikasi dikirim. Cek kotak masuk Anda.', 'fa-solid fa-envelope-circle-check')
    } catch (err) {
      showToast('Terjadi kesalahan saat mengirim email.', 'fa-solid fa-triangle-exclamation')
    }
  })
}

document.addEventListener('DOMContentLoaded', initProfil)
