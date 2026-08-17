document.addEventListener('DOMContentLoaded', () => {
  setActiveSidebarLink()
  scrollActiveLinkIntoView()
  injectCollapseButton()
  document.dispatchEvent(new Event('componentsLoaded'))
  document.body.classList.add('components-ready')
})

// Sisipin tombol collapse sidebar ke breadcrumb halaman manapun
function injectCollapseButton() {
  const breadcrumb = document.querySelector('.breadcrumb')
  if (!breadcrumb) return

  const btn = document.createElement('button')
  btn.className = 'breadcrumb-collapse-btn'
  btn.id = 'sidebarCollapseBtn'
  btn.title = 'Ciutkan Sidebar'
  btn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>'

  const divider = document.createElement('span')
  divider.className = 'breadcrumb-divider'

  breadcrumb.prepend(divider)
  breadcrumb.prepend(btn)

  btn.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed')
    const icon = btn.querySelector('i')
    if (document.body.classList.contains('sidebar-collapsed')) {
      icon.classList.remove('fa-chevron-left')
      icon.classList.add('fa-chevron-right')
    } else {
      icon.classList.remove('fa-chevron-right')
      icon.classList.add('fa-chevron-left')
    }
  })
}

function setActiveSidebarLink() {
  const currentPath = window.location.pathname.replace(/\/+$/, '') || '/'

  document.querySelectorAll('.sidebar-link').forEach((link) => {
    if (link.getAttribute('href') === '#') return
    const normalized = new URL(link.href).pathname.replace(/\/+$/, '') || '/'
    if (normalized === currentPath) {
      link.closest('.sidebar-item')?.classList.add('active')
    }
  })

  document.querySelectorAll('.submenu-link').forEach((link) => {
    const normalized = new URL(link.href).pathname.replace(/\/+$/, '') || '/'
    if (normalized === currentPath) {
      link.classList.add('active')
      link.closest('.sidebar-item.has-submenu')?.classList.add('active', 'open')
    }
  })
}

// Gulir sidebar ke item menu aktif kalo posisinya di bawah/atas layar.
// Biasanya sidebar ke-reset ke atas tiap halaman di-reload, padahal menu
// aktif (mis. Pengaturan) ada di bagian bawah — biar langsung keliatan.
function scrollActiveLinkIntoView() {
  const sidebar = document.getElementById('sidebar')
  const active = document.querySelector('.sidebar-item.active > .sidebar-link, .submenu-link.active')
  if (!sidebar || !active) return

  const sidebarRect = sidebar.getBoundingClientRect()
  const itemRect = active.getBoundingClientRect()

  // Posisi item relatif terhadap sisi atas sidebar (dalam viewport)
  const top = itemRect.top - sidebarRect.top
  const bottom = itemRect.bottom - sidebarRect.top

  // Kalo item keluar dari area sidebar yang terlihat, scroll biar item
  // ke-tengah (item + konteks di sekitarnya ikut kebaca).
  if (top < 0 || bottom > sidebarRect.height) {
    sidebar.scrollTop += top - (sidebarRect.height - itemRect.height) / 2
  }
}