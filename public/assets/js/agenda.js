// --- Data dummy agenda, key-nya format "YYYY-MM-DD" ---
let agendaData = Array.isArray(window.__AGENDA_DATA__) ? window.__AGENDA_DATA__ : []

let activeTab = 'hari-ini'
let editingId = null
let currentDetailItem = null

// --- Helper: request API dengan auto-refresh token CSRF (saat 419) ---
function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]').content
}

async function refreshCsrf() {
  const res = await fetch('/csrf-token')
  const data = await res.json()
  if (data.token) document.querySelector('meta[name="csrf-token"]').content = data.token
  return data.token
}

async function apiRequest(url, options = {}) {
  let res = await fetch(url, options)
  if (res.status === 419 && !options._retried) {
    options._retried = true
    const fresh = await refreshCsrf()
    options.headers['X-CSRF-TOKEN'] = fresh
    res = await fetch(url, options)
    if (res.status === 419) {
      showToast('Sesi berakhir, halaman akan dimuat ulang...', 'fa-solid fa-triangle-exclamation')
      setTimeout(() => location.reload(), 1200)
    }
  }
  return res
}

// --- Generate agenda rutin Sholat Jumat otomatis ---
function isJumat(dateKey) {
  return new Date(dateKey + 'T00:00:00').getDay() === 5 // 5 = Jumat
}

function buatAgendaJumatOtomatis(dateKey) {
  return {
    id: `jumat-${dateKey}`,
    tanggal: dateKey,
    nama: 'Sholat Jumat Berjamaah',
    kategori: 'Ibadah',
    jamMulai: '12:00',
    jamSelesai: '13:00',
    lokasi: 'YMBPK Baiturrahim (Aula Utama)',
    pemateri: 'Ust. Hakim',
    pj: 'Takmir Masjid',
    peserta: '-',
    status: 'Rutin',
    deskripsi: 'Jadwal rutin mingguan, otomatis setiap hari Jumat.',
    isRecurring: true,
  }
}

function getEventsOnDate(dateKey, filteredData) {
  const manual = filteredData.filter((item) => item.tanggal === dateKey)
  if (manual.length > 0) return manual
  if (isJumat(dateKey)) return [buatAgendaJumatOtomatis(dateKey)]
  return []
}

let currentDate = new Date()

const monthNames = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
]
const statusClassMap = {
  Berlangsung: 'status-berlangsung',
  Selesai: 'status-selesai',
  'Akan Datang': 'status-akan-datang',
  Dibatalkan: 'status-nonaktif',
  Rutin: 'status-rutin',
}

function pad(n) {
  return n.toString().padStart(2, '0')
}
function toDateKey(d) {
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

function getFilteredData() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const kategori = document.getElementById('kategoriFilter').value
  const status = document.getElementById('statusFilter').value

  return agendaData.filter((item) => {
    const matchSearch = item.nama.toLowerCase().includes(keyword)
    const matchKategori = kategori === '' || item.kategori === kategori
    const matchStatus = status === '' || item.status === status
    return matchSearch && matchKategori && matchStatus
  })
}

// ============ KALENDER ============
function populateMonthYearSelect() {
  const monthSelect = document.getElementById('monthSelect')
  const yearSelect = document.getElementById('yearSelect')
  monthSelect.innerHTML = monthNames.map((m, i) => `<option value="${i}">${m}</option>`).join('')
  yearSelect.innerHTML = [2024, 2025, 2026, 2027].map((y) => `<option value="${y}">${y}</option>`).join('')
}

function renderCalendar() {
  const year = currentDate.getFullYear()
  const month = currentDate.getMonth()
  document.getElementById('monthSelect').value = month
  document.getElementById('yearSelect').value = year

  const filtered = getFilteredData()

  const firstDay = new Date(year, month, 1).getDay()
  const totalDays = new Date(year, month + 1, 0).getDate()
  const prevMonthDays = new Date(year, month, 0).getDate()
  const todayKey = toDateKey(new Date())

  const grid = document.getElementById('calendarGrid')
  grid.innerHTML = ''

  for (let i = firstDay - 1; i >= 0; i--) {
    grid.insertAdjacentHTML('beforeend', `<div class="calendar-day other-month">${prevMonthDays - i}</div>`)
  }

  for (let d = 1; d <= totalDays; d++) {
    const dateObj = new Date(year, month, d)
    const dateKey = toDateKey(dateObj)
    const isToday = dateKey === todayKey
    const events = getEventsOnDate(dateKey, filtered)
    const hasEvent = events.length > 0

    grid.insertAdjacentHTML(
      'beforeend',
      `
      <div class="calendar-day ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : 'empty-day'}" data-date="${dateKey}">
        ${d}
        ${hasEvent ? '<span class="calendar-day-dot"></span>' : ''}
      </div>
    `,
    )
  }

  document.querySelectorAll('.calendar-day.has-event, .calendar-day.empty-day').forEach((el) => {
    el.addEventListener('click', () => {
      const dateKey = el.dataset.date
      const events = getEventsOnDate(dateKey, filtered)
      if (events.length > 0) {
        openDetailModal(events[0])
      } else {
        openFormModal(dateKey)
      }
    })
  })
}

document.getElementById('prevMonthBtn').addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() - 1)
  renderCalendar()
})

document.getElementById('nextMonthBtn').addEventListener('click', () => {
  currentDate.setMonth(currentDate.getMonth() + 1)
  renderCalendar()
})

document.getElementById('todayBtn').addEventListener('click', () => {
  currentDate = new Date()
  renderCalendar()
})

document.getElementById('monthSelect').addEventListener('change', (e) => {
  currentDate.setMonth(parseInt(e.target.value))
  renderCalendar()
})

document.getElementById('yearSelect').addEventListener('change', (e) => {
  currentDate.setFullYear(parseInt(e.target.value))
  renderCalendar()
})

// ============ PANEL AKTIVITAS (tabs) ============
function renderJadwal() {
  const today = new Date()
  const todayKey = toDateKey(today)
  const tomorrow = new Date(today)
  tomorrow.setDate(today.getDate() + 1)
  const tomorrowKey = toDateKey(tomorrow)
  const weekEnd = new Date(today)
  weekEnd.setDate(today.getDate() + 7)

  const filtered = getFilteredData()
  let list = []

  if (activeTab === 'hari-ini') {
    list = getEventsOnDate(todayKey, filtered)
  } else if (activeTab === 'besok') {
    list = getEventsOnDate(tomorrowKey, filtered)
  } else {
    list = []
    for (let d = new Date(today); d <= weekEnd; d.setDate(d.getDate() + 1)) {
      list = list.concat(getEventsOnDate(toDateKey(d), filtered))
    }
  }

  const el = document.getElementById('jadwalList')
  el.innerHTML = ''

  if (list.length === 0) {
    el.innerHTML = `<li style="text-align:center; color:var(--text-muted); font-size:12.5px; padding:20px 0;">Tidak ada agenda</li>`
    return
  }

  list.forEach((item) => {
    el.insertAdjacentHTML(
      'beforeend',
      `
      <li class="jadwal-item">
        <span class="jadwal-time">${item.jamMulai}</span>
        <div class="jadwal-dot"><i class="fa-solid fa-circle" style="font-size:6px;"></i></div>
        <div class="jadwal-content">
          <div class="jadwal-title">${item.nama}</div>
          <div class="jadwal-loc"><i class="fa-solid fa-location-dot"></i> ${item.lokasi}</div>
          <span class="status-badge ${statusClassMap[item.status]} jadwal-status">${item.status}</span>
        </div>
      </li>
    `,
    )
  })
}

document.querySelectorAll('.timeline-tab').forEach((tab) => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.timeline-tab').forEach((t) => t.classList.remove('active'))
    tab.classList.add('active')
    activeTab = tab.dataset.tab
    renderJadwal()
  })
})

// ============ MODAL: FORM (Tambah/Edit) ============
function openFormModal(dateKey, existingItem = null) {
  editingId = existingItem ? existingItem.id : null
  document.getElementById('formModalTitle').textContent = existingItem ? 'Edit Agenda' : 'Tambah Agenda'
  document.getElementById('formNama').value = existingItem?.nama || ''
  document.getElementById('formKategori').value = existingItem?.kategori || 'Kajian'
  document.getElementById('formTanggal').value = existingItem?.tanggal || dateKey
  document.getElementById('formJamMulai').value = existingItem?.jamMulai || ''
  setTimeControls('formJamMulai', existingItem?.jamMulai || '')
  document.getElementById('formJamSelesai').value = existingItem?.jamSelesai || ''
  setTimeControls('formJamSelesai', existingItem?.jamSelesai || '')
  if (typeof syncCustomSelects === 'function') syncCustomSelects()
  document.getElementById('formLokasi').value = existingItem?.lokasi || ''
  document.getElementById('formPemateri').value = existingItem?.pemateri || ''
  document.getElementById('formPJ').value = existingItem?.pj || ''
  document.getElementById('formPeserta').value = existingItem?.peserta || ''
  document.getElementById('formStatus').value = existingItem?.status || 'Akan Datang'
  document.getElementById('formDeskripsi').value = existingItem?.deskripsi || ''
  document.getElementById('formRepeat').value = existingItem?.repeat || 'Tidak Berulang'
  document.getElementById('formReminder').value = existingItem?.reminder || '30 Menit Sebelum'

  document.getElementById('detailModal').classList.remove('show')
  document.getElementById('formModal').classList.add('show')
}

function closeFormModal() {
  document.getElementById('formModal').classList.remove('show')
  editingId = null
}

document.getElementById('closeFormModal').addEventListener('click', closeFormModal)
document.getElementById('cancelFormBtn').addEventListener('click', closeFormModal)

document.getElementById('saveFormBtn').addEventListener('click', async () => {
  const nama = document.getElementById('formNama').value.trim()
  if (!nama) {
    showToast('Nama Agenda wajib diisi.', 'fa-solid fa-triangle-exclamation')
    return
  }

  const payload = {
    nama,
    kategori: document.getElementById('formKategori').value,
    tanggal: document.getElementById('formTanggal').value,
    jam_mulai: document.getElementById('formJamMulai').value,
    jam_selesai: document.getElementById('formJamSelesai').value,
    lokasi: document.getElementById('formLokasi').value,
    pemateri: document.getElementById('formPemateri').value,
    pj: document.getElementById('formPJ').value,
    peserta: document.getElementById('formPeserta').value || null,
    status: document.getElementById('formStatus').value,
    deskripsi: document.getElementById('formDeskripsi').value,
  }

  const csrf = getCsrf()
  const isEdit = !!editingId
  const url = isEdit ? `/kegiatan/agenda/${editingId}` : '/kegiatan/agenda'
  const method = isEdit ? 'PUT' : 'POST'

  try {
    const res = await apiRequest(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(payload),
    })
    if (!res.ok) {
      const errData = await res.json()
      const firstError = Object.values(errData.errors || {})[0]?.[0] || 'Gagal menyimpan agenda.'
      showToast(firstError, 'fa-solid fa-triangle-exclamation')
      return
    }
    const saved = await res.json()

    if (isEdit) {
      const idx = agendaData.findIndex((i) => i.id === saved.id)
      agendaData[idx] = saved
    } else {
      agendaData.push(saved)
    }

    closeFormModal()
    renderCalendar()
    renderJadwal()
  } catch (err) {
    showToast('Terjadi kesalahan saat menyimpan agenda.', 'fa-solid fa-triangle-exclamation')
  }
})

// ============ MODAL: DETAIL ============
function openDetailModal(item) {
  currentDetailItem = item
  const content = document.getElementById('detailContent')
  content.innerHTML = `
    <div class="detail-row"><span class="detail-label">Nama Agenda</span><span class="detail-value">${item.nama}</span></div>
    <div class="detail-row"><span class="detail-label">Kategori</span><span class="detail-value">${item.kategori}</span></div>
    <div class="detail-row"><span class="detail-label">Tanggal</span><span class="detail-value">${item.tanggal}</span></div>
    <div class="detail-row"><span class="detail-label">Jam</span><span class="detail-value">${item.jamMulai} - ${item.jamSelesai}</span></div>
    <div class="detail-row"><span class="detail-label">Lokasi</span><span class="detail-value">${item.lokasi}</span></div>
    <div class="detail-row"><span class="detail-label">Pemateri</span><span class="detail-value">${item.pemateri || '-'}</span></div>
    <div class="detail-row"><span class="detail-label">Penanggung Jawab</span><span class="detail-value">${item.pj}</span></div>
    <div class="detail-row"><span class="detail-label">Jumlah Peserta</span><span class="detail-value">${item.peserta}</span></div>
    <div class="detail-row"><span class="detail-label">Berulang</span><span class="detail-value">${item.repeat || 'Tidak Berulang'}</span></div>
    <div class="detail-row"><span class="detail-label">Pengingat</span><span class="detail-value">${item.reminder || '30 Menit Sebelum'}</span></div>
    <div class="detail-row"><span class="detail-label">Status</span><span class="status-badge ${statusClassMap[item.status]}">${item.status}</span></div>
    <div class="detail-row"><span class="detail-label">Catatan</span><span class="detail-value">${item.deskripsi || '-'}</span></div>
  `

  document.getElementById('editAgendaBtn').style.display = item.isRecurring ? 'none' : 'inline-flex'
  document.getElementById('deleteAgendaBtn').style.display = item.isRecurring ? 'none' : 'inline-flex'

  document.getElementById('detailModal').classList.add('show')
}

document.getElementById('closeDetailModal').addEventListener('click', () => {
  document.getElementById('detailModal').classList.remove('show')
})

document.getElementById('editAgendaBtn').addEventListener('click', () => {
  openFormModal(currentDetailItem.tanggal, currentDetailItem)
})

document.getElementById('deleteAgendaBtn').addEventListener('click', () => {
  if (currentDetailItem.isRecurring) return

  const item = currentDetailItem
  openConfirmDelete({
    title: 'Hapus Agenda?',
    message: `Yakin ingin menghapus agenda "${item.nama}"? Tindakan ini tidak bisa dibatalkan.`,
    onConfirm: async () => {
      const csrf = getCsrf()
      try {
        const res = await apiRequest(`/kegiatan/agenda/${item.id}`, {
          method: 'DELETE',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        })
        if (!res.ok) throw new Error('gagal hapus')

        agendaData = agendaData.filter((i) => i.id !== item.id)
        document.getElementById('detailModal').classList.remove('show')
        renderCalendar()
        renderJadwal()
        showToast(`Agenda "${item.nama}" berhasil dihapus.`, 'fa-solid fa-trash')
      } catch (err) {
        showToast('Gagal menghapus agenda.', 'fa-solid fa-triangle-exclamation')
        throw err
      }
    },
  })
})

// Klik overlay (di luar modal-box) buat nutup modal
document.querySelectorAll('.modal-overlay').forEach((overlay) => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) overlay.classList.remove('show')
  })
})

// ============ INIT ============
document.getElementById('searchInput').addEventListener('input', () => {
  renderCalendar()
  renderJadwal()
})
document.getElementById('kategoriFilter').addEventListener('change', () => {
  renderCalendar()
  renderJadwal()
})
document.getElementById('statusFilter').addEventListener('change', () => {
  renderCalendar()
  renderJadwal()
})

// 24-jam (WIB) time picker: isi dropdown Jam/Menit dan jaga sync ke hidden input
function populateTimePicker(prefix) {
  const hourSel = document.getElementById(prefix + 'Hour')
  const minSel = document.getElementById(prefix + 'Min')
  if (!hourSel || !minSel) return

  hourSel.add(new Option('Jam', ''))
  minSel.add(new Option('Menit', ''))
  for (let h = 0; h < 24; h++) {
    const v = pad(h)
    hourSel.add(new Option(v, v))
  }
  for (let m = 0; m < 60; m++) {
    const v = pad(m)
    minSel.add(new Option(v, v))
  }
  hourSel.addEventListener('change', syncTimeInputs)
  minSel.addEventListener('change', syncTimeInputs)
}

function setTimeControls(prefix, value) {
  const hourSel = document.getElementById(prefix + 'Hour')
  const minSel = document.getElementById(prefix + 'Min')
  if (!hourSel || !minSel) return
  hourSel.value = ''
  minSel.value = ''
  if (!value || !value.includes(':')) return
  const [h, m] = value.split(':')
  hourSel.value = pad(parseInt(h, 10))
  minSel.value = pad(parseInt(m, 10))
}

function timeFromControls(prefix) {
  const h = document.getElementById(prefix + 'Hour').value
  const m = document.getElementById(prefix + 'Min').value
  return h && m ? `${h}:${m}` : ''
}

function syncTimeInputs() {
  document.getElementById('formJamMulai').value = timeFromControls('formJamMulai')
  document.getElementById('formJamSelesai').value = timeFromControls('formJamSelesai')
}

populateTimePicker('formJamMulai')
populateTimePicker('formJamSelesai')
populateMonthYearSelect()
renderCalendar()
renderJadwal()
