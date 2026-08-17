const dashboardData = window.__DASHBOARD_DATA__ || { chart: [], jamaahTerbaru: [] }

document.addEventListener('DOMContentLoaded', () => {
  initArusKasChart()
  initDonasiChart()
  initJamaahChart()
  initZiswafChart()
  initCardMenu()
  initTableTools()
})

// Dropdown titik tiga di kartu Agenda Terdekat
function initCardMenu() {
  const btn = document.getElementById('agendaCardMenuBtn')
  const menu = document.getElementById('agendaCardMenu')
  if (!btn || !menu) return

  btn.addEventListener('click', (e) => {
    e.stopPropagation()
    menu.classList.toggle('open')
  })

  // Tutup saat klik di luar dropdown
  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !btn.contains(e.target)) {
      menu.classList.remove('open')
    }
  })

  // Tutup setelah salah satu menu diklik
  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => menu.classList.remove('open'))
  })
}

function initArusKasChart() {
  const canvas = document.getElementById('arusKasChart')
  if (!canvas || typeof Chart === 'undefined') return

  const ctx = canvas.getContext('2d')
  const isDark = () => document.body.classList.contains('dark-mode')

  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: dashboardData.chart.map((m) => m.label),
      datasets: [
        {
          label: 'Pemasukan',
          data: dashboardData.chart.map((m) => m.masuk),
          borderColor: isDark() ? '#2dd4bf' : '#0f766e',
          backgroundColor: isDark() ? 'rgba(45,212,191,0.10)' : 'rgba(15,118,110,0.08)',
          tension: 0.4,
          fill: true,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: 'Pengeluaran',
          data: dashboardData.chart.map((m) => m.keluar),
          borderColor: isDark() ? '#64748b' : '#b0b5bd',
          backgroundColor: 'transparent',
          tension: 0.4,
          fill: false,
          pointRadius: 0,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          grid: { color: isDark() ? '#2d3a4f' : '#f0f1f3' },
          ticks: {
            callback: (val) => 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k',
            color: isDark() ? '#94a3b8' : '#6b7280',
            font: { size: 11 },
          },
        },
        x: {
          grid: { display: false },
          ticks: { color: isDark() ? '#94a3b8' : '#6b7280', font: { size: 11 } },
        },
      },
    },
  })

  // Saat dark mode di-toggle (header atau menu Umum), sesuaikan warna grafik tanpa reload
  document.addEventListener('mosquehub:darkmode', () => {
    const dark = isDark()
    chart.data.datasets[0].borderColor = dark ? '#2dd4bf' : '#0f766e'
    chart.data.datasets[0].backgroundColor = dark ? 'rgba(45,212,191,0.10)' : 'rgba(15,118,110,0.08)'
    chart.data.datasets[1].borderColor = dark ? '#64748b' : '#b0b5bd'
    chart.options.scales.y.grid.color = dark ? '#2d3a4f' : '#f0f1f3'
    chart.options.scales.y.ticks.color = dark ? '#94a3b8' : '#6b7280'
    chart.options.scales.x.ticks.color = dark ? '#94a3b8' : '#6b7280'
    chart.update()
  })
}

// Grafik donasi & infaq per bulan (6 bulan terakhir)
function initDonasiChart() {
  const canvas = document.getElementById('donasiChart')
  if (!canvas || typeof Chart === 'undefined') return

  const data = dashboardData.donasiChart || []

  new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels: data.map((m) => m.label),
      datasets: [
        {
          label: 'Donasi',
          data: data.map((m) => m.total),
          backgroundColor: 'rgba(15,118,110,0.75)',
          borderRadius: 6,
          maxBarThickness: 34,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          grid: { color: '#f0f1f3' },
          ticks: { callback: (val) => 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k', color: '#6b7280', font: { size: 11 } },
        },
        x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
      },
    },
  })
}

// Grafik pertumbuhan jemaah (jemaah baru per bulan, 6 bulan terakhir)
function initJamaahChart() {
  const canvas = document.getElementById('jamaahChart')
  if (!canvas || typeof Chart === 'undefined') return

  const data = dashboardData.jamaahChart || []

  new Chart(canvas.getContext('2d'), {
    type: 'line',
    data: {
      labels: data.map((m) => m.label),
      datasets: [
        {
          label: 'Jemaah Baru',
          data: data.map((m) => m.baru),
          borderColor: '#0f766e',
          backgroundColor: 'rgba(15,118,110,0.10)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#f0f1f3' },
          ticks: { precision: 0, color: '#6b7280', font: { size: 11 } },
        },
        x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
      },
    },
  })
}

// Distribusi ziswaf (donut) berdasarkan kategori
function initZiswafChart() {
  const canvas = document.getElementById('ziswafChart')
  if (!canvas || typeof Chart === 'undefined') return

  const data = dashboardData.ziswafDist || []
  const colors = ['#0f766e', '#14b8a6', '#5eead4', '#f59e0b', '#8b5cf6', '#64748b']

  new Chart(canvas.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: data.map((d) => d.label),
      datasets: [
        {
          data: data.map((d) => d.total),
          backgroundColor: colors,
          borderWidth: 2,
          borderColor: '#ffffff',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, boxHeight: 10, font: { size: 11 }, color: '#6b7280' },
        },
        tooltip: {
          callbacks: {
            label: (ctx) => ' ' + ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID'),
          },
        },
      },
    },
  })
}

// Cari + Urutkan + Saring tabel "Aktivitas Jemaah Terakhir"
function initTableTools() {
  const searchInput = document.getElementById('tableSearch')
  const sortSelect = document.getElementById('sortFilter')
  const statusSelect = document.getElementById('statusFilter')
  const tableBody = document.getElementById('jamaahTableBody')
  if (!searchInput || !tableBody) return

  function apply() {
    const keyword = searchInput.value.toLowerCase()
    const status = statusSelect ? statusSelect.value : ''
    const sort = sortSelect ? sortSelect.value : 'nama-asc'

    const rows = Array.from(tableBody.querySelectorAll('tr'))

    // Filter: kata kunci (nama) + status
    rows.forEach((row) => {
      const name = (row.children[2]?.textContent || '').toLowerCase() // kolom "Nama"
      const rowStatus = row.dataset.status || ''
      const matchSearch = name.includes(keyword)
      const matchStatus = !status || rowStatus === status
      row.style.display = matchSearch && matchStatus ? '' : 'none'
    })

    // Urutkan baris yang terlihat
    const visible = rows.filter((row) => row.style.display !== 'none')
    visible.sort((a, b) => {
      const nameA = (a.children[2]?.textContent || '').toLowerCase()
      const nameB = (b.children[2]?.textContent || '').toLowerCase()
      const dateA = a.dataset.tanggal || ''
      const dateB = b.dataset.tanggal || ''
      switch (sort) {
        case 'nama-desc':
          return nameB.localeCompare(nameA, 'id')
        case 'terakhir-baru':
          return dateB.localeCompare(dateA)
        case 'terakhir-lama':
          return dateA.localeCompare(dateB)
        default:
          return nameA.localeCompare(nameB, 'id')
      }
    })
    visible.forEach((row) => tableBody.appendChild(row))
  }

  searchInput.addEventListener('input', apply)
  sortSelect?.addEventListener('change', apply)
  statusSelect?.addEventListener('change', apply)
}
