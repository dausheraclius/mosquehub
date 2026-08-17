@extends('layouts.app')

@section('title', 'MosqueHub - Jadwal Petugas Sholat')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/data-jamaah.css')
  @vite('resources/assets/css/jadwal-petugas.css')
@endpush

@section('content')

  <x-page-header crumb="Kegiatan" active="Jadwal Petugas Sholat" title="Jadwal Petugas Sholat" subtitle="Kelola jadwal Khatib, Imam, dan Muadzin untuk sholat Jumat dan sholat harian.">
    <button class="btn btn-primary" id="tambahBtn">
      <i class="fa-solid fa-plus"></i> Tambah Jadwal
    </button>
    <a href="{{ route('ekspor.jadwal-petugas') }}" class="btn btn-outline" title="Unduh seluruh jadwal petugas sebagai Excel">
      <i class="fa-solid fa-file-excel"></i> Ekspor Excel
    </a>
  </x-page-header>

  <!-- STAT CARDS -->
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Total Jadwal</span>
      </div>
      <span class="stat-value" id="statTotal">{{ $stats['total'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Sholat Jumat</span>
      </div>
      <span class="stat-value" id="statJumat">{{ $stats['jumat'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Bulan Ini</span>
      </div>
      <span class="stat-value" id="statBulan">{{ $stats['bulanIni'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Dengan Khatib</span>
      </div>
      <span class="stat-value" id="statKhatib">{{ $stats['denganKhatib'] }}</span>
    </div>
  </div>

  <!-- FILTER -->
  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari nama khatib / imam / muadzin..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Sholat</span>
      <select class="filter-select" id="sholatFilter">
        <option value="">Semua Sholat</option>
        <option value="Jumat">Jumat</option>
        <option value="Subuh">Subuh</option>
        <option value="Dzuhur">Dzuhur</option>
        <option value="Ashar">Ashar</option>
        <option value="Maghrib">Maghrib</option>
        <option value="Isya">Isya</option>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label" style="display:flex; align-items:center; gap:6px; cursor:pointer;">
        <input type="checkbox" id="hideLewatFilter" style="width:auto;"> Sembunyikan yang sudah lewat
      </label>
    </div>
  </div>

  <!-- TABEL -->
  <div class="card table-card">
    <table class="data-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Hari</th>
          <th>Sholat</th>
          <th>Khatib</th>
          <th>Imam</th>
          <th>Muadzin</th>
          <th>Keterangan</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
    <div class="pagination-row" id="paginationRow" style="margin-top:16px;"></div>
  </div>

  <!-- MODAL: TAMBAH / EDIT -->
  <div class="modal-overlay" id="formModal">
    <div class="modal-box add-modal">
      <div class="modal-header">
        <span class="modal-title" id="formModalTitle">Tambah Jadwal</span>
        <button class="modal-close" id="closeModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="formTanggal" />
          </div>
          <div class="form-group">
            <label class="form-label">Sholat</label>
            <select class="form-control" id="formSholat">
              <option value="Jumat">Sholat Jumat</option>
              <option value="Subuh">Subuh</option>
              <option value="Dzuhur">Dzuhur</option>
              <option value="Ashar">Ashar</option>
              <option value="Maghrib">Maghrib</option>
              <option value="Isya">Isya</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Khatib</label>
            <div class="jp-select" data-field="khatib">
              <div class="jp-select-trigger" type="button">
                <span class="jp-select-label placeholder">— Pilih dari Data Jemaah —</span>
                <i class="fa-solid fa-chevron-down jp-select-arrow"></i>
              </div>
              <div class="jp-dropdown">
                <div class="jp-dropdown-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" placeholder="Cari jemaah..." />
                </div>
                <div class="jp-dropdown-items"></div>
              </div>
              <input type="hidden" id="formKhatib" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Imam</label>
            <div class="jp-select" data-field="imam">
              <div class="jp-select-trigger" type="button">
                <span class="jp-select-label placeholder">— Pilih dari Data Jemaah —</span>
                <i class="fa-solid fa-chevron-down jp-select-arrow"></i>
              </div>
              <div class="jp-dropdown">
                <div class="jp-dropdown-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input type="text" placeholder="Cari jemaah..." />
                </div>
                <div class="jp-dropdown-items"></div>
              </div>
              <input type="hidden" id="formImam" />
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Muadzin</label>
          <div class="jp-select" data-field="muadzin">
            <div class="jp-select-trigger" type="button">
              <span class="jp-select-label placeholder">— Pilih dari Data Jemaah —</span>
              <i class="fa-solid fa-chevron-down jp-select-arrow"></i>
            </div>
            <div class="jp-dropdown">
              <div class="jp-dropdown-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Cari jemaah..." />
              </div>
              <div class="jp-dropdown-items"></div>
            </div>
            <input type="hidden" id="formMuadzin" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan</label>
          <textarea class="form-control" id="formKeterangan" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelBtn">Batal</button>
        <button class="btn btn-primary" id="saveBtn">Simpan</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    window.__JADWAL_PETUGAS_DATA__ = @json($jadwalList);
    window.__JAMAAH_OPTIONS__ = @json($jamaahOptions);
  </script>
  <script src="{{ asset('assets/js/jadwal-petugas.js') }}"></script>
@endpush