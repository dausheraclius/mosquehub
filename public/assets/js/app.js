// ============================================================
// GLOBAL UTILITIES — shared across all pages
// ============================================================

function showToast(message, icon = 'fa-solid fa-circle-check') {
  const toast = document.getElementById('appToast')
  if (!toast) return
  toast.innerHTML = `<i class="${icon}"></i> ${message}`
  toast.classList.add('show')
  clearTimeout(showToast._t)
  showToast._t = setTimeout(() => toast.classList.remove('show'), 2200)
}

function formatRupiah(angka) {
  return angka === 0 ? '-' : 'Rp ' + angka.toLocaleString('id-ID')
}

// Escape HTML buat mencegah XSS saat data user di-render ke innerHTML
function esc(value) {
  const s = value == null ? '' : String(value)
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

// ============================================================
// HTTP HELPERS — CSRF, headers, dan error fetch (dipakai semua halaman)
// ============================================================

function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]').content
}

function adminUrl(path) {
  const base = window.__ADMIN_BASE || ''
  return path.startsWith('/') ? `${base}${path}` : `${base}/${path}`
}

function getHeaders(extra = {}) {
  const { multipart = false, ...rest } = extra
  const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrf() }
  if (!multipart) headers['Content-Type'] = 'application/json'
  return { ...headers, ...rest }
}

async function apiFetch(url, options = {}) {
  const method = options.method || 'GET'
  const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': getCsrf() }
  let body = options.body
  if (body && typeof body === 'object' && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
    body = JSON.stringify(body)
  }
  return fetch(url, { ...options, method, headers, body })
}

// Tampilkan pesan error dari response fetch non-ok ke toast.
// Laravel: validasi → { errors: {...} }, abort/guard → { message: "..." }.
async function showFetchError(res, fallback = 'Gagal menyimpan data.') {
  const errData = await res.json().catch(() => ({}))
  const firstError = Object.values(errData.errors || {})[0]?.[0] || errData.message || fallback
  showToast(firstError, 'fa-solid fa-triangle-exclamation')
}

function downloadUrl(url, filename) {
  const a = document.createElement('a')
  a.href = url
  a.download = filename || ''
  document.body.appendChild(a)
  a.click()
  a.remove()
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  // Init custom select dropdown di semua halaman yang punya .filter-select
  if (typeof initCustomSelects === 'function') initCustomSelects()
})

// ============================================================
// CUSTOM SELECT DROPDOWN — komponen global
// Mengganti native <select class="filter-select"> dengan
// dropdown modern. Dipakai di semua halaman.
// ============================================================

// Semua dropdown single-choice memakai komponen custom. Dengan begitu select
// tanpa class khusus (mis. di halaman pengaturan dan halaman publik) tidak
// lagi jatuh ke tampilan bawaan browser.
// Select dalam modal yang bisa di-scroll memakai kontrol native agar opsi tidak
// terpotong oleh container `overflow-y: auto`.
const CUSTOM_SELECT_SELECTOR = 'select:not([multiple]):not([data-native-select])'

function initCustomSelects() {
  document.querySelectorAll(CUSTOM_SELECT_SELECTOR).forEach((nativeSelect) => {
    // Skip kalo udah di-wrap
    if (nativeSelect.closest('.custom-select-wrapper')) return

    const wrapper = document.createElement('div')
    wrapper.className = 'custom-select-wrapper'
    if (nativeSelect.disabled) wrapper.classList.add('disabled')

    const trigger = document.createElement('button')
    trigger.type = 'button'
    trigger.className = 'custom-select-trigger'
    trigger.innerHTML = `
      <span class="custom-select-text">${getSelectedText(nativeSelect)}</span>
      <i class="fa-solid fa-chevron-down custom-select-chevron"></i>
    `

    const optionsContainer = document.createElement('div')
    optionsContainer.className = 'custom-select-options'

    // Pindahkan native select ke dalam wrapper, lalu sembunyikan
    nativeSelect.parentNode.insertBefore(wrapper, nativeSelect)
    wrapper.appendChild(nativeSelect)
    wrapper.appendChild(trigger)
    wrapper.appendChild(optionsContainer)
    nativeSelect.style.display = 'none'

    // Build daftar opsi
    buildCustomOptions(nativeSelect, optionsContainer, trigger)

    // Toggle open/close
    trigger.addEventListener('click', (e) => {
      e.stopPropagation()
      if (nativeSelect.disabled) return
      // Tutup dropdown lain yang lagi open
      document.querySelectorAll('.custom-select-wrapper.open').forEach((el) => {
        if (el !== wrapper) el.classList.remove('open')
      })
      wrapper.classList.toggle('open')
    })

    // Pilih opsi
    optionsContainer.addEventListener('click', (e) => {
      const optionEl = e.target.closest('.custom-select-option')
      if (!optionEl) return

      nativeSelect.value = optionEl.dataset.value
      trigger.querySelector('.custom-select-text').textContent = optionEl.textContent.trim()
      optionsContainer.querySelectorAll('.custom-select-option').forEach((el) => el.classList.remove('selected'))
      optionEl.classList.add('selected')
      wrapper.classList.remove('open')

      nativeSelect.dispatchEvent(new Event('change', { bubbles: true }))
    })
  })

  // Satu listener global buat nutup semua dropdown
  if (!initCustomSelects.hasDocumentClickListener) {
    document.addEventListener('click', closeAllCustomSelects)
    initCustomSelects.hasDocumentClickListener = true
  }
}

function closeAllCustomSelects() {
  document.querySelectorAll('.custom-select-wrapper.open').forEach((el) => el.classList.remove('open'))
}

function getSelectedText(nativeSelect) {
  return nativeSelect.options[nativeSelect.selectedIndex]?.textContent || nativeSelect.value
}

function buildCustomOptions(nativeSelect, optionsContainer, trigger) {
  optionsContainer.innerHTML = ''
  const selectedValue = nativeSelect.value

  Array.from(nativeSelect.options).forEach((opt) => {
    const div = document.createElement('div')
    div.className = 'custom-select-option'
    if (opt.value === selectedValue) div.classList.add('selected')
    div.dataset.value = opt.value
    div.textContent = opt.textContent
    optionsContainer.appendChild(div)
  })
}

function syncCustomSelects() {
  document.querySelectorAll(CUSTOM_SELECT_SELECTOR).forEach((nativeSelect) => {
    const wrapper = nativeSelect.closest('.custom-select-wrapper')
    if (!wrapper) return

    // Status disabled bisa berubah saat runtime (mis. kategori terkunci per tab)
    wrapper.classList.toggle('disabled', nativeSelect.disabled)

    const trigger = wrapper.querySelector('.custom-select-trigger')
    const optionsContainer = wrapper.querySelector('.custom-select-options')

    // Rebuild opsi dari native select
    buildCustomOptions(nativeSelect, optionsContainer, trigger)

    // Update teks trigger
    trigger.querySelector('.custom-select-text').textContent = getSelectedText(nativeSelect)
  })
}

// Select pada modal atau daftar yang dirender setelah halaman siap tetap
// otomatis memakai komponen yang sama. Perubahan opsi juga langsung disinkronkan.
document.addEventListener('DOMContentLoaded', () => {
  const observer = new MutationObserver((mutations) => {
    const changedSelects = new Set()

    mutations.forEach((mutation) => {
      if (mutation.target instanceof HTMLSelectElement) changedSelects.add(mutation.target)
      mutation.addedNodes.forEach((node) => {
        if (!(node instanceof Element)) return
        if (node.matches(CUSTOM_SELECT_SELECTOR)) changedSelects.add(node)
        node.querySelectorAll?.(CUSTOM_SELECT_SELECTOR).forEach((select) => changedSelects.add(select))
      })
    })

    changedSelects.forEach((select) => {
      if (select.closest('.custom-select-wrapper')) {
        syncCustomSelects()
      } else {
        initCustomSelects()
      }
    })
  })

  observer.observe(document.body, { childList: true, subtree: true })
})

// ============================================================
// LOGOUT MODAL — shared component
// ============================================================

// ganti 2 baris ini sesuai punya lu:
const LOGOUT_REDIRECT_URL = 'index.html' // halaman login asli lu
const LOGOUT_SIMULATED_DELAY_MS = 900 // biar keliatan ada proses, bukan instan

function openLogoutModal() {
  document.getElementById('logoutModalOverlay')?.classList.add('show')
}
function closeLogoutModal() {
  document.getElementById('logoutModalOverlay')?.classList.remove('show')
}

function confirmLogout() {
  const btn = document.getElementById('logoutConfirmBtn')
  btn.disabled = true
  btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Keluar...`

  document.getElementById('logoutForm').submit()
}

// link "Log Out" di sidebar -- lu udah punya id="sidebarLogout" di
// linknya, jadi itu yang dipake duluan (paling presisi, gak nebak dari teks).
function isLogoutTrigger(el) {
  const link = el.closest('#sidebarLogout, [data-logout-btn], .sidebar-link, a, button')
  if (!link) return false
  if (link.id === 'sidebarLogout') return true
  if (link.hasAttribute('data-logout-btn')) return true
  return link.textContent.trim().toLowerCase() === 'log out'
}

document.addEventListener('click', (e) => {
  if (isLogoutTrigger(e.target)) {
    e.preventDefault()
    openLogoutModal()
    return
  }
  if (e.target.id === 'logoutCancelBtn') closeLogoutModal()
  if (e.target.id === 'logoutConfirmBtn') confirmLogout()
  if (e.target.id === 'logoutModalOverlay') closeLogoutModal() // klik area gelap
})

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeLogoutModal()
    closeConfirmDelete()
  }
})

// ============================================================
// GLOBAL CONFIRM DELETE DIALOG — reusable across all pages
// ============================================================

let _confirmDeleteCallback = null
let _confirmDeleteBtn = null

function openConfirmDelete(opts) {
  const overlay = document.getElementById('confirmDeleteOverlay')
  if (!overlay) return

  document.getElementById('confirmDeleteTitle').textContent = opts.title || 'Hapus Data?'
  document.getElementById('confirmDeleteMessage').textContent =
    opts.message || 'Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.'

  const confirmBtn = document.getElementById('confirmDeleteConfirmBtn')
  confirmBtn.innerHTML = opts.confirmText
    ? `<i class="fa-solid fa-trash"></i> ${opts.confirmText}`
    : '<i class="fa-solid fa-trash"></i> Hapus'
  confirmBtn.disabled = false

  _confirmDeleteCallback = opts.onConfirm || null
  _confirmDeleteBtn = confirmBtn

  overlay.classList.add('active')
  document.body.style.overflow = 'hidden'
}

function closeConfirmDelete() {
  const overlay = document.getElementById('confirmDeleteOverlay')
  if (!overlay) return``
  overlay.classList.remove('active')
  document.body.style.overflow = ''
  _confirmDeleteCallback = null
  if (_confirmDeleteBtn) _confirmDeleteBtn.disabled = false
  _confirmDeleteBtn = null
}

// Event listeners for the global confirm delete dialog
document.addEventListener('click', (e) => {
  if (e.target.id === 'confirmDeleteOverlay') closeConfirmDelete()
  if (e.target.closest('#confirmDeleteCloseBtn')) closeConfirmDelete()
  if (e.target.closest('#confirmDeleteCancelBtn')) closeConfirmDelete()
  if (e.target.closest('#confirmDeleteConfirmBtn') && _confirmDeleteCallback) {
    const btn = document.getElementById('confirmDeleteConfirmBtn')
    btn.disabled = true
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...'
    Promise.resolve(_confirmDeleteCallback()).then(() => {
      closeConfirmDelete()
    }).catch((err) => {
      closeConfirmDelete()
    })
  }
})
