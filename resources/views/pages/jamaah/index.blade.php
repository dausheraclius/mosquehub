@extends('layouts.app')

@section('title', 'MosqueHub - ' . __('pages.jamaah.title'))

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/data-jamaah.css')
@endpush

@section('content')

  <x-page-header active="{{ __('pages.jamaah.title') }}" title="{{ __('pages.jamaah.title') }}" subtitle="{{ __('pages.jamaah.subtitle') }}">
    <button class="btn btn-primary" id="openAddJamaahBtn" type="button">
      <i class="fa-solid fa-plus"></i> {{ __('general.tambah_jemaah') }}
    </button>
  </x-page-header>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('pages.jamaah.total_jemaah') }}</span>
      </div>
      <span class="stat-value" id="statTotal">{{ number_format($stats['total']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('pages.jamaah.laki_ikhwan') }}</span>
      </div>
      <span class="stat-value" id="statLaki">{{ number_format($stats['laki']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('pages.jamaah.perempuan_akhwat') }}</span>
      </div>
      <span class="stat-value" id="statPerempuan">{{ number_format($stats['perempuan']) }}</span>
    </div>

    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('pages.jamaah.jemaah_aktif') }}</span>
      </div>
      <span class="stat-value" id="statAktif">{{ number_format($stats['aktif']) }}</span>
    </div>
  </div>

  <div class="card table-card">
    <div class="filter-bar">
      <div class="filter-group filter-search-group">
        <span class="filter-label">{{ __('general.cari') }}</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="searchInput" placeholder="{{ __('pages.jamaah.cari_nama') }}" />
        </div>
      </div>

      <div class="filter-group">
        <span class="filter-label">{{ __('general.status') }}</span>
        <select class="filter-select" id="statusFilter">
          <option value="">{{ __('general.semua_status') }}</option>
          <option value="Aktif">{{ __('general.aktif') }}</option>
          <option value="Tidak Aktif">{{ __('general.tidak_aktif') }}</option>
          <option value="Pindah">{{ __('general.pindah') }}</option>
          <option value="Wafat">{{ __('general.wafat') }}</option>
        </select>
      </div>

      <div class="filter-group">
        <span class="filter-label">{{ __('pages.jamaah.jenis_kelamin') }}</span>
        <select class="filter-select" id="genderFilter">
          <option value="">{{ __('pages.jamaah.semua') }}</option>
          <option value="Laki-laki">{{ __('pages.jamaah.laki_ikhwan') }}</option>
          <option value="Perempuan">{{ __('pages.jamaah.perempuan_akhwat') }}</option>
        </select>
      </div>

      <div class="filter-group">
        <span class="filter-label">{{ __('general.urutkan') }}</span>
        <select class="filter-select" id="sortFilter">
          <option value="nama-asc">{{ __('general.nama_az') }}</option>
          <option value="nama-desc">{{ __('general.nama_za') }}</option>
          <option value="bergabung-baru">{{ __('pages.jamaah.bergabung_terbaru') }}</option>
          <option value="bergabung-lama">{{ __('pages.jamaah.bergabung_terlama') }}</option>
        </select>
      </div>

      <div class="filter-actions">
        <button class="btn-outline" id="importBtn"><i class="fa-solid fa-upload"></i> {{ __('pages.jamaah.impor') }}</button>
        <button class="btn-outline" id="exportBtn"><i class="fa-solid fa-download"></i> {{ __('pages.jamaah.ekspor') }}</button>
        <input type="file" id="importJamaahInput" accept=".csv,text/csv" hidden />
      </div>
    </div>

    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>{{ __('general.foto') }}</th>
            <th>{{ __('pages.jamaah.nama_lengkap') }}</th>
            <th>{{ __('general.email') }}</th>
            <th>{{ __('pages.jamaah.nomor_hp') }}</th>
            <th>{{ __('pages.jamaah.jenis_kelamin') }}</th>
            <th>{{ __('pages.jamaah.status_jemaah') }}</th>
            <th>{{ __('general.aksi') }}</th>
          </tr>
        </thead>
        <tbody id="jamaahTableBody"></tbody>
      </table>
    </div>

    <div class="table-pagination">
      <span class="pagination-info" id="paginationInfo">{{ __('pages.jamaah.title') }}</span>
      <div class="pagination-controls" id="paginationControls">
        <button class="pagination-btn" id="prevPageBtn" type="button" aria-label="{{ __('general.sebelumnya') }}">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="page-numbers" id="pageNumbers"></div>
        <button class="pagination-btn" id="nextPageBtn" type="button" aria-label="{{ __('general.berikutnya') }}">
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
        <h2>{{ __('pages.jamaah.detail_jemaah') }}</h2>
        <button class="modal-close" id="closeDetailModalX" type="button" aria-label="{{ __('general.tutup') }}">
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
                <span class="detail-info-label">{{ __('pages.jamaah.jenis_kelamin') }}</span>
                <span class="detail-info-value" id="detailGender">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-cake-candles"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.tempat_tanggal_lahir') }}</span>
                <span class="detail-info-value" id="detailTtl">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-phone"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.nomor_hp') }}</span>
                <span class="detail-info-value" id="detailHp">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-envelope"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('general.email') }}</span>
                <span class="detail-info-value" id="detailEmail">-</span>
              </div>
            </div>

            <div class="detail-info-item detail-info-item-wide">
              <span class="detail-info-icon"><i class="fa-solid fa-location-dot"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.alamat_lengkap') }}</span>
                <span class="detail-info-value" id="detailAlamat">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-briefcase"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.pekerjaan') }}</span>
                <span class="detail-info-value" id="detailPekerjaan">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-heart"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.status_pernikahan') }}</span>
                <span class="detail-info-value" id="detailNikah">-</span>
              </div>
            </div>

            <div class="detail-info-item">
              <span class="detail-info-icon"><i class="fa-solid fa-calendar-check"></i></span>
              <div class="detail-info-text">
                <span class="detail-info-label">{{ __('pages.jamaah.tanggal_bergabung') }}</span>
                <span class="detail-info-value" id="detailBergabung">-</span>
              </div>
            </div>
          </div>

          <div class="detail-note-box">
            <span class="detail-info-label"><i class="fa-solid fa-note-sticky"></i> {{ __('general.catatan') }}</span>
            <p id="detailCatatan">-</p>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-outline" id="closeDetailModalBtn" type="button">{{ __('general.tutup') }}</button>
        <button class="btn btn-primary" id="openEditFromDetailBtn" type="button"><i class="fa-solid fa-pen"></i> {{ __('pages.jamaah.edit_data') }}</button>
      </div>
    </div>
  </div>

  <!-- Tambah Jemaah Modal -->
  <div class="modal-overlay" id="addModalOverlay">
    <div class="modal-box add-modal">
      <div class="modal-header">
        <h2 id="addModalTitle">{{ __('pages.jamaah.tambah_baru') }}</h2>
        <button class="modal-close" id="closeAddModalX" type="button" aria-label="{{ __('general.tutup') }}">
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
              <i class="fa-solid fa-camera"></i> {{ __('pages.jamaah.upload_foto') }}
            </label>
            <input type="file" id="avatarUploadInput" accept="image/*" hidden />
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="addNama">{{ __('pages.jamaah.nama_lengkap') }}</label>
              <input class="form-input" type="text" id="addNama" placeholder="{{ __('pages.jamaah.contoh_nama') }}" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="addGender">{{ __('pages.jamaah.jenis_kelamin') }}</label>
              <select class="form-select" id="addGender" data-native-select required>
                <option value="">{{ __('pages.jamaah.pilih_jenis_kelamin') }}</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addTempatLahir">{{ __('general.tempat_lahir') }}</label>
              <input class="form-input" type="text" id="addTempatLahir" placeholder="{{ __('pages.jamaah.contoh_tempat') }}" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addTanggalLahir">{{ __('general.tanggal_lahir') }}</label>
              <input class="form-input" type="date" id="addTanggalLahir" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addHp">{{ __('pages.jamaah.nomor_hp') }}</label>
              <input class="form-input" type="text" id="addHp" placeholder="{{ __('pages.jamaah.contoh_hp') }}" required />
            </div>

            <div class="form-group">
              <label class="form-label" for="addEmail">{{ __('general.email') }}</label>
              <input class="form-input" type="email" id="addEmail" placeholder="{{ __('pages.jamaah.contoh_email') }}" />
            </div>

            <div class="form-group form-group-wide">
              <label class="form-label" for="addAlamat">{{ __('pages.jamaah.alamat_lengkap') }}</label>
              <textarea
                class="form-textarea"
                id="addAlamat"
                rows="2"
                placeholder="{{ __('pages.jamaah.contoh_alamat') }}"
              ></textarea>
            </div>

            <div class="form-group">
              <label class="form-label" for="addPekerjaan">{{ __('pages.jamaah.pekerjaan') }}</label>
              <input class="form-input" type="text" id="addPekerjaan" placeholder="{{ __('pages.jamaah.contoh_pekerjaan') }}" />
            </div>

            <div class="form-group">
              <label class="form-label" for="addNikah">{{ __('pages.jamaah.status_pernikahan') }}</label>
              <select class="form-select" id="addNikah" data-native-select>
                <option value="Belum Menikah">{{ __('pages.jamaah.belum_menikah') }}</option>
                <option value="Menikah">{{ __('pages.jamaah.menikah') }}</option>
                <option value="Janda">Janda</option>
                <option value="Duda">Duda</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addStatusJamaah">{{ __('pages.jamaah.status_jemaah') }}</label>
              <select class="form-select" id="addStatusJamaah" data-native-select>
                <option value="Aktif">{{ __('general.aktif') }}</option>
                <option value="Tidak Aktif">{{ __('general.tidak_aktif') }}</option>
                <option value="Pindah">{{ __('general.pindah') }}</option>
                <option value="Wafat">{{ __('general.wafat') }}</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="addTanggalBergabung">{{ __('pages.jamaah.tanggal_bergabung') }}</label>
              <input class="form-input" type="date" id="addTanggalBergabung" />
            </div>

            <div class="form-group form-group-wide">
              <label class="form-label" for="addCatatan">{{ __('general.catatan') }}</label>
              <textarea
                class="form-textarea"
                id="addCatatan"
                rows="2"
                placeholder="{{ __('pages.jamaah.catatan_opsional') }}"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-outline" id="cancelAddModalBtn" type="button">{{ __('general.batal') }}</button>
          <button class="btn btn-primary" id="addModalSubmitBtn" type="submit"><i class="fa-solid fa-check"></i> {{ __('pages.jamaah.simpan_jemaah') }}</button>
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
