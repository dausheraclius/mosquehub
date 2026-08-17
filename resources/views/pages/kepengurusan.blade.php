@extends('layouts.app')

@section('title', 'MosqueHub - Kepengurusan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/kepengurusan.css')
@endpush

@section('content')

  <x-page-header crumb="Pengelolaan" active="Kepengurusan" title="Kepengurusan" subtitle="Kelola struktur kepengurusan masjid berdasarkan data jamaah." />

  <!-- ACTION ROW: tombol aksi utama -->
  <div class="kepengurusan-actions">
    <button class="btn btn-primary" id="toggleEditBtn"><i class="fa-solid fa-pen"></i> Edit Kepengurusan</button>
    <a href="{{ route('ekspor.kepengurusan') }}" class="btn btn-outline" title="Unduh struktur kepengurusan sebagai Excel">
      <i class="fa-solid fa-file-excel"></i> Ekspor Excel
    </a>
  </div>

  <!-- TOP ROW: 4 CARD RINGKASAN -->
  <div class="kepengurusan-top-row">
    <div class="top-card" style="padding: 10px 14px">
      <div class="org-tabs" id="orgTabs">
        <button type="button" class="org-tab active" data-org="YMBPK">
          <i class="fa-solid fa-people-group"></i> YMBPK
        </button>
      </div>
    </div>

    <div class="top-card top-card-split">
      <div>
        <div class="top-card-label">Periode Aktif</div>
        <div class="top-card-value">2026 - 2029</div>
      </div>
      <div>
        <div class="top-card-label">Jumlah Pengurus</div>
        <div class="top-card-value" id="jumlahPengurusValue">0</div>
      </div>
    </div>

    <div class="top-card">
      <div class="top-card-badge-row">
        <span class="top-card-label">Jumlah Jabatan Terisi</span>
        <span class="stat-badge up" id="jabatanTerisiBadge">0 Terisi</span>
      </div>
      <div class="top-card-value" id="jabatanTerisiValue">0</div>
    </div>

    <div class="top-card">
      <div class="top-card-badge-row">
        <span class="top-card-label">Jumlah Jabatan Kosong</span>
        <span class="stat-badge" id="jabatanKosongBadge" style="background: #fdeaea; color: var(--color-red)">0 Kosong</span>
      </div>
      <div class="top-card-value" id="jabatanKosongValue">0</div>
    </div>
  </div>

  <!-- ORG CHART + LIST JABATAN -->
  <div class="dashboard-grid">
    <!-- KIRI: ORG CHART -->
    <div class="card">
      <div class="org-chart-wrapper" id="orgChartWrapper"></div>
    </div>

    <!-- KANAN: STRUKTUR + PILIH JAMAAH -->
    <div class="card">
      <div class="struktur-section" id="strukturSection" style="display:none;">
        <div class="struktur-section-title">Atur Struktur Jabatan</div>
        <p class="struktur-section-hint">Pilih atasan langsung untuk tiap jabatan. Kosongkan jika jabatan berada di level teratas.</p>
        <div id="strukturList"></div>
      </div>

      <div class="jabatan-list-header">
        <span>Nama Jabatan</span>
        <span>Pilih Jemaah</span>
      </div>
      <div id="jabatanList"></div>

      <div class="jabatan-footer-actions" id="jabatanFooterActions" style="display: none">
        <button class="btn btn-outline" id="resetBtn">Reset</button>
        <button class="btn btn-primary" id="simpanBtn">Simpan Perubahan</button>
      </div>
    </div>
  </div>

@endsection

@section('modals')
  <!-- MODAL: TAMBAH JABATAN BARU -->
  <div class="modal-overlay" id="addJabatanModal">
    <div class="modal-box" style="max-width: 420px;">
      <div class="modal-header">
        <span class="modal-title" id="addJabatanModalTitle">Tambah Jabatan</span>
        <button class="modal-close" id="closeAddJabatanModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Jabatan Baru</label>
          <input type="text" class="form-control" id="addJabatanInput" placeholder="Contoh: Sie Humas" autofocus />
        </div>
        <p id="addJabatanHint" style="font-size:12px;color:var(--text-muted);margin-top:8px;"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelAddJabatanBtn">Batal</button>
        <button class="btn btn-primary" id="confirmAddJabatanBtn"><i class="fa-solid fa-plus"></i> Tambah</button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    window.__JABATAN_LIST__ = @json($namaJabatanList);
    window.__HIERARKI__ = @json($hierarki);
    window.__PENEMPATAN__ = @json($penempatan);
    window.__DAFTAR_JAMAAH__ = @json($daftarJamaah);
  </script>
  <script src="{{ asset('assets/js/kepengurusan.js') }}"></script>
@endpush