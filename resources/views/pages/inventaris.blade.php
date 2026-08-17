@extends('layouts.app')

@section('title', 'MosqueHub - Inventaris')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/data-jamaah.css')
  @vite('resources/assets/css/inventaris.css')
@endpush

@section('content')

  <x-page-header crumb="Pengelolaan" active="Inventaris" title="Inventaris" subtitle="Kelola seluruh aset dan perlengkapan masjid dengan rapi dan mudah.">
    <button class="btn btn-primary" id="tambahInventarisBtn">
      <i class="fa-solid fa-plus"></i> Tambah Inventaris
    </button>
    <a href="{{ route('ekspor.inventaris') }}" class="btn btn-outline" title="Unduh seluruh inventaris sebagai Excel">
      <i class="fa-solid fa-file-excel"></i> Ekspor Excel
    </a>
  </x-page-header>

  <!-- STAT CARDS -->
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Total Item</span>
      </div>
      <span class="stat-value" id="statTotal">9</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Kondisi Baik</span>
      </div>
      <span class="stat-value" id="statGood">6</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Perlu Perhatian</span>
      </div>
      <span class="stat-value" id="statAttention">2</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Nonaktif</span>
      </div>
      <span class="stat-value" id="statInactive">1</span>
    </div>
  </div>

  <div class="filter-bar">
    <div class="filter-group" style="flex:1; min-width:200px;">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari item..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="kategoriFilter">
        <option value="">Kategori</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Kondisi</span>
      <select class="filter-select" id="kondisiFilter">
        <option value="">Kondisi</option>
        <option value="Baik">Baik</option>
        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
        <option value="Rusak">Rusak</option>
        <option value="Nonaktif">Nonaktif</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Lokasi</span>
      <select class="filter-select" id="lokasiFilter">
        <option value="">Lokasi</option>
      </select>
    </div>
  </div>

  <div class="card table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th><input type="checkbox" id="checkAll"></th>
          <th>Nama Item</th>
          <th>Kategori</th>
          <th>Lokasi</th>
          <th>Kondisi</th>
          <th>Jumlah</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="inventarisTableBody"></tbody>
    </table>
    <div class="pagination-row" id="paginationRow" style="margin-top:16px;"></div>
  </div>

  <!-- MODAL: DETAIL INVENTARIS -->
  <div class="modal-overlay" id="detailModal">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Detail Inventaris</h2>
        <button class="modal-close" id="closeDetailModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="detailModalBody"></div>
    </div>
  </div>

  <!-- MODAL: TAMBAH INVENTARIS -->
  <div class="modal-overlay" id="tambahModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title" id="tambahModalTitle">Tambah Inventaris</span>
        <button class="modal-close" id="closeTambahModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Item</label>
          <input type="text" class="form-control" id="formNama" placeholder="Contoh: Kipas Angin">
        </div>
        <div class="form-group">
          <label class="form-label">Gambar</label>
          <div class="file-upload-wrapper">
            <input type="file" class="form-control-file" id="formGambar" accept="image/*">
            <div class="file-upload-preview" id="gambarPreview"><i class="fa-regular fa-image"></i><span>Pilih gambar</span></div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <input type="text" class="form-control" id="formKategori" placeholder="Contoh: Elektronik">
          </div>
          <div class="form-group">
            <label class="form-label">Lokasi</label>
            <input type="text" class="form-control" id="formLokasi" placeholder="Contoh: Ruang Sholat Utama">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kondisi</label>
            <select class="form-control" id="formKondisi">
              <option value="Baik">Baik</option>
              <option value="Perlu Perbaikan">Perlu Perbaikan</option>
              <option value="Rusak">Rusak</option>
              <option value="Nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Jumlah</label>
            <input type="text" class="form-control" id="formQty" placeholder="Contoh: 1 set">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Sumber</label>
            <select class="form-control" id="formSumber">
              <option value="Beli">Beli</option>
              <option value="Waqaf">Waqaf</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Beli</label>
            <input type="date" class="form-control" id="formTglBeli">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Harga Beli</label>
          <input type="text" class="form-control" id="formHarga" placeholder="Contoh: Rp 1.000.000">
        </div>
        <div class="form-group">
          <label class="form-label">Catatan</label>
          <textarea class="form-control" id="formCatatan" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelTambahBtn">Batal</button>
        <button class="btn btn-primary" id="simpanTambahBtn">Simpan</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    window.inventarisData = @json($inventarisList)
  </script>
  <script src="{{ asset('assets/js/inventaris.js') }}"></script>
@endpush