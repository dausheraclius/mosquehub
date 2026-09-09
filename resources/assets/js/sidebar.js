document.addEventListener('componentsLoaded', () => {
  // Toggle submenu (Data Jemaah, Keuangan, Kegiatan)
  const submenuToggles = document.querySelectorAll('.submenu-toggle')
  submenuToggles.forEach((toggle) => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault()
      const parentItem = toggle.closest('.sidebar-item')
      parentItem.classList.toggle('open')
    })
  })

  // Set active menu saat item sidebar utama diklik (bukan submenu)
  const sidebarLinks = document.querySelectorAll('.sidebar-link:not(.submenu-toggle)')
  sidebarLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      if (link.id === 'sidebarLogout') return

      document.querySelectorAll('.sidebar-item').forEach((item) => {
        item.classList.remove('active')
      })
      link.closest('.sidebar-item').classList.add('active')
    })
  })

  // Toggle sidebar mobile (hamburger + overlay)
  const hamburgerBtn = document.getElementById('hamburgerBtn')
  const sidebar = document.getElementById('sidebar')
  const sidebarOverlay = document.getElementById('sidebarOverlay')

  function openMobileSidebar() {
    if (sidebar && sidebarOverlay) {
      sidebar.classList.add('mobile-open')
      sidebarOverlay.classList.add('show')
      document.body.style.overflow = 'hidden'
    }
  }

  function closeMobileSidebar() {
    if (sidebar && sidebarOverlay) {
      sidebar.classList.remove('mobile-open')
      sidebarOverlay.classList.remove('show')
      document.body.style.overflow = ''
    }
  }

  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', () => {
      if (sidebar.classList.contains('mobile-open')) {
        closeMobileSidebar()
      } else {
        openMobileSidebar()
      }
    })
  }

  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', closeMobileSidebar)
  }

  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      closeMobileSidebar()
    }
  })
})
