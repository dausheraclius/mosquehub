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
function attachHeaderListeners() {
  const darkModeBtn = document.getElementById('darkModeToggle')
  const topbarCollapseBtn = document.getElementById('topbarCollapseBtn')
  const topbar = document.querySelector('.topbar')
  const logoutBtn = document.getElementById('logoutBtn')

  // Dark mode toggle
  darkModeBtn?.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark-mode')
    const icon = darkModeBtn.querySelector('i')
    icon.classList.toggle('fa-moon')
    icon.classList.toggle('fa-sun')
    darkModeBtn.title = isDark ? 'Mode Terang' : 'Mode Gelap'
    localStorage.setItem('mosquehub-dark-mode', isDark)
  })

  // Topbar collapse
  const topbarCollapsed = localStorage.getItem('mosquehub-topbar-collapsed') === 'true'
  if (topbarCollapsed) {
    topbar.classList.add('collapsed')
    document.body.classList.add('topbar-collapsed')
  }

  topbarCollapseBtn?.addEventListener('click', () => {
    const now = topbar.classList.toggle('collapsed')
    document.body.classList.toggle('topbar-collapsed')
    localStorage.setItem('mosquehub-topbar-collapsed', now)
  })

  // Logout — trigger modal dari app.js
  logoutBtn?.addEventListener('click', (e) => {
    e.preventDefault()
    if (typeof openLogoutModal === 'function') openLogoutModal()
  })
}

function applyFontSizeState() {
  const sizeMap = { small: '13px', medium: '14px', large: '16px' }
  const saved = localStorage.getItem('mosquehub-font-size') || 'medium'
  document.body.style.fontSize = sizeMap[saved] || '14px'
}

// ===== Init saat komponen selesai di-load =====
document.addEventListener('componentsLoaded', () => {
  applyDarkModeState()
  applyFontSizeState()
  attachHeaderListeners()
})
