document.addEventListener('DOMContentLoaded', () => {
  initArusKasChart()
  initTableSearch()
})

function initArusKasChart() {
  const canvas = document.getElementById('arusKasChart')
  if (!canvas || typeof Chart === 'undefined') return

  const ctx = canvas.getContext('2d')

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['6 Jan', 'Feb', 'Mar', 'Apr', 'Mei', '6 Jun'],
      datasets: [
        {
          label: 'Pemasukan',
          data: [40, 65, 55, 90, 130, 100],
          borderColor: '#0f766e',
          backgroundColor: 'rgba(15,118,110,0.08)',
          tension: 0.4,
          fill: true,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: 'Pengeluaran',
          data: [20, 35, 45, 55, 70, 95],
          borderColor: '#b0b5bd',
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
          grid: { color: '#f0f1f3' },
          ticks: {
            callback: (val) => val + 'k',
            font: { size: 11 },
          },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 } },
        },
      },
    },
  })
}

function initTableSearch() {
  const searchInput = document.getElementById('tableSearch')
  const tableBody = document.getElementById('jamaahTableBody')
  if (!searchInput || !tableBody) return

  searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase()
    const rows = tableBody.querySelectorAll('tr')

    rows.forEach((row) => {
      const nameCell = row.children[2] // kolom "Nama"
      const match = nameCell.textContent.toLowerCase().includes(keyword)
      row.style.display = match ? '' : 'none'
    })
  })
}
