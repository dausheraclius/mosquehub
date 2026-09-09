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

//Auto-Scroll ka menu nu aktif 
function normalizePath(url) {
  const raw = String(url || '').trim()

  try {
    const pathname = raw.includes('://')
      ? new URL(raw, window.location.origin).pathname
      : raw.split('?')[0].split('#')[0]

    const segments = pathname.split('/').filter(Boolean)
    if (segments[0] === 'id' || segments[0] === 'en') {
      segments.shift()
    }

    const normalized = '/' + segments.join('/')
    return normalized.replace(/\/+$/, '') || '/'
  } catch {
    const pathname = raw.split('?')[0].split('#')[0]
    const segments = pathname.split('/').filter(Boolean)
    if (segments[0] === 'id' || segments[0] === 'en') {
      segments.shift()
    }

    const normalized = '/' + segments.join('/')
    return normalized.replace(/\/+$/, '') || '/'
  }
}

function setActiveSidebarLink() {
  const currentPath = normalizePath(window.location.href)

  document.querySelectorAll('.sidebar-item').forEach((item) => item.classList.remove('active', 'open'))
  document.querySelectorAll('.submenu-link').forEach((link) => link.classList.remove('active'))

  document.querySelectorAll('.sidebar-link').forEach((link) => {
    if (link.getAttribute('href') === '#') return

    const normalized = normalizePath(link.href)
    if (normalized === currentPath) {
      const item = link.closest('.sidebar-item')
      item?.classList.add('active')
      if (item?.classList.contains('has-submenu')) {
        item.classList.add('open')
      }
    }
  })

  document.querySelectorAll('.submenu-link').forEach((link) => {
    const normalized = normalizePath(link.href)
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

  const align = () => {
    const sidebarRect = sidebar.getBoundingClientRect()
    const itemRect = active.getBoundingClientRect()
    const top = itemRect.top - sidebarRect.top
    const bottom = itemRect.bottom - sidebarRect.top

    // Jika item aktif tidak terlihat di area sidebar, scroll agar item masuk ke area tampilan.
    if (top < 0 || bottom > sidebarRect.height) {
      const targetScroll = sidebar.scrollTop + top - (sidebarRect.height - itemRect.height) / 2
      sidebar.scrollTop = Math.max(0, targetScroll)
    }
  }

  // Layout belum final saat DOMContentLoaded: submenu aktif lagi
  // nge-expand (transisi max-height) dan font/icon eksternal bisa
  // geser posisi belakangan — jadi ukur ulang sampai stabil.
  align()
  requestAnimationFrame(() => requestAnimationFrame(align))
  document.fonts?.ready.then(() => align())
  active.closest('.submenu')?.addEventListener('transitionend', align, { once: true })
  setTimeout(align, 300)
}