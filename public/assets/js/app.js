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

function initCustomSelects() {
  document.querySelectorAll('.filter-select, .time-select').forEach((nativeSelect) => {
    // Skip kalo udah di-wrap
    if (nativeSelect.closest('.custom-select-wrapper')) return

    const wrapper = document.createElement('div')
    wrapper.className = 'custom-select-wrapper'

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
  document.addEventListener('click', closeAllCustomSelects)
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
  document.querySelectorAll('.filter-select, .time-select').forEach((nativeSelect) => {
    const wrapper = nativeSelect.closest('.custom-select-wrapper')
    if (!wrapper) return

    const trigger = wrapper.querySelector('.custom-select-trigger')
    const optionsContainer = wrapper.querySelector('.custom-select-options')

    // Rebuild opsi dari native select
    buildCustomOptions(nativeSelect, optionsContainer, trigger)

    // Update teks trigger
    trigger.querySelector('.custom-select-text').textContent = getSelectedText(nativeSelect)
  })
}

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
  if (!overlay) return
  overlay.classList.remove('active')
  document.body.style.overflow = ''
  _confirmDeleteCallback = null
  if (_confirmDeleteBtn) _confirmDeleteBtn.disabled = false
  _confirmDeleteBtn = null
}

// Event listeners for the global confirm delete dialog
document.addEventListener('click', (e) => {
  if (e.target.id === 'confirmDeleteOverlay') closeConfirmDelete()
  if (e.target.id === 'confirmDeleteCloseBtn') closeConfirmDelete()
  if (e.target.id === 'confirmDeleteCancelBtn') closeConfirmDelete()
  if (e.target.id === 'confirmDeleteConfirmBtn' && _confirmDeleteCallback) {
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
