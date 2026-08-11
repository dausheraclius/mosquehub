let suratData = [
  {
    id: 1,
    nomor: '911230100001',
    subjek: 'Undangan Maulid Nabi',
    jenis: 'Surat Undangan',
    tanggal: '2024-06-15',
    status: 'Terkirim',
    isi: 'Sehubungan dengan akan diselenggarakannya peringatan Maulid Nabi Muhammad SAW, kami mengundang Bapak/Ibu untuk hadir pada acara tersebut.',
    kepada: 'Bpk. Hendra',
  },
  {
    id: 2,
    nomor: '911230100002',
    subjek: 'Sertifikat YMBPK',
    jenis: 'Sertifikat',
    tanggal: '2024-06-16',
    status: 'Draft',
    isi: 'Diberikan kepada pengurus YMBPK atas dedikasi dan partisipasi aktif dalam kegiatan kepengurusan masjid.',
    kepada: 'M. Reza',
  },
  {
    id: 3,
    nomor: '911230100003',
    subjek: 'Undangan Maulid',
    jenis: 'Surat Undangan',
    tanggal: '2024-06-15',
    status: 'Terkirim',
    isi: 'Sehubungan dengan akan diselenggarakannya peringatan Maulid Nabi Muhammad SAW, kami mengundang Bapak/Ibu untuk hadir pada acara tersebut.',
    kepada: 'Ibu Sri',
  },
  {
    id: 4,
    nomor: '911230100004',
    subjek: 'Keterangan Aktif Jamaah',
    jenis: 'Surat Keterangan',
    tanggal: '2024-06-20',
    status: 'Terkirim',
    isi: 'Surat ini menerangkan bahwa yang bersangkutan benar merupakan jamaah aktif YMBPK Baiturrahim.',
    kepada: 'S. Abdullah',
  },
]

const statusClassMap = { Terkirim: 'status-berlangsung', Draft: 'status-empty' }

function formatTanggal(iso) {
  const d = new Date(iso)
  const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`
}

function getFilteredSurat() {
  const keyword = document.getElementById('searchInput').value.toLowerCase()
  const jenis = document.getElementById('jenisFilter').value
  const status = document.getElementById('statusFilter').value
  const tanggal = document.getElementById('dateFilter').value

  return suratData.filter((s) => {
    const matchSearch = s.subjek.toLowerCase().includes(keyword) || s.nomor.includes(keyword)
    const matchJenis = jenis === '' || s.jenis === jenis
    const matchStatus = status === '' || s.status === status
    const matchTanggal = tanggal === '' || s.tanggal === tanggal
    return matchSearch && matchJenis && matchStatus && matchTanggal
  })
}

function renderTable() {
  const tbody = document.getElementById('suratTableBody')
  const filtered = getFilteredSurat()
  tbody.innerHTML = ''

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:24px; color:var(--text-muted);">Belum ada surat.</td></tr>`
    return
  }

  filtered.forEach((s) => {
    tbody.insertAdjacentHTML(
      'beforeend',
      `
      <tr>
        <td>${s.nomor}</td>
        <td>${s.subjek}</td>
        <td>${s.jenis}</td>
        <td>${formatTanggal(s.tanggal)}</td>
        <td><span class="status-badge ${statusClassMap[s.status]}">${s.status}</span></td>
        <td>
          <button class="btn-sm btn-detail" data-id="${s.id}">Detail</button>
          <button class="btn-sm btn-hapus" data-id="${s.id}">Hapus</button>
        </td>
      </tr>
    `,
    )
  })

  tbody.querySelectorAll('.btn-detail').forEach((btn) => {
    btn.addEventListener('click', () => openDetailModal(parseInt(btn.dataset.id)))
  })

  tbody.querySelectorAll('.btn-hapus').forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.dataset.id)
      const item = suratData.find((s) => s.id === id)
      if (!item) return
      openConfirmDelete({
        title: 'Hapus Surat?',
        message: `Yakin ingin menghapus surat "${item.subjek}"? Tindakan ini tidak bisa dibatalkan.`,
        onConfirm: async () => {
          suratData = suratData.filter((s) => s.id !== id)
          renderTable()
          showToast('Surat berhasil dihapus.', 'fa-solid fa-trash')
        },
      })
    })
  })
}

function filePreviewHtml(s) {
  if (!s.fileData) return ''

  const ext = s.fileName ? s.fileName.split('.').pop().toLowerCase() : ''

  if (ext === 'pdf') {
    return `
      <div style="margin-top: 16px;">
        <strong style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 6px;">File Surat</strong>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
          <i class="fa-solid fa-file-pdf" style="color: #ef4444; font-size: 18px;"></i>
          <span style="font-size: 13px;">${s.fileName}</span>
          <a href="${s.fileData}" download="${s.fileName}" class="btn btn-outline" style="margin-left: auto; padding: 4px 12px; font-size: 11px; text-decoration: none;"><i class="fa-solid fa-download"></i> Unduh</a>
        </div>
        <embed src="${s.fileData}" type="application/pdf" style="width: 100%; height: 400px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);" />
      </div>
    `
  }

  if (ext === 'jpg' || ext === 'jpeg' || ext === 'png') {
    return `
      <div style="margin-top: 16px;">
        <strong style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 6px;">File Surat</strong>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
          <i class="fa-solid fa-file-image" style="color: #3b82f6; font-size: 18px;"></i>
          <span style="font-size: 13px;">${s.fileName}</span>
          <a href="${s.fileData}" download="${s.fileName}" class="btn btn-outline" style="margin-left: auto; padding: 4px 12px; font-size: 11px; text-decoration: none;"><i class="fa-solid fa-download"></i> Unduh</a>
        </div>
        <img src="${s.fileData}" alt="${s.fileName}" style="width: 100%; max-height: 400px; object-fit: contain; border: 1px solid var(--border-color); border-radius: var(--radius-sm);" />
      </div>
    `
  }

  return `
    <div style="margin-top: 16px;">
      <strong style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 6px;">File Surat</strong>
      <div style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
        <i class="fa-solid fa-file" style="color: var(--text-muted); font-size: 18px;"></i>
        <span style="font-size: 13px;">${s.fileName}</span>
        <a href="${s.fileData}" download="${s.fileName}" class="btn btn-primary" style="margin-left: auto; padding: 4px 12px; font-size: 11px; text-decoration: none;"><i class="fa-solid fa-download"></i> Unduh</a>
      </div>
    </div>
  `
}

function openDetailModal(id) {
  const s = suratData.find((item) => item.id === id)
  document.getElementById('detailModalTitle').textContent = s.subjek
  document.getElementById('detailModalBody').innerHTML = `
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
      <div><strong style="font-size: 11px; color: var(--text-muted);">Nomor Surat</strong><br><span style="font-size: 13px;">${s.nomor}</span></div>
      <div><strong style="font-size: 11px; color: var(--text-muted);">Tanggal</strong><br><span style="font-size: 13px;">${formatTanggal(s.tanggal)}</span></div>
      <div><strong style="font-size: 11px; color: var(--text-muted);">Jenis</strong><br><span style="font-size: 13px;">${s.jenis}</span></div>
      <div><strong style="font-size: 11px; color: var(--text-muted);">Status</strong><br><span class="status-badge ${statusClassMap[s.status]}">${s.status}</span></div>
    </div>
    <div style="margin-bottom: 12px;">
      <strong style="font-size: 11px; color: var(--text-muted);">Kepada</strong><br>
      <span style="font-size: 13px;">${s.kepada}</span>
    </div>
    <div>
      <strong style="font-size: 11px; color: var(--text-muted);">Isi Surat</strong><br>
      <p style="font-size: 13px; line-height: 1.6; margin-top: 4px;">${s.isi}</p>
    </div>
    ${filePreviewHtml(s)}
  `
  document.getElementById('detailModal').classList.add('active')
}

function closeDetailModal() {
  document.getElementById('detailModal').classList.remove('active')
}

function resetTambahForm() {
  document.getElementById('inputNomor').value = ''
  document.getElementById('inputSubjek').value = ''
  document.getElementById('inputJenis').value = 'Surat Undangan'
  document.getElementById('inputTanggal').value = new Date().toISOString().split('T')[0]
  document.getElementById('inputStatus').value = 'Draft'
  document.getElementById('inputKepada').value = ''
  document.getElementById('inputIsi').value = ''
  document.getElementById('inputFile').value = ''
  document.getElementById('fileInfo').style.display = 'none'
}

document.getElementById('inputFile').addEventListener('change', (e) => {
  const file = e.target.files[0]
  const info = document.getElementById('fileInfo')
  if (file) {
    info.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`
    info.style.display = 'block'
  } else {
    info.style.display = 'none'
  }
})

document.getElementById('tambahSuratBtn').addEventListener('click', () => {
  resetTambahForm()
  document.getElementById('tambahModal').classList.add('active')
})

document.getElementById('closeTambahModal').addEventListener('click', () => {
  document.getElementById('tambahModal').classList.remove('active')
})
document.getElementById('batalTambahBtn').addEventListener('click', () => {
  document.getElementById('tambahModal').classList.remove('active')
})

document.getElementById('simpanTambahBtn').addEventListener('click', () => {
  const nomor = document.getElementById('inputNomor').value.trim()
  const subjek = document.getElementById('inputSubjek').value.trim()
  if (!nomor || !subjek) return

  const fileInput = document.getElementById('inputFile')
  const file = fileInput.files[0]

  const simpan = (fileData) => {
    const suratBaru = {
      id: Date.now(),
      nomor,
      subjek,
      jenis: document.getElementById('inputJenis').value,
      tanggal: document.getElementById('inputTanggal').value,
      status: document.getElementById('inputStatus').value,
      kepada: document.getElementById('inputKepada').value.trim() || '-',
      isi: document.getElementById('inputIsi').value.trim() || '-',
      fileName: fileData ? file.name : null,
      fileType: fileData ? file.type : null,
      fileData: fileData || null,
    }

    suratData.unshift(suratBaru)
    document.getElementById('tambahModal').classList.remove('active')
    renderTable()
  }

  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => simpan(e.target.result)
    reader.readAsDataURL(file)
  } else {
    simpan(null)
  }
})

document.getElementById('closeDetailModal').addEventListener('click', closeDetailModal)
document.getElementById('closeDetailBtn').addEventListener('click', closeDetailModal)

document.querySelectorAll('.modal-overlay').forEach((overlay) => {
  overlay.addEventListener('click', (e) => {
    const pickerOpen = document.querySelector('.datepicker-popup.show')
    if (e.target === overlay && !pickerOpen) overlay.classList.remove('active')
  })
})

document.getElementById('searchInput').addEventListener('input', renderTable)
document.getElementById('jenisFilter').addEventListener('change', renderTable)
document.getElementById('statusFilter').addEventListener('change', renderTable)
document.getElementById('dateFilter').addEventListener('change', renderTable)

// ============ DATE PICKER (custom, gaya kalender Agenda) ============
const datepickerMonthNames = [
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

function buildDatePicker(inputEl, onSelect) {
  const wrapper = document.createElement('div')
  wrapper.className = 'datepicker'
  inputEl.parentNode.insertBefore(wrapper, inputEl)
  wrapper.appendChild(inputEl)

  const icon = document.createElement('i')
  icon.className = 'fa-solid fa-calendar-days datepicker-icon'
  wrapper.appendChild(icon)

  const popup = document.createElement('div')
  popup.className = 'datepicker-popup'
  wrapper.appendChild(popup)

  let viewDate = new Date()
  viewDate.setDate(1)

  const pad = (n) => n.toString().padStart(2, '0')
  const toDateKey = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`

  function render() {
    const year = viewDate.getFullYear()
    const month = viewDate.getMonth()
    const todayKey = toDateKey(new Date())
    const selectedKey = inputEl.value

    popup.innerHTML = `
      <div class="datepicker-header">
        <button type="button" class="datepicker-nav" data-nav="-1"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="datepicker-title">${datepickerMonthNames[month]} ${year}</div>
        <button type="button" class="datepicker-nav" data-nav="1"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
      <div class="datepicker-days-header">
        <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
      </div>
      <div class="datepicker-days"></div>
      <div class="datepicker-footer">
        <button type="button" class="datepicker-clear">Kosongkan</button>
        <button type="button" class="datepicker-today">Hari Ini</button>
      </div>
    `

    const firstDay = new Date(year, month, 1).getDay()
    const totalDays = new Date(year, month + 1, 0).getDate()
    const prevMonthDays = new Date(year, month, 0).getDate()
    const daysGrid = popup.querySelector('.datepicker-days')

    for (let i = firstDay - 1; i >= 0; i--) {
      daysGrid.insertAdjacentHTML('beforeend', `<div class="datepicker-day other-month">${prevMonthDays - i}</div>`)
    }
    for (let d = 1; d <= totalDays; d++) {
      const key = `${year}-${pad(month + 1)}-${pad(d)}`
      const cls = key === todayKey ? 'today' : ''
      daysGrid.insertAdjacentHTML(
        'beforeend',
        `<div class="datepicker-day ${cls} ${key === selectedKey ? 'selected' : ''}" data-date="${key}">${d}</div>`,
      )
    }

    popup.querySelector('[data-nav="-1"]').addEventListener('click', () => {
      viewDate.setMonth(viewDate.getMonth() - 1)
      render()
    })
    popup.querySelector('[data-nav="1"]').addEventListener('click', () => {
      viewDate.setMonth(viewDate.getMonth() + 1)
      render()
    })
    popup.querySelector('.datepicker-today').addEventListener('click', () => {
      const today = toDateKey(new Date())
      inputEl.value = today
      onSelect && onSelect(today)
      close()
    })
    popup.querySelector('.datepicker-clear').addEventListener('click', () => {
      inputEl.value = ''
      onSelect && onSelect('')
      close()
    })
    daysGrid.querySelectorAll('.datepicker-day[data-date]').forEach((el) => {
      el.addEventListener('click', () => {
        const val = el.dataset.date
        inputEl.value = val
        onSelect && onSelect(val)
        close()
      })
    })
  }

  function open() {
    if (inputEl.value) {
      const parts = inputEl.value.split('-').map(Number)
      if (parts.length === 3 && !parts.some(isNaN)) {
        viewDate = new Date(parts[0], parts[1] - 1, 1)
      }
    }
    render()
    popup.classList.add('show')
  }

  function close() {
    popup.classList.remove('show')
  }

  inputEl.addEventListener('click', (e) => {
    e.preventDefault()
    if (popup.classList.contains('show')) close()
    else open()
  })

  document.addEventListener('click', (e) => {
    if (!wrapper.contains(e.target)) close()
  })
}

buildDatePicker(document.getElementById('dateFilter'), () => renderTable())
buildDatePicker(document.getElementById('inputTanggal'))

renderTable()
