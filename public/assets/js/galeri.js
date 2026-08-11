let dataAlbum = window.__ALBUM_DATA__ || []

let activeAlbumId = null
let activePhotoIndex = 0
let editingAlbumId = null // null = lagi bikin album baru

function formatTanggal(iso) {
  const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
  const [y, m, d] = iso.split('-').map(Number)
  return `${d} ${bulan[m - 1]} ${y}`
}

// ============================================================
// 1. STAT CARDS
// ============================================================
function renderStatCards() {
  const totalAlbum = dataAlbum.length
  const totalFoto = dataAlbum.reduce((s, a) => s + a.photos.length, 0)
  const published = dataAlbum.filter((a) => a.status === 'published').length
  const draft = totalAlbum - published

  document.getElementById('galeriStatCards').innerHTML = `
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Total Album</span></div>
      <span class="stat-value">${totalAlbum}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Total Foto</span></div>
      <span class="stat-value">${totalFoto}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Dipublikasikan</span></div>
      <span class="stat-value" style="color:var(--color-green)">${published}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Draft</span></div>
      <span class="stat-value" style="color:var(--text-muted)">${draft}</span>
    </div>
  `
}

// ============================================================
// 2. GRID ALBUM
// ============================================================
function renderAlbumGrid() {
  const grid = document.getElementById('albumGrid')
  grid.innerHTML = ''

  dataAlbum.forEach((album) => {
    const cover = album.photos.find((p) => p.isCover) || album.photos[0]
    const badgeClass = album.status === 'published' ? 'published' : 'draft'
    const badgeLabel = album.status === 'published' ? 'Tayang di Web' : 'Draft'

    grid.insertAdjacentHTML(
      'beforeend',
      `
      <div class="album-card" data-album-id="${album.id}">
        <span class="album-card-badge ${badgeClass}">${badgeLabel}</span>
        ${
          cover && cover.url
            ? `<img class="album-cover-img" src="${cover.url}" alt="${album.nama}">`
            : `<div class="album-cover-placeholder"><i class="fa-solid fa-images"></i></div>`
        }
        <div class="album-card-overlay">
          <div class="album-card-title">${album.nama}</div>
          <div class="album-card-meta">
            <span><i class="fa-regular fa-calendar"></i> ${formatTanggal(album.tanggal)}</span>
            <span><i class="fa-regular fa-image"></i> ${album.photos.length} foto</span>
          </div>
        </div>
      </div>
    `,
    )
  })

  grid.querySelectorAll('.album-card').forEach((card) => {
    card.addEventListener('click', () => openAlbumDetail(Number(card.dataset.albumId)))
  })
}

// ============================================================
// 3. VIEW: DETAIL ALBUM
// ============================================================
function openAlbumDetail(albumId) {
  activeAlbumId = albumId
  const album = dataAlbum.find((a) => a.id === albumId)
  if (!album) return

  document.getElementById('albumListView').hidden = true
  document.getElementById('albumDetailView').hidden = false

  document.getElementById('albumDetailTitle').textContent = album.nama
  document.getElementById('albumDetailMeta').textContent =
    `${formatTanggal(album.tanggal)} · ${album.photos.length} foto · ${album.deskripsi || 'Belum ada deskripsi.'}`
  document.getElementById('albumPublishToggle').checked = album.status === 'published'

  renderPhotoGrid()
}

function closeAlbumDetail() {
  document.getElementById('albumListView').hidden = false
  document.getElementById('albumDetailView').hidden = true
  activeAlbumId = null
  renderStatCards()
  renderAlbumGrid()
}

function getActiveAlbum() {
  return dataAlbum.find((a) => a.id === activeAlbumId)
}

// ============================================================
// 4. GRID FOTO DI DALAM ALBUM
// ============================================================
function renderPhotoGrid() {
  const album = getActiveAlbum()
  if (!album) return
  const grid = document.getElementById('photoGrid')
  grid.innerHTML = ''

  if (album.photos.length === 0) {
    grid.innerHTML = `<p style="color:var(--text-muted); font-size:13px;">Belum ada foto di album ini. Upload foto pertama di atas.</p>`
    return
  }

  album.photos.forEach((photo, index) => {
    grid.insertAdjacentHTML(
      'beforeend',
      `
      <div class="photo-tile" data-photo-index="${index}">
        ${photo.isCover ? `<span class="photo-tile-cover-badge"><i class="fa-solid fa-star"></i> Cover</span>` : ''}
        ${
          photo.url
            ? `<img src="${photo.url}" alt="Foto kegiatan">`
            : `<div class="photo-tile-placeholder"><i class="fa-regular fa-image"></i></div>`
        }
        <div class="photo-tile-overlay">
          <button type="button" class="photo-tile-action" data-action="view" title="Lihat"><i class="fa-solid fa-eye"></i></button>
          <button type="button" class="photo-tile-action" data-action="cover" title="Jadikan cover"><i class="fa-solid fa-star"></i></button>
          <button type="button" class="photo-tile-action danger" data-action="delete" title="Hapus"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
    `,
    )
  })

  grid.querySelectorAll('.photo-tile').forEach((tile) => {
    const index = Number(tile.dataset.photoIndex)
    tile.querySelector('[data-action="view"]').addEventListener('click', () => openLightbox(index))
    tile.querySelector('[data-action="cover"]').addEventListener('click', (e) => {
      e.stopPropagation()
      setCover(index)
    })
    tile.querySelector('[data-action="delete"]').addEventListener('click', (e) => {
      e.stopPropagation()
      deletePhoto(index)
    })
    tile.addEventListener('click', () => openLightbox(index))
  })
}

async function setCover(index) {
  const album = getActiveAlbum()
  const photo = album.photos[index]
  const csrf = document.querySelector('meta[name="csrf-token"]').content

  try {
    await fetch(`/kegiatan/galeri/${album.id}/photos/${photo.id}/cover`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
    })
    album.photos.forEach((p, i) => (p.isCover = i === index))
    renderPhotoGrid()
    showToast('Foto dijadikan cover album.')
  } catch (err) {
    showToast('Gagal set cover, coba lagi.')
  }
}

async function deletePhoto(index) {
  const album = getActiveAlbum()
  const photo = album.photos[index]

  openConfirmDelete({
    title: 'Hapus Foto?',
    message: 'Yakin ingin menghapus foto ini dari album?',
    onConfirm: async () => {
      const csrf = document.querySelector('meta[name="csrf-token"]').content

      try {
        const res = await fetch(`/kegiatan/galeri/${album.id}/photos/${photo.id}`, {
          method: 'DELETE',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        })
        const result = await res.json()

        album.photos.splice(index, 1)
        if (result.newCoverId) {
          album.photos.forEach((p) => (p.isCover = p.id === result.newCoverId))
        }
        renderPhotoGrid()
        document.getElementById('albumDetailMeta').textContent =
          `${formatTanggal(album.tanggal)} · ${album.photos.length} foto · ${album.deskripsi || 'Belum ada deskripsi.'}`
        showToast('Foto dihapus.', 'fa-solid fa-trash')
      } catch (err) {
        showToast('Gagal menghapus foto.')
        throw err
      }
    },
  })
}

// 5. UPLOAD FOTO (drag & drop + klik pilih file)

async function handleFiles(fileList) {
  const album = getActiveAlbum()
  if (!album) return
  const files = Array.from(fileList).filter((f) => f.type.startsWith('image/'))
  if (files.length === 0) return

  const formData = new FormData()
  files.forEach((file) => formData.append('photos[]', file))
  const csrf = document.querySelector('meta[name="csrf-token"]').content

  showToast('Sedang mengupload foto...')

  try {
    const res = await fetch(`/kegiatan/galeri/${album.id}/photos`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
      body: formData,
    })
    if (!res.ok) throw new Error('gagal upload')

    const newPhotos = await res.json()
    album.photos.push(...newPhotos)
    renderPhotoGrid()
    document.getElementById('albumDetailMeta').textContent =
      `${formatTanggal(album.tanggal)} · ${album.photos.length} foto · ${album.deskripsi || 'Belum ada deskripsi.'}`
    showToast(`${files.length} foto berhasil diupload.`)
  } catch (err) {
    showToast('Gagal upload foto, coba lagi.')
  }
}

function initDropzone() {
  const zone = document.getElementById('uploadDropzone')
  const input = document.getElementById('uploadFileInput')

  zone.addEventListener('click', () => input.click())
  input.addEventListener('change', () => handleFiles(input.files))

  ;['dragenter', 'dragover'].forEach((evt) =>
    zone.addEventListener(evt, (e) => {
      e.preventDefault()
      zone.classList.add('drag-over')
    }),
  )
  ;['dragleave', 'drop'].forEach((evt) =>
    zone.addEventListener(evt, (e) => {
      e.preventDefault()
      zone.classList.remove('drag-over')
    }),
  )
  zone.addEventListener('drop', (e) => {
    if (e.dataTransfer.files.length) handleFiles(e.dataTransfer.files)
  })
}

// ============================================================
// 6. LIGHTBOX
// ============================================================
function openLightbox(index) {
  activePhotoIndex = index
  document.getElementById('lightboxOverlay').classList.add('active')
  renderLightbox()
}

function closeLightbox() {
  document.getElementById('lightboxOverlay').classList.remove('active')
}

function renderLightbox() {
  const album = getActiveAlbum()
  const photo = album.photos[activePhotoIndex]
  const img = document.getElementById('lightboxImg')
  img.src = photo.url || ''
  img.style.background = photo.url ? 'transparent' : 'var(--bg-gray, #f2f4f3)'
}

function lightboxNav(delta) {
  const album = getActiveAlbum()
  activePhotoIndex = (activePhotoIndex + delta + album.photos.length) % album.photos.length
  renderLightbox()
}

// ============================================================
// 7. MODAL BUAT / EDIT ALBUM
// ============================================================
function openAlbumModal(albumId = null) {
  editingAlbumId = albumId
  const overlay = document.getElementById('albumModalOverlay')
  const title = document.getElementById('albumModalTitle')

  if (albumId) {
    const album = dataAlbum.find((a) => a.id === albumId)
    title.textContent = 'Edit Album'
    document.getElementById('inputAlbumNama').value = album.nama
    document.getElementById('inputAlbumTanggal').value = album.tanggal
    document.getElementById('inputAlbumStatus').value = album.status
    document.getElementById('inputAlbumDeskripsi').value = album.deskripsi
  } else {
    title.textContent = 'Buat Album Baru'
    document.getElementById('inputAlbumNama').value = ''
    document.getElementById('inputAlbumTanggal').value = ''
    document.getElementById('inputAlbumStatus').value = 'draft'
    document.getElementById('inputAlbumDeskripsi').value = ''
  }

  overlay.classList.add('active')
}

function closeAlbumModal() {
  document.getElementById('albumModalOverlay').classList.remove('active')
}

async function saveAlbumModal() {
  const nama = document.getElementById('inputAlbumNama').value.trim()
  if (!nama) {
    showToast('Isi nama kegiatan/album dulu ya.')
    return
  }
  const payload = {
    nama,
    tanggal: document.getElementById('inputAlbumTanggal').value || new Date().toISOString().slice(0, 10),
    status: document.getElementById('inputAlbumStatus').value,
    deskripsi: document.getElementById('inputAlbumDeskripsi').value.trim(),
  }
  const csrf = document.querySelector('meta[name="csrf-token"]').content

  const isEdit = !!editingAlbumId
  const url = isEdit ? `/kegiatan/galeri/${editingAlbumId}` : '/kegiatan/galeri'
  const method = isEdit ? 'PUT' : 'POST'

  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(payload),
    })
    if (!res.ok) {
      const errData = await res.json()
      const firstError = Object.values(errData.errors || {})[0]?.[0] || 'Gagal menyimpan album.'
      showToast(firstError)
      return
    }
    const saved = await res.json()

    if (isEdit) {
      const album = dataAlbum.find((a) => a.id === editingAlbumId)
      Object.assign(album, { nama: saved.nama, tanggal: saved.tanggal, status: saved.status, deskripsi: saved.deskripsi })
      showToast('Album berhasil diperbarui.')
      if (activeAlbumId === editingAlbumId) openAlbumDetail(editingAlbumId)
    } else {
      dataAlbum.unshift(saved)
      showToast('Album baru berhasil dibuat.')
    }

    closeAlbumModal()
    renderStatCards()
    renderAlbumGrid()
  } catch (err) {
    showToast('Terjadi kesalahan saat menyimpan album.')
  }
}

// ============================================================
// 8. INISIALISASI EVENT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
  renderStatCards()
  renderAlbumGrid()
  initDropzone()

  document.getElementById('btnBuatAlbum').addEventListener('click', () => openAlbumModal())
  document.getElementById('btnEditAlbum').addEventListener('click', () => openAlbumModal(activeAlbumId))
  document.getElementById('btnBackToAlbums').addEventListener('click', closeAlbumDetail)

  document.getElementById('btnHapusAlbum').addEventListener('click', () => {
    openConfirmDelete({
      title: 'Hapus Album?',
      message: 'Yakin ingin menghapus album ini beserta semua fotonya? Tindakan ini tidak bisa dibatalkan.',
      onConfirm: async () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').content

        try {
          await fetch(`/kegiatan/galeri/${activeAlbumId}`, {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
          })
          const idx = dataAlbum.findIndex((a) => a.id === activeAlbumId)
          if (idx > -1) dataAlbum.splice(idx, 1)
          showToast('Album dihapus.', 'fa-solid fa-trash')
          closeAlbumDetail()
        } catch (err) {
          showToast('Gagal menghapus album.', 'fa-solid fa-triangle-exclamation')
          throw err
        }
      },
    })
  })

  document.getElementById('albumPublishToggle').addEventListener('change', async (e) => {
    const album = getActiveAlbum()
    const csrf = document.querySelector('meta[name="csrf-token"]').content

    try {
      await fetch(`/kegiatan/galeri/${album.id}/publish`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ published: e.target.checked }),
      })
      album.status = e.target.checked ? 'published' : 'draft'
      showToast(e.target.checked ? 'Album ditayangkan ke website.' : 'Album disembunyikan dari website.')
    } catch (err) {
      e.target.checked = !e.target.checked
      showToast('Gagal ubah status publish.')
    }
  })

  // Modal album
  document.getElementById('albumModalCloseBtn').addEventListener('click', closeAlbumModal)
  document.getElementById('albumModalCancelBtn').addEventListener('click', closeAlbumModal)
  document.getElementById('albumModalSaveBtn').addEventListener('click', saveAlbumModal)
  document.getElementById('albumModalOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'albumModalOverlay') closeAlbumModal()
  })

  // Lightbox
  document.getElementById('lightboxCloseBtn').addEventListener('click', closeLightbox)
  document.getElementById('lightboxPrevBtn').addEventListener('click', () => lightboxNav(-1))
  document.getElementById('lightboxNextBtn').addEventListener('click', () => lightboxNav(1))
  document.getElementById('lightboxOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'lightboxOverlay') closeLightbox()
  })
  document.getElementById('lightboxSetCoverBtn').addEventListener('click', () => {
    setCover(activePhotoIndex)
    renderLightbox()
  })
  document.getElementById('lightboxDeleteBtn').addEventListener('click', () => {
    deletePhoto(activePhotoIndex)
    closeLightbox()
  })

  document.addEventListener('keydown', (e) => {
    if (document.getElementById('lightboxOverlay').classList.contains('active')) {
      if (e.key === 'Escape') closeLightbox()
      if (e.key === 'ArrowLeft') lightboxNav(-1)
      if (e.key === 'ArrowRight') lightboxNav(1)
    } else if (document.getElementById('albumModalOverlay').classList.contains('active')) {
      if (e.key === 'Escape') closeAlbumModal()
    }
  })
})
