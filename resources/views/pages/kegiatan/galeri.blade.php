@extends('layouts.app')

@section('title', 'MosqueHub - Galeri')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/galeri.css')
@endpush

@section('content')

  <!-- ============ VIEW: DAFTAR ALBUM ============ -->
  <div id="albumListView">
    <x-page-header crumb="Kegiatan" active="Galeri" title="Galeri Kegiatan" subtitle="Kelola dokumentasi kegiatan masjid yang ditampilin ke website jemaah.">
      <button class="btn btn-primary" id="btnBuatAlbum"><i class="fa-solid fa-plus"></i> Buat Album Baru</button>
    </x-page-header>

    <div class="stat-cards" id="galeriStatCards"></div>

    <div class="filter-bar" style="margin-bottom: 16px">
      <div class="filter-group filter-search-group">
        <span class="filter-label">Cari</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="albumSearch" placeholder="Cari album..." />
        </div>
      </div>
    </div>

    <div class="album-grid" id="albumGrid"></div>
  </div>

  <!-- ============ VIEW: DETAIL ALBUM ============ -->
  <div id="albumDetailView" hidden>
    <button class="album-back-btn" id="btnBackToAlbums">
      <i class="fa-solid fa-arrow-left"></i> Kembali ke Semua Album
    </button>

    <div class="album-detail-header">
      <div>
        <h1 class="page-title" id="albumDetailTitle">-</h1>
        <p class="page-subtitle" id="albumDetailMeta">-</p>
      </div>
      <div class="album-detail-actions">
        <div class="publish-toggle-wrap">
          <label class="switch">
            <input type="checkbox" id="albumPublishToggle" />
            <span class="switch-track"></span>
          </label>
          <span>Tampilkan di Website</span>
        </div>
        <button class="btn-sm btn-edit" id="btnEditAlbum"><i class="fa-solid fa-pen"></i> Edit Album</button>
        <button class="btn-sm btn-hapus" id="btnHapusAlbum">
          <i class="fa-solid fa-trash"></i> Hapus Album
        </button>
      </div>
    </div>

    <!-- DROPZONE UPLOAD -->
    <div class="upload-dropzone" id="uploadDropzone">
      <input type="file" id="uploadFileInput" accept="image/*" multiple hidden />
      <div class="upload-dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
      <div class="upload-dropzone-text">
        <strong>Tarik & taruh foto di sini</strong>
        <span>atau klik untuk pilih file dari komputer (JPG, PNG, maks 5MB per foto)</span>
      </div>
    </div>

    <!-- GRID FOTO -->
    <div class="photo-grid" id="photoGrid"></div>
  </div>

@endsection

@section('modals')
  <!-- MODAL: BUAT / EDIT ALBUM -->
  <div class="modal-overlay" id="albumModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="albumModalTitle">Buat Album Baru</h2>
        <button class="modal-close" id="albumModalCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="inputAlbumNama">Nama Kegiatan / Album</label>
          <input class="form-input" type="text" id="inputAlbumNama" placeholder="cth: Kajian Subuh Rutin" />
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="inputAlbumTanggal">Tanggal Kegiatan</label>
            <input class="form-input" type="date" id="inputAlbumTanggal" />
          </div>
          <div class="form-group">
            <label class="form-label" for="inputAlbumStatus">Status</label>
            <select class="form-select" id="inputAlbumStatus">
              <option value="draft">Draf (belum tayang)</option>
              <option value="published">Terbitkan ke Website</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="inputAlbumDeskripsi">Deskripsi Singkat</label>
          <textarea
            class="form-textarea"
            id="inputAlbumDeskripsi"
            rows="3"
            placeholder="Ceritain dikit soal kegiatannya, buat caption di website"
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="albumModalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="albumModalSaveBtn"><i class="fa-solid fa-check"></i> Simpan Album</button>
      </div>
    </div>
  </div>

  <!-- LIGHTBOX FOTO -->
  <div class="lightbox-overlay" id="lightboxOverlay">
    <button class="lightbox-close" id="lightboxCloseBtn"><i class="fa-solid fa-xmark"></i></button>
    <button class="lightbox-nav lightbox-prev" id="lightboxPrevBtn"><i class="fa-solid fa-chevron-left"></i></button>
    <img src="" alt="Preview foto" class="lightbox-img" id="lightboxImg" />
    <button class="lightbox-nav lightbox-next" id="lightboxNextBtn"><i class="fa-solid fa-chevron-right"></i></button>
    <div class="lightbox-toolbar">
      <button class="lightbox-toolbar-btn" id="lightboxSetCoverBtn">
        <i class="fa-solid fa-star"></i> Jadikan Cover
      </button>
      <button class="lightbox-toolbar-btn lightbox-toolbar-danger" id="lightboxDeleteBtn">
        <i class="fa-solid fa-trash"></i> Hapus Foto
      </button>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.__ALBUM_DATA__ = @json($albumList);
  </script>
  <script src="{{ asset('assets/js/galeri.js') }}"></script>
@endpush