@extends('layouts.app')

@section('title', 'MosqueHub - Surat')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/surat.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.pengelolaan') }}" active="{{ __('menu.surat') }}" title="{{ __('pages.surat_title') }}" subtitle="{{ __('pages.surat_subtitle') }}">
    <button class="btn btn-primary" id="tambahSuratBtn"><i class="fa-solid fa-plus"></i> {{ __('general.tambah') }} {{ __('menu.surat') }}</button>
    <a href="{{ route('ekspor.surat', ['locale' => App::getLocale()]) }}" class="btn btn-outline" title="{{ __('general.unduh') }}">
      <i class="fa-solid fa-file-excel"></i> {{ __('general.ekspor_excel') }}
    </a>
  </x-page-header>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="{{ __('general.cari_surat') }}" />
      </div>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.jenis') }}</span>
      <select class="filter-select" id="jenisFilter">          <option value="">{{ __('general.semua') }} {{ __('general.jenis') }}</option>
        <option value="Surat Undangan">Surat Undangan</option>
        <option value="Sertifikat">Sertifikat</option>
        <option value="Surat Keterangan">Surat Keterangan</option>
        <option value="Surat Tugas">Surat Tugas</option>
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.status') }}</span>
      <select class="filter-select" id="statusFilter">          <option value="">{{ __('general.semua_status') }}</option>
        <option value="Terkirim">Terkirim</option>
        <option value="Draft">Draf</option>
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.tanggal') }}</span>
      <input type="text" class="filter-select datepicker-input" id="dateFilter" data-datepicker placeholder="Semua Tanggal" readonly />
    </div>
  </div>

  <!-- TABEL SURAT -->
  <div class="card table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th>Nomor Surat</th>
          <th>Subjek</th>
          <th>Jenis</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="suratTableBody"></tbody>
    </table>
  </div>

  <!-- MODAL: DETAIL SURAT -->
  <div class="modal-overlay" id="detailModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title" id="detailModalTitle">Detail Surat</span>
        <button class="modal-close" id="closeDetailModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="detailModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="closeDetailBtn">{{ __('general.tutup') }}</button>
      </div>
    </div>
  </div>

  <!-- MODAL: TAMBAH SURAT -->
  <div class="modal-overlay" id="tambahModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title">Tambah Surat</span>
        <button class="modal-close" id="closeTambahModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inputNomor">Nomor Surat</label>
            <input type="text" class="form-input" id="inputNomor" />
          </div>
          <div class="form-group">
            <label class="form-label" for="inputTanggal">Tanggal</label>
            <input type="text" class="form-input" id="inputTanggal" data-datepicker readonly />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="inputSubjek">Subjek</label>
          <input type="text" class="form-input" id="inputSubjek" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="inputJenis">Jenis</label>
            <select id="inputJenis" class="form-select" data-native-select>
              <option value="Surat Undangan">Surat Undangan</option>
              <option value="Sertifikat">Sertifikat</option>
              <option value="Surat Keterangan">Surat Keterangan</option>
              <option value="Surat Tugas">Surat Tugas</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="inputStatus">Status</label>
            <select id="inputStatus" class="form-select" data-native-select>
              <option value="Draft">Draf</option>
              <option value="Terkirim">Terkirim</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="inputKepada">Kepada</label>
          <input type="text" class="form-input" id="inputKepada" />
        </div>
        <div class="form-group">
          <label class="form-label" for="inputIsi">Isi Surat</label>
          <textarea id="inputIsi" class="form-textarea" rows="5"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label" for="inputFile">File Surat (PDF/DOCX)</label>
          <div class="file-pick-wrap">
            <input type="file" id="inputFile" accept=".pdf,.docx,.doc,.jpg,.jpeg,.png" />
            <span class="file-pick-btn"><i class="fa-solid fa-paperclip"></i> Pilih File</span>
            <span class="file-pick-name" id="fileInfo">{{ __('general.belum_ada_file') }}</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="batalTambahBtn">{{ __('general.batal') }}</button>
        <button class="btn btn-primary" id="simpanTambahBtn"><i class="fa-solid fa-check"></i> {{ __('general.simpan') }}</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    window.suratData = @json($suratList)
  </script>
  <script src="{{ asset('assets/js/surat.js') }}"></script>
@endpush