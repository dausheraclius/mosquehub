@extends('layouts.app')

@section('title', 'MosqueHub - Laporan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/laporan.css')
@endpush

@section('content')

  <x-page-header active="Laporan" title="Laporan" subtitle="Semua laporan administrasi masjid tersedia dalam satu halaman.">
    <div class="laporan-header-actions">
      <button class="date-range-btn" id="dateRangeBtn"><i class="fa-regular fa-calendar"></i> <span>{{ now()->startOfMonth()->translatedFormat('d M Y') }} - {{ now()->translatedFormat('d M Y') }}</span></button>
      <button class="btn btn-outline" id="exportSemuaPdfBtn">
        <i class="fa-solid fa-file-pdf"></i> Ekspor Semua PDF
      </button>
      <button class="btn btn-primary" id="exportSemuaBtn">
        <i class="fa-solid fa-file-excel"></i> Ekspor Semua Excel
      </button>
    </div>
  </x-page-header>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari laporan..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="kategoriFilter">
        <option value="">Kategori Laporan</option>
        <option value="Jemaah">Jemaah</option>
        <option value="Keuangan">Keuangan</option>
        <option value="Kegiatan">Kegiatan</option>
        <option value="Aset">Aset</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Periode</span>
      <select class="filter-select" id="periodeFilter">
        <option value="">Periode</option>
        <option value="Harian">Harian</option>
        <option value="Mingguan">Mingguan</option>
        <option value="Bulanan">Bulanan</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Tahun</span>
      <select class="filter-select" id="tahunFilter">
        <option value="">Tahun</option>
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