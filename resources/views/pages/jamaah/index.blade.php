@extends('layouts.app')

@section('title', 'MosqueHub - Data Jemaah')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/data-jamaah.css')
@endpush

@section('content')

  <x-page-header active="Data Jemaah" title="Data Jemaah" subtitle="Kelola seluruh data jemaah yang terdaftar di masjid.">
    <button class="btn btn-primary" id="openAddJamaahBtn" type="button">
      <i class="fa-solid fa-plus"></i> Tambah Jemaah
    </button>
  </x-page-header>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Total Jemaah</span>
      </div>
      <span class="stat-value" id="statTotal">{{ number_format($stats['total']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Laki-laki / Ikhwan</span>
      </div>
      <span class="stat-value" id="statLaki">{{ number_format($stats['laki']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Perempuan / Akhwat</span>
      </div>
      <span class="stat-value" id="statPerempuan">{{ number_format($stats['perempuan']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Jemaah Aktif</span>
      </div>
      <span class="stat-value" id="statAktif">{{ number_format($stats['aktif']) }}</span>
    </div>
  </div>

  <div class="card table-card">
    <div class="filter-bar">
      <div class="filter-group filter-search-group">
        <span class="filter-label">Cari</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="searchInput" placeholder="Cari nama jemaah..." />
        </div>
      </div>

      <div class="filter-group">
        <span class="filter-label">Status</span>
        <select class="filter-select" id="statusFilter">
          <option value="">Semua Status</option>
          <option value="Aktif">Aktif</option>
          <option value="Tidak Aktif">Tidak Aktif</option>
          <option value="Pindah">Pindah</option>
          <option value="Wafat">Wafat</option>
        </select>
      </div>

      <div class="filter-group">
        <span class="filter-label">Jenis Kelamin</span>
        <select class="filter-select" id="genderFilter">
          <option value="">Semua</option>
          <option value="Laki-laki">Laki-laki / Ikhwan</option>
          <option value="Perempuan">Perempuan / Akhwat</option>
        </select>
      </div>

      <div class="filter-group">
        <span class="filter-label">Urutkan</span>
        <select class="filter-select" id="sortFilter">
          <option value="nama-asc">Nama (A-Z)</option>
          <option value="nama-desc">Nama (Z-A)</option>
          <option value="bergabung-baru">Bergabung Terbaru</option>
          <option value="bergabung-lama">Bergabung Terlama</option>
        </select>
      </div>

      <div class="filter-actions">
        <button class="btn-outline" id="importBtn"><i class="fa-solid fa-upload"></i> Impor</button>
        <button class="btn-outline" id="exportBtn"><i class="fa-solid fa-download"></i> Ekspor</button>
        <input type="file" id="importJamaahInput" accept=".csv,text/csv" hidden />
      </div>
    </div>

    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Nomor HP</th>
            <th>Jenis Kelamin</th>
            <th>Status Jemaah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="jamaahTableBody"></tbody>
      </table>
    </div>

    <div class="table-pagination">
      <span class="pagination-info" id="paginationInfo">Menampilkan data jemaah</span>
      <div class="pagination-controls" id="paginationControls">
        <button class="pagination-btn" id="prevPageBtn" type="button" aria-label="Halaman sebelumnya">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="page-numbers" id="pageNumbers"></div>
        <button class="pagination-btn" id="nextPageBtn" type="button" aria-label="Halaman berikutnya">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>

@endsection

@section('modals')
  <!-- Detail Jemaah Modal -->
  <div class="modal-overlay" id="detailModalOverlay">
    <div class="modal-box detail-modal">
      <div class="modal-header">
        <h2>Detail Jemaah</h2>
        <button class="modal-close" id="closeDetailModalX" type="button" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body detail-modal-body">
        <div class="detail-left">
          <div class="detail-avatar-large" id="detailAvatar">
            <i class="fa-solid fa-user"></i>
          </div>
          <h3 class="detail-name" id="detailName">-</h3>
          <span class="status-badge" id="detailStatusBadge">-</span>
        </div>

        <div class="detail-right">
          <div class="detail-info-grid">
            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-venus-mars"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Jenis Kelamin</span>
                <span class="detail-info-value" id="detailGender">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-cake-candles"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Tempat, Tanggal Lahir</span>
                <span class="detail-info-value" id="detailTtl">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-phone"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Nomor HP</span>
                <span class="detail-info-value" id="detailHp">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-envelope"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Email</span>
                <span class="detail-info-value" id="detailEmail">-</span>
              </div>
            </div>

            <div class="detail-info-item detail-info-item-wide">
              <span class="detail-info-icon"><i class="fa-solid fa-location-dot"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Alamat Lengkap</span>
                <span class="detail-info-value" id="detailAlamat">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-briefcase"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Pekerjaan</span>
                <span class="detail-info-value" id="detailPekerjaan">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-heart"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Status Pernikahan</span>
                <span class="detail-info-value" id="detailNikah">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-calendar-check"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">Tanggal Bergabung</span>
                <span class="detail-info-value" id="detailBergabung">-</span>
              </div>
            </div>
          </div>

          <div class="detail-note-box">
            <span class="detail-info-label"><i class="fa-solid fa-note-sticky"></i> Catatan</span>
            <p id="detailCatatan">-</p>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-outline" id="closeDetailModalBtn" type="button">Tutup</button>
        <button class="btn btn-primary" id="openEditFromDetailBtn" type="button"><i class="fa-solid fa-pen"></i> Edit Data</button>
      </div>
    </div>
  </div>

  <!-- Tambah Jemaah Modal -->
  <div class="modal-overlay" id="addModalOverlay">
    <div class="modal-box add-modal">
      <div class="modal-header">
        <h2 id="addModalTitle">Tambah Jemaah Baru</h2>
        <button class="modal-close" id="closeAddModalX" type="button" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form id="addJamaahForm">
        <div class="modal-body">
          <div class="avatar-upload-wrap">
            <div class="avatar-upload" id="avatarUploadPreview">
              <i class="fa-solid fa-user"></i>
            </div>
            <label class="avatar-upload-btn" for="avatarUploadInput">
              <i class="fa-solid fa-camera"></i> Upload Foto
            </label>
            <input type="file" id="avatarUploadInput" accept="image/*" hidden />
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="addNama">Nama Lengkap</label>
              <input class="form-input" type="text" id="addNama" placeholder="Contoh: Ahmad Fauzi" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="addGender">Jenis Kelamin</label>
              <select class="form-select" id="addGender" data-native-select required>
                <option value="">Pilih Jenis Kelamin</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addTempatLahir">Tempat Lahir</label>
              <input class="form-input" type="text" id="addTempatLahir" placeholder="Contoh: Bandung" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addTanggalLahir">Tanggal Lahir</label>
              <input class="form-input" type="date" id="addTanggalLahir" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addHp">Nomor HP</label>
              <input class="form-input" type="text" id="addHp" placeholder="Contoh: 0812-3456-7890" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="addEmail">Email</label>
              <input class="form-input" type="email" id="addEmail" placeholder="Contoh: nama@email.com" />
            </div>

            <div class="form-group form-group-wide">
              <label class="form-label" for="addAlamat">Alamat Lengkap</label>
              <textarea
                class="form-textarea"
                id="addAlamat"
                rows="2"
                placeholder="Jl. ... RT/RW ... Kelurahan ... Kota"
              ></textarea>
            </div>

            <div class="form-group">
              <label class="form-label" for="addPekerjaan">Pekerjaan</label>
              <input class="form-input" type="text" id="addPekerjaan" placeholder="Contoh: Wiraswasta" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addNikah">Status Pernikahan</label>
              <select class="form-select" id="addNikah" data-native-select>
                <option value="Belum Menikah">Belum Menikah</option>
                <option value="Menikah">Menikah</option>
                <option value="Janda">Janda</option>
                <option value="Duda">Duda</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addStatusJamaah">Status Jemaah</label>
              <select class="form-select" id="addStatusJamaah" data-native-select>
                <option value="Aktif">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
                <option value="Pindah">Pindah</option>
                <option value="Wafat">Wafat</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addTanggalBergabung">Tanggal Bergabung</label>
              <input class="form-input" type="date" id="addTanggalBergabung" />
            </div>

            <div class="form-group form-group-wide">
              <label class="form-label" for="addCatatan">Catatan</label>
              <textarea
                class="form-textarea"
                id="addCatatan"
                rows="2"
                placeholder="Catatan tambahan (opsional)"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-outline" id="cancelAddModalBtn" type="button">Batal</button>
          <button class="btn btn-primary" id="addModalSubmitBtn" type="submit"><i class="fa-solid fa-check"></i> Simpan Jemaah</button>
        </div>
      </form>
    </div>
  </div>

  @endsection

@push('scripts')
  <script>
    window.__JAMAAH_DATA__ = @json($jamaahList);
  </script>
  <script src="{{ asset('assets/js/data-jamaah.js') }}"></script>
@endpush
