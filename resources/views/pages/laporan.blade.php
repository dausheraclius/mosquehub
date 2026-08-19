@extends('layouts.app')

@section('title', 'MosqueHub - Laporan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/laporan.css')
@endpush

@section('content')

  <x-page-header active="{{ __('menu.laporan_page') }}" title="{{ __('pages.laporan_title') }}" subtitle="{{ __('pages.laporan_subtitle') }}">
    <div class="laporan-header-actions">
      <button class="date-range-btn" id="dateRangeBtn"><i class="fa-regular fa-calendar"></i> <span>{{ now()->startOfMonth()->translatedFormat('d M Y') }} - {{ now()->translatedFormat('d M Y') }}</span></button>
      <button class="btn btn-outline" id="exportSemuaPdfBtn">
        <i class="fa-solid fa-file-pdf"></i> {{ __('pages.unduh_pdf') }}
      </button>
      <button class="btn btn-primary" id="exportSemuaBtn">
        <i class="fa-solid fa-file-excel"></i> {{ __('pages.unduh_excel') }}
      </button>
    </div>
  </x-page-header>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">        <span class="filter-label">{{ __('general.cari') }}</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="{{ __('general.cari_laporan') }}" />
      </div>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.kategori') }}</span>
      <select class="filter-select" id="kategoriFilter">
        <option value="">{{ __('general.kategori_laporan') }}</option>
        <option value="Jemaah">Jemaah</option>
        <option value="Keuangan">Keuangan</option>
        <option value="Kegiatan">Kegiatan</option>
        <option value="Aset">Aset</option>
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.periode') }}</span>
      <select class="filter-select" id="periodeFilter">
        <option value="">{{ __('general.periode') }}</option>
        <option value="Harian">{{ __('general.harian') }}</option>
        <option value="Mingguan">{{ __('general.mingguan') }}</option>
        <option value="Bulanan">{{ __('general.bulanan') }}</option>
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.tahun') }}</span>
      <select class="filter-select" id="tahunFilter">
        <option value="">{{ __('general.tahun') }}</option>
        @for ($tahun = now()->year; $tahun >= now()->year - 5; $tahun--)
          <option value="{{ $tahun }}">{{ $tahun }}</option>
        @endfor
      </select>
    </div>
  </div>

  <div class="laporan-split-grid">
    <!-- KIRI: GRID KARTU LAPORAN -->
    <div>
      <div class="laporan-grid" id="laporanGrid"></div>
      <div class="pagination-row" id="paginationRow"></div>
    </div>

    <!-- KANAN: PANEL PREVIEW -->
    <div class="card" id="previewPanel"></div>
  </div>

@endsection

@push('scripts')
  <script>
    window.laporanData = @json($laporanData)
  </script>
  <script src="{{ asset('assets/js/laporan.js') }}"></script>
@endpush