// Data dummy transaksi
const dataTransaksi = window.__KAS_DATA__ || []

const dataAktivitas = window.__AKTIVITAS_DATA__ || []

function renderActivity() {
  const list = document.getElementById('activityList')
  list.innerHTML = ''
  dataAktivitas.forEach((item) => {
    const icon = item.tipe === 'plus' ? 'fa-plus' : 'fa-minus'
    list.insertAdjacentHTML(
      'beforeend',
      `
      <li class="activity-item">
        <div class="activity-left">
          <div class="activity-icon ${item.tipe}"><i class="fa-solid ${icon}"></i></div>
          <span class="activity-title">${esc(item.title)}</span>
        </div>
        <span class="activity-time">${item.time}</span>
      </li>
    `,
    )
  })
}

function renderTable(data) {
  const tbody = document.getElementById('kasTableBody')
  tbody.innerHTML = ''

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:24px; color:var(--text-muted);">Data tidak ditemukan</td></tr>`
    return
  }

  data.forEach((item) => {
    tbody.insertAdjacentHTML(
      'beforeend',
      `
      <tr>
        <td>${item.tanggal}</td>
        <td>${esc(item.jenis)}</td>
        <td>${esc(item.kategori)}</td>
        <td>${esc(item.ket)}</td>
        <td class="amount-in">${formatRupiah(item.masuk)}</td>
        <td class="amount-out">${formatRupiah(item.keluar)}</td>
        <td>
          <button class="btn-sm btn-detail" data-id="${item.id}" title="Lihat Detail"><i class="fa-regular fa-eye"></i></button>
          <button class="btn-sm btn-edit" data-id="${item.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
          <button class="btn-sm btn-hapus" data-id="${item.id}" title="Hapus"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `,
    )
  })
}

function applyFilter() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const kategori = document.getElementById('kategoriFilter').value
  const bulan = document.getElementById('bulanFilter').value

  const filtered = dataTransaksi.filter((item) => {
    const matchSearch =
      (item.ket || '').toLowerCase().includes(keyword) || (item.jenis || '').toLowerCase().includes(keyword)
    const matchKategori = kategori === '' || (item.kategori || '') === kategori
    const matchBulan = bulan === '' || (item.tanggalIso || '').startsWith(bulan)
    return matchSearch && matchKategori && matchBulan
  })

  renderTable(filtered)
}

document.getElementById('searchInput').addEventListener('input', applyFilter)
document.getElementById('kategoriFilter').addEventListener('change', applyFilter)
document.getElementById('bulanFilter').addEventListener('change', applyFilter)

// ============================
// CHART & STAT — dihitung dari data asli (kas_transactions)
// ============================
let kasChart = null

function pad2(n) {
  return String(n).padStart(2, '0')
}

function monthKeyOf(tanggalIso) {
  const [y, m] = tanggalIso.split('-')
  return `${y}-${m}`
}

function getChartData() {
  const now = new Date()
  const months = []
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    months.push({
      key: `${d.getFullYear()}-${pad2(d.getMonth() + 1)}`,
      label: d.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }),
      masuk: 0,
      keluar: 0,
      saldo: 0,
    })
  }

  const startKey = months[0].key
  let saldo = 0

  dataTransaksi.forEach((t) => {
    const key = monthKeyOf(t.tanggalIso)
    if (key < startKey) {
      saldo += t.masuk - t.keluar
    } else {
      const month = months.find((m) => m.key === key)
      if (month) {
        month.masuk += t.masuk
        month.keluar += t.keluar
      }
    }
  })

  months.forEach((m) => {
    saldo += m.masuk - m.keluar
    m.saldo = saldo
  })

  return months
}

function renderChart() {
  const ctx = document.getElementById('trenSaldoChart')
  if (!ctx || typeof Chart === 'undefined') return

  const months = getChartData()

  if (kasChart) kasChart.destroy()

  kasChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months.map((m) => m.label),
      datasets: [
        {
          data: months.map((m) => m.saldo),
          borderColor: '#0f766e',
          backgroundColor: 'rgba(15,118,110,0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: (v) => 'Rp ' + v.toLocaleString('id-ID') } },
      },
    },
  })
}

function recomputeStats() {
  const now = new Date()
  const thisMonth = `${now.getFullYear()}-${pad2(now.getMonth() + 1)}`
  const thisYear = String(now.getFullYear())

  let saldo = 0
  let pemasukanBulan = 0
  let pengeluaranBulan = 0
  let pemasukanTahun = 0
  let pengeluaranTahun = 0

  dataTransaksi.forEach((t) => {
    const [y, m] = t.tanggalIso.split('-').map(Number)
    const key = `${y}-${pad2(m)}`
    saldo += t.masuk - t.keluar
    if (key === thisMonth) {
      pemasukanBulan += t.masuk
      pengeluaranBulan += t.keluar
    }
    if (String(y) === thisYear) {
      pemasukanTahun += t.masuk
      pengeluaranTahun += t.keluar
    }
  })

  const rupiah = (v) => 'Rp ' + v.toLocaleString('id-ID')

  document.getElementById('saldoSekarang').textContent = rupiah(saldo)
  document.getElementById('pemasukanBulanIni').textContent = rupiah(pemasukanBulan)
  document.getElementById('pengeluaranBulanIni').textContent = rupiah(pengeluaranBulan)
  document.getElementById('statPemasukan').textContent = rupiah(pemasukanTahun)
  document.getElementById('statPengeluaran').textContent = rupiah(pengeluaranTahun)
  document.getElementById('statJumlah').textContent = dataTransaksi.length
  document.getElementById('statSaldoAkhir').textContent = rupiah(saldo)
}

function refreshAfterMutation() {
  applyFilter()
  recomputeStats()
  renderChart()
}

// ============================
// EKSPOR CSV
// ============================
document.getElementById('btnExport').addEventListener('click', () => {
  const rows = [['Tanggal', 'Jenis Transaksi', 'Kategori', 'Keterangan', 'Pemasukan', 'Pengeluaran', 'Dibuat Oleh']]

  dataTransaksi.forEach((t) => {
    rows.push([t.tanggal, t.jenis, t.kategori || '', t.ket || '', t.masuk, t.keluar, t.oleh || ''])
  })

  const csv = rows
    .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(','))
    .join('\n')

  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `kas-masjid-${new Date().toISOString().slice(0, 10)}.csv`
  link.click()
  URL.revokeObjectURL(link.href)

  showToast('Data berhasil diekspor ke CSV.', 'fa-solid fa-download')
})

  // MODAL TRANSAKSI BARU / EDIT
const transaksiModalOverlay = document.getElementById('transaksiModalOverlay')
const transaksiForm = document.getElementById('transaksiForm')

let editingId = null
let detailId = null

function openTransaksiModal(id = null) {
  editingId = id ? Number(id) : null

  const item = editingId ? dataTransaksi.find((d) => Number(d.id) === editingId) : null

  document.getElementById('transaksiModalTitle').textContent = item ? 'Edit Transaksi' : 'Transaksi Baru'
  document.getElementById('transaksiSubmitBtn').innerHTML = item
    ? '<i class="fa-solid fa-check"></i> Simpan Perubahan'
    : '<i class="fa-solid fa-check"></i> Simpan Transaksi'

  if (item) {
    document.getElementById('txTipe').value = item.tipe
    document.getElementById('txTanggal').value = item.tanggalIso
    document.getElementById('txJumlah').value = item.masuk > 0 ? item.masuk : item.keluar
    document.getElementById('txJenis').value = item.jenis
    document.getElementById('txKategori').value = item.kategori || ''
    document.getElementById('txKeterangan').value = item.ket || ''
  } else {
    transaksiForm.reset()
    document.getElementById('txTanggal').value = new Date().toISOString().slice(0, 10)
  }

  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  transaksiModalOverlay.classList.add('active')
}

function closeTransaksiModal() {
  transaksiModalOverlay.classList.remove('active')
  transaksiForm.reset()
  editingId = null
}

document.getElementById('btnTransaksiBaru').addEventListener('click', () => openTransaksiModal())
document.getElementById('closeTransaksiModal').addEventListener('click', closeTransaksiModal)
document.getElementById('cancelTransaksiModal').addEventListener('click', closeTransaksiModal)

transaksiModalOverlay.addEventListener('click', (e) => {
  if (e.target === transaksiModalOverlay) closeTransaksiModal()
})

transaksiForm.addEventListener('submit', async (e) => {
  e.preventDefault()

  const isEditing = !!editingId
  const url = isEditing ? `/keuangan/kas-masjid/${editingId}` : '/keuangan/kas-masjid'

  const payload = {
    tanggal: document.getElementById('txTanggal').value,
    jenis: document.getElementById('txJenis').value || document.getElementById('txTipe').value,
    kategori: document.getElementById('txKategori').value,
    keterangan: document.getElementById('txKeterangan').value,
    tipe: document.getElementById('txTipe').value,
    jumlah: document.getElementById('txJumlah').value,
  }

  try {
    const res = await fetch(url, {
      method: isEditing ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrf(),
      },
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      await showFetchError(res, 'Gagal menyimpan transaksi.')
      return
    }

    const savedItem = await res.json()

    if (isEditing) {
      const idx = dataTransaksi.findIndex((d) => Number(d.id) === editingId)
      if (idx > -1) dataTransaksi[idx] = savedItem
    } else {
      dataTransaksi.unshift(savedItem)
    }

    closeTransaksiModal()
    refreshAfterMutation()
    showToast(isEditing ? 'Transaksi berhasil diperbarui.' : 'Transaksi berhasil disimpan.')
  } catch (err) {
    showToast('Terjadi kesalahan saat menyimpan transaksi.', 'fa-solid fa-triangle-exclamation')
  }
 })

// ============================
// DETAIL TRANSAKSI MODAL
// ============================
const detailModalOverlay = document.getElementById('detailModalOverlay')

function openDetailModal(id) {
  const item = dataTransaksi.find((d) => Number(d.id) === Number(id))
  if (!item) return

  detailId = Number(id)

  const tipeBadge =
    item.tipe === 'Pemasukan'
      ? `<span class="tipe-badge in">Pemasukan</span>`
      : `<span class="tipe-badge out">Pengeluaran</span>`

  document.getElementById('detailTipe').innerHTML = tipeBadge
  document.getElementById('detailTanggal').textContent = item.tanggal
  document.getElementById('detailJenis').textContent = item.jenis
  document.getElementById('detailKategori').textContent = item.kategori || '-'
  document.getElementById('detailJumlah').textContent = formatRupiah(item.masuk > 0 ? item.masuk : item.keluar)
  document.getElementById('detailKeterangan').textContent = item.ket || '-'
  document.getElementById('detailOleh').textContent = item.oleh || '-'

  detailModalOverlay.classList.add('active')
}

function closeDetailModal() {
  detailModalOverlay.classList.remove('active')
  detailId = null
}

document.getElementById('closeDetailModal').addEventListener('click', closeDetailModal)
document.getElementById('closeDetailModalBtn').addEventListener('click', closeDetailModal)

detailModalOverlay.addEventListener('click', (e) => {
  if (e.target === detailModalOverlay) closeDetailModal()
})

document.getElementById('editFromDetailBtn').addEventListener('click', () => {
  if (!detailId) return
  closeDetailModal()
  openTransaksiModal(detailId)
})

// ============================
// HAPUS TRANSAKSI (pakai dialog konfirmasi global)
// ============================
function openDeleteModal(id) {
  const item = dataTransaksi.find((d) => Number(d.id) === Number(id))
  if (!item) return

  openConfirmDelete({
    title: 'Hapus Transaksi?',
    message: `Yakin ingin menghapus transaksi "${item.jenis}"? Tindakan ini tidak bisa dibatalkan.`,
    onConfirm: async () => {

      try {
        const res = await fetch(`/keuangan/kas-masjid/${item.id}`, {
          method: 'DELETE',
          headers: getHeaders(),
        })
        if (!res.ok) throw new Error('gagal hapus transaksi')

        const idx = dataTransaksi.findIndex((d) => Number(d.id) === Number(item.id))
        if (idx > -1) dataTransaksi.splice(idx, 1)
        refreshAfterMutation()
        showToast('Transaksi berhasil dihapus.', 'fa-solid fa-trash')
      } catch (err) {
        showToast('Gagal menghapus transaksi.', 'fa-solid fa-triangle-exclamation')
        throw err
      }
    },
  })
}

// Event delegation untuk tombol Detail/Edit/Hapus (baris dirender ulang tiap filter)
document.getElementById('kasTableBody').addEventListener('click', (e) => {
  const detailBtn = e.target.closest('.btn-detail')
  if (detailBtn) {
    openDetailModal(detailBtn.dataset.id)
    return
  }
  const editBtn = e.target.closest('.btn-edit')
  if (editBtn) {
    openTransaksiModal(editBtn.dataset.id)
    return
  }
  const deleteBtn = e.target.closest('.btn-hapus')
  if (deleteBtn) {
    openDeleteModal(deleteBtn.dataset.id)
    return
  }
})

// Escape menutup modal manapun yang sedang aktif
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return
  if (transaksiModalOverlay.classList.contains('active')) closeTransaksiModal()
  if (detailModalOverlay.classList.contains('active')) closeDetailModal()
})

document.addEventListener('DOMContentLoaded', () => {
  renderTable(dataTransaksi)
  renderActivity()
  recomputeStats()
  renderChart()
})
