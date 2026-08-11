@extends('layouts.app')

@section('title', 'MosqueHub - Surat')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('styles-after-components')
  <link rel="stylesheet" href="{{ asset('assets/css/surat.css') }}">
@endpush

@section('content')

  <x-page-header crumb="Pengelolaan" active="Surat" title="Surat" subtitle="Arsip surat resmi masjid.">
    <button class="btn btn-primary" id="tambahSuratBtn"><i class="fa-solid fa-plus"></i> Tambah Surat</button>
  </x-page-header>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari surat..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Jenis</span>
      <select class="filter-select" id="jenisFilter">
        <option value="">Semua Jenis</option>
        <option value="Surat Undangan">Surat Undangan</option>
        <option value="Sertifikat">Sertifikat</option>
        <option value="Surat Keterangan">Surat Keterangan</option>
        <option value="Surat Tugas">Surat Tugas</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Status</span>
      <select class="filter-select" id="statusFilter">
        <option value="">Semua Status</option>
        <option value="Terkirim">Terkirim</option>
        <option value="Draft">Draf</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Tanggal</span>
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
        <button class="btn btn-outline" id="closeDetailBtn">Tutup</button>
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
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Nomor Surat</label>
            <input type="text" id="inputNomor" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box;" />
          </div>
          <div>
            <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Tanggal</label>
            <input type="text" id="inputTanggal" data-datepicker readonly style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box; cursor: pointer;" />
          </div>
        </div>
        <div style="margin-bottom: 12px;">
          <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Subjek</label>
          <input type="text" id="inputSubjek" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box;" />
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Jenis</label>
            <select id="inputJenis" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box;">
              <option value="Surat Undangan">Surat Undangan</option>
              <option value="Sertifikat">Sertifikat</option>
              <option value="Surat Keterangan">Surat Keterangan</option>
              <option value="Surat Tugas">Surat Tugas</option>
            </select>
          </div>
          <div>
            <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Status</label>
            <select id="inputStatus" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box;">
              <option value="Draft">Draf</option>
              <option value="Terkirim">Terkirim</option>
            </select>
          </div>
        </div>
        <div style="margin-bottom: 12px;">
          <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Kepada</label>
          <input type="text" id="inputKepada" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box;" />
        </div>
        <div>
          <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Isi Surat</label>
          <textarea id="inputIsi" rows="5" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 13px; box-sizing: border-box; resize: vertical; font-family: inherit;"></textarea>
        </div>
        <div style="margin-top: 12px;">
          <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">File Surat (PDF/DOCX)</label>
          <input type="file" id="inputFile" accept=".pdf,.docx,.doc,.jpg,.jpeg,.png" style="font-size: 13px;" />
          <div id="fileInfo" style="font-size: 11px; color: var(--color-teal); margin-top: 4px; display: none;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="batalTambahBtn">Batal</button>
        <button class="btn btn-primary" id="simpanTambahBtn"><i class="fa-solid fa-check"></i> Simpan</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script src="{{ asset('assets/js/surat.js') }}"></script>
@endpush