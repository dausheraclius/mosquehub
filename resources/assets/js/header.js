// ===== Helper: apply dark mode state ke UI =====
function applyDarkModeState() {
  const darkModeBtn = document.getElementById('darkModeToggle')
  const isDark = localStorage.getItem('mosquehub-dark-mode') === 'true'

  document.body.classList.toggle('dark-mode', isDark)

  if (darkModeBtn) {
    const icon = darkModeBtn.querySelector('i')
    if (icon) {
      icon.classList.toggle('fa-moon', !isDark)
      icon.classList.toggle('fa-sun', isDark)
    }
    darkModeBtn.title = isDark ? 'Mode Terang' : 'Mode Gelap'
  }
}

// ===== Helper: attach event listeners ke komponen yang baru di-load =====
let headerListenersBound = false

function attachHeaderListeners() {
  if (headerListenersBound) return

  const darkModeBtn = document.getElementById('darkModeToggle')
  const topbarCollapseBtn = document.getElementById('topbarCollapseBtn')
  const topbar = document.querySelector('.topbar')
  const logoutBtn = document.getElementById('logoutBtn')

  if (!darkModeBtn && !topbarCollapseBtn && !logoutBtn && !topbar) {
    return
  }

  // Dark mode toggle
  darkModeBtn?.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark-mode')
    const icon = darkModeBtn.querySelector('i')
    icon.classList.toggle('fa-moon')
    icon.classList.toggle('fa-sun')
    darkModeBtn.title = isDark ? 'Mode Terang' : 'Mode Gelap'
    localStorage.setItem('mosquehub-dark-mode', isDark)
    document.dispatchEvent(new CustomEvent('mosquehub:darkmode', { detail: { dark: isDark } }))
  })

  // Topbar collapse
  const topbarCollapsed = localStorage.getItem('mosquehub-topbar-collapsed') === 'true'
  if (topbar) {
    if (topbarCollapsed) {
      topbar.classList.add('collapsed')
      document.body.classList.add('topbar-collapsed')
    }
    setTopbarCollapseIcon(topbarCollapsed)

    topbarCollapseBtn?.addEventListener('click', () => {
      const now = topbar.classList.toggle('collapsed')
      document.body.classList.toggle('topbar-collapsed')
      localStorage.setItem('mosquehub-topbar-collapsed', now)
      setTopbarCollapseIcon(now)
    })
  }

  // Logout — trigger modal dari app.js
  logoutBtn?.addEventListener('click', (e) => {
    e.preventDefault()
    if (typeof openLogoutModal === 'function') openLogoutModal()
  })

  headerListenersBound = true
}

// Chevron tombol collapse menyesuaikan arah: ke atas saat terbuka, ke bawah saat tertutup
function setTopbarCollapseIcon(collapsed) {
  const btn = document.getElementById('topbarCollapseBtn')
  const icon = btn?.querySelector('i')
  if (!icon) return
  icon.classList.toggle('fa-chevron-up', !collapsed)
  icon.classList.toggle('fa-chevron-down', collapsed)
  if (btn) btn.title = collapsed ? 'Tampilkan Topbar' : 'Sembunyikan Topbar'
}

function applyFontSizeState() {
  const saved = localStorage.getItem('mosquehub-font-size') || 'medium'
  document.body.classList.remove('font-size-small', 'font-size-medium', 'font-size-large')
  document.body.classList.add('font-size-' + saved)
}

// ===== Init saat komponen selesai di-load =====
function initHeaderModule() {
  applyDarkModeState()
  applyFontSizeState()
  attachHeaderListeners()
}

document.addEventListener('componentsLoaded', initHeaderModule)
document.addEventListener('DOMContentLoaded', initHeaderModule)

if (document.readyState !== 'loading') {
  initHeaderModule()
}
