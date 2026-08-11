/* ============================================================
   JADWAL KEGIATAN — interaksi halaman
   Prototype only: filter & accordion berjalan di client-side,
   tidak ada koneksi backend/database.
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
  const emptyState = document.getElementById('jkEmpty')
  const items = document.querySelectorAll('.jk-timeline-item')

  if (!searchInput) return // halaman lain yang meng-include app.js tidak terpengaruh

  function applyFilters() {
    const query = searchInput.value.toLowerCase().trim()
    const kategori = kategoriSelect.value
    const lokasi = lokasiSelect.value
    const status = statusSelect.value
    let visibleCount = 0

    items.forEach(function (item) {
      const matchQuery = !query || item.dataset.name.includes(query)
      const matchKategori = !kategori || item.dataset.category === kategori
      const matchLokasi = !lokasi || item.dataset.location === lokasi
      const matchStatus = !status || item.dataset.status === status
      const visible = matchQuery && matchKategori && matchLokasi && matchStatus

      item.style.display = visible ? '' : 'none'
      if (visible) visibleCount++
    })

    emptyState.classList.toggle('show', visibleCount === 0)
  }

  searchInput.addEventListener('input', applyFilters)
  kategoriSelect.addEventListener('change', applyFilters)
  lokasiSelect.addEventListener('change', applyFilters)
  statusSelect.addEventListener('change', applyFilters)
  dateInput.addEventListener('change', applyFilters) // catatan: tanggal hanya untuk tampilan di prototype ini

  resetBtn.addEventListener('click', function () {
    searchInput.value = ''
    kategoriSelect.value = ''
    lokasiSelect.value = ''
    statusSelect.value = ''
    applyFilters()
  })
})
