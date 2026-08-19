@extends('layouts.app')

@section('title', 'MosqueHub - Relawan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/relawan.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.pengelolaan') }}" active="{{ __('menu.relawan') }}" title="{{ __('pages.relawan_title') }}" subtitle="{{ __('pages.relawan_subtitle') }}">
    <a href="{{ route('ekspor.relawan', ['locale' => App::getLocale()]) }}" class="btn btn-outline" title="{{ __('general.unduh') }}">
      <i class="fa-solid fa-file-excel"></i> {{ __('general.ekspor_excel') }}
    </a>
  </x-page-header>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">        <span class="filter-label">{{ __('general.cari') }}</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="{{ __('general.cari_relawan') }}" />
      </div>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.status') }}</span>
      <select class="filter-select" id="statusFilter">
        <option value="">{{ __('general.semua_status') }}</option>
        <option value="Akan Datang">{{ __('general.akan_datang') }}</option>
        <option value="Berlangsung">{{ __('general.berlangsung') }}</option>
        <option value="Selesai">{{ __('general.selesai') }}</option>
      </select>
    </div>
  </div>

  <div class="relawan-grid" id="relawanGrid"></div>

  <div class="pagination-row" id="paginationRow"></div>

  <!-- MODAL: KELOLA RELAWAN -->
  <div class="modal-overlay" id="relawanModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title" id="relawanModalTitle">Kelola Relawan</span>
        <button class="modal-close" id="closeRelawanModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="relawan-slot-info">
          <span>Pilih jamaah yang akan bertugas sebagai relawan</span>
          <span
            ><span class="relawan-slot-count" id="slotCount">0</span> /
            <span id="slotMax">20</span> Terdaftar</span
          >
        </div>
        <div class="filter-search relawan-modal-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="jamaahSearchInput" placeholder="Cari nama jamaah..." />
        </div>
        <button class="btn btn-outline" id="tambahRelawanBtn" style="margin-bottom: 10px; width: 100%;">
          <i class="fa-solid fa-plus"></i> {{ __('general.tambah_relawan_baru') }}
        </button>
        <div class="relawan-tambah-form" id="relawanTambahForm">
          <input type="text" id="relawanNamaInput" placeholder="Nama lengkap" />
          <input type="tel" id="relawanTeleponInput" placeholder="No. Telepon" />
          <div class="relawan-tambah-actions">
            <button class="btn btn-primary" id="simpanRelawanBaruBtn"><i class="fa-solid fa-check"></i> {{ __('general.tambah') }}</button>
            <button class="btn btn-outline" id="batalTambahRelawanBtn">{{ __('general.batal') }}</button>
          </div>
        </div>
        <div class="relawan-jamaah-list" id="relawanJamaahList"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelRelawanBtn">{{ __('general.batal') }}</button>
        <button class="btn btn-primary" id="saveRelawanBtn">{{ __('general.simpan') }}</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    window.__KEGIATAN_RELAWAN__ = @json($kegiatanList);
    window.__DAFTAR_JAMAAH_RELAWAN__ = @json($daftarJamaahRelawan);
  </script>
  <script src="{{ asset('assets/js/relawan.js') }}"></script>
@endpush