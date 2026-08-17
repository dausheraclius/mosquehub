/* ============================================================
   JADWAL KEGIATAN — interaksi halaman
   Daftar agenda dipaginate di server (maks 10/halaman).
   Filter (cari / tanggal / kategori / lokasi / status) dan
   ganti halaman memuat ulang daftar via AJAX dari endpoint yang
   sama (?partial=1), jadi server hanya mengirim 10 agenda saja.
   ============================================================ */

// Toggle panel detail (accordion) per kartu kegiatan
function jkToggleDetail(btn) {
  const panel = btn.closest('.jk-activity-card').querySelector('.jk-detail-panel')
  const isOpen = panel.classList.toggle('open')
  btn.textContent = isOpen ? 'Tutup' : 'Detail'
}

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('jkSearch')
  const kategoriSelect = document.getElementById('jkKategori')
  const lokasiSelect = document.getElementById('jkLokasi')
  const statusSelect = document.getElementById('jkStatus')
  const dateInput = document.getElementById('jkDate')
  const resetBtn = document.getElementById('jkReset')
  const listWrap = document.getElementById('jkListWrap')
  const statCards = document.querySelectorAll('.jk-stat-card')

  if (!listWrap || !searchInput) return // halaman lain yang meng-include app.js tidak terpengaruh

  let debounceTimer = null

  // Kumpulkan nilai filter + halaman jadi query string untuk request AJAX.
  function currentParams(page = 1) {
    const params = new URLSearchParams()
    if (searchInput.value.trim()) params.set('search', searchInput.value.trim())
    if (dateInput.value) params.set('tanggal', dateInput.value)
    if (kategoriSelect.value) params.set('kategori', kategoriSelect.value)
    if (lokasiSelect.value) params.set('lokasi', lokasiSelect.value)
    if (statusSelect.value) params.set('status', statusSelect.value)
    if (page > 1) params.set('page', page)
    params.set('partial', '1')
    return params
  }

  async function loadPage(page = 1) {
    clearTimeout(debounceTimer)

    const url = window.location.pathname + '?' + currentParams(page).toString()
    listWrap.classList.add('loading')

    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      if (!res.ok) throw new Error('gagal memuat data')

      const html = await res.text()
      listWrap.innerHTML = html
      listWrap.scrollIntoView({ behavior: 'smooth', block: 'start' })

      // Simpan filter di URL biar bisa di-refresh / di-share tanpa kehilangan state.
      const stateParams = currentParams(page)
      stateParams.delete('partial')
      const query = stateParams.toString()
      history.replaceState(null, '', window.location.pathname + (query ? '?' + query : ''))
    } catch (err) {
      if (typeof showToast === 'function') {
        showToast('Gagal memuat data agenda.', 'fa-solid fa-triangle-exclamation')
      }
    } finally {
      listWrap.classList.remove('loading')
    }
  }

  // Filter berubah → muat ulang dari halaman 1 (search di-debounce biar tidak spam request).
  function onFilterChange() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => loadPage(1), 300)
  }

  searchInput.addEventListener('input', onFilterChange)
  dateInput.addEventListener('change', onFilterChange)
  kategoriSelect.addEventListener('change', onFilterChange)
  lokasiSelect.addEventListener('change', onFilterChange)
  statusSelect.addEventListener('change', onFilterChange)

  // Klik stat card = filter berdasarkan status.
  statCards.forEach(function (card) {
    card.addEventListener('click', function () {
      statCards.forEach((c) => c.classList.remove('active'))
      card.classList.add('active')
      statusSelect.value = card.dataset.statusFilter || ''
      if (typeof syncCustomSelects === 'function') syncCustomSelects()
      loadPage(1)
    })
  })

  resetBtn.addEventListener('click', function () {
    searchInput.value = ''
    dateInput.value = ''
    kategoriSelect.value = ''
    lokasiSelect.value = ''
    statusSelect.value = ''
    statCards.forEach((c) => c.classList.remove('active'))
    statCards[0]?.classList.add('active')
    if (typeof syncCustomSelects === 'function') syncCustomSelects()
    loadPage(1)
  })

  // Klik tombol pagination (prev / nomor halaman / next) — delegation.
  listWrap.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-page]')
    if (!btn || btn.disabled) return
    loadPage(Number(btn.dataset.page))
  })
})
