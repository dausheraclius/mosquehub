@extends('layouts.app')

@section('title', 'MosqueHub - Kas Masjid')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/kas-masjid.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.keuangan') }}" active="{{ __('menu.kas_masjid') }}" title="{{ __('pages.keuangan.kas_title') }}" subtitle="{{ __('pages.keuangan.kas_subtitle') }}">
      <button class="btn btn-primary" id="btnTransaksiBaru"><i class="fa-solid fa-plus"></i> {{ __('general.transaksi_baru') }}</button>
  </x-page-header>

  <!-- KARTU SALDO UTAMA -->
  <div class="finance-summary-card">
    <div>
      <div class="saldo-label">Saldo Kas Saat Ini</div>
      <div class="saldo-value" id="saldoSekarang">Rp {{ number_format($stats['saldoSekarang'], 0, ',', '.') }}</div>
      <div class="saldo-sub" id="tanggalHariIni">Hingga Hari Ini, {{ now()->translatedFormat('d F Y') }}</div>
    </div>
    <div class="summary-mini">
      <div>
        <div class="summary-mini-label">Total Pemasukan Bulan Ini</div>
        <div class="summary-mini-value in" id="pemasukanBulanIni">Rp {{ number_format($stats['pemasukanBulanIni'], 0, ',', '.') }}</div>
      </div>
      <div>
        <div class="summary-mini-label">Total Pengeluaran Bulan Ini</div>
        <div class="summary-mini-value out" id="pengeluaranBulanIni">Rp {{ number_format($stats['pengeluaranBulanIni'], 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  <!-- 4 STAT CARDS -->
  <div class="finance-stat-cards">
    <div class="finance-stat-card">
      <div class="finance-stat-icon up"><i class="fa-solid fa-arrow-up"></i></div>
      <div class="finance-stat-content">
        <span class="finance-stat-label">Total Pemasukan</span>
        <span class="finance-stat-value" id="statPemasukan">Rp {{ number_format($stats['pemasukanTahunIni'], 0, ',', '.') }}</span>
        <span class="finance-stat-sub">Tahun Berjalan</span>
      </div>
    </div>
    <div class="finance-stat-card">
      <div class="finance-stat-icon down"><i class="fa-solid fa-arrow-down"></i></div>
      <div class="finance-stat-content">
        <span class="finance-stat-label">Total Pengeluaran</span>
        <span class="finance-stat-value" id="statPengeluaran">Rp {{ number_format($stats['pengeluaranTahunIni'], 0, ',', '.') }}</span>
        <span class="finance-stat-sub">Tahun Berjalan</span>
      </div>
    </div>
    <div class="finance-stat-card">
      <div class="finance-stat-icon doc"><i class="fa-solid fa-file-lines"></i></div>
      <div class="finance-stat-content">
        <span class="finance-stat-label">Jumlah Transaksi</span>
        <span class="finance-stat-value" id="statJumlah">{{ $stats['jumlahTransaksi'] }}</span>
        <span class="finance-stat-sub">Total Transaksi</span>
      </div>
    </div>
    <div class="finance-stat-card">
      <div class="finance-stat-icon wallet"><i class="fa-solid fa-sack-dollar"></i></div>
      <div class="finance-stat-content">
        <span class="finance-stat-label">Saldo Akhir</span>
        <span class="finance-stat-value" id="statSaldoAkhir">Rp {{ number_format($stats['saldoSekarang'], 0, ',', '.') }}</span>
        <span class="finance-stat-sub">Hingga Hari Ini</span>
      </div>
    </div>
  </div>

  <!-- CHART + AKTIVITAS -->
  <div class="dashboard-grid">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Tren Saldo Kas</h2>
      </div>
      <div class="chart-wrapper">
        <canvas id="trenSaldoChart"></canvas>
      </div>
    </div>

    <div class="card agenda-card">
      <div class="card-header">
        <h2 class="card-title">Aktivitas Terbaru</h2>
      </div>
      <ul class="activity-list" id="activityList"></ul>
    </div>
  </div>

  <!-- TABLE -->
  <div class="card table-card">
    <div class="filter-bar">
      <div class="filter-group filter-search-group">
        <span class="filter-label">{{ __('general.cari') }}</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="searchInput" placeholder="Cari" />
        </div>
      </div>
      <div class="filter-group">
        <span class="filter-label">{{ __('general.bulan') }}</span>
        <select class="filter-select" id="bulanFilter">
          <option value="">{{ __('general.semua') }} {{ __('general.bulan') }}</option>
          @foreach($dataBulan as $bulan)
            <option value="{{ $bulan['value'] }}">{{ $bulan['label'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">{{ __('general.kategori') }}</span>
        <select class="filter-select" id="kategoriFilter">
          <option value="">{{ __('general.semua') }} {{ __('general.kategori') }}</option>
          @foreach($dataKategori as $kategori)
            <option value="{{ $kategori }}">{{ $kategori }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-actions">
        <button class="btn-outline" id="btnExport"><i class="fa-solid fa-download"></i> {{ __('general.ekspor_excel') }}</button>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jenis Transaksi</th>
          <th>Kategori</th>
          <th>Ket</th>
          <th class="col-income">Pemasukan</th>
          <th class="col-expense">Pengeluaran</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="kasTableBody"></tbody>
    </table>
  </div>

  <!-- MODAL: TRANSAKSI BARU / EDIT -->
  <div class="modal-overlay" id="transaksiModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="transaksiModalTitle">{{ __('general.transaksi_baru') }}</h2>
        <button class="modal-close" id="closeTransaksiModal" type="button"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="transaksiForm">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label" for="txTipe">Jenis Transaksi</label>
            <select class="form-select" id="txTipe" required>
              <option value="Pemasukan">Pemasukan</option>
              <option value="Pengeluaran">Pengeluaran</option>
            </select>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="txTanggal">Tanggal</label>
              <input class="form-input" type="date" id="txTanggal" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="txJumlah">Jumlah (Rp)</label>
              <input class="form-input" type="number" id="txJumlah" placeholder="Contoh: 500000" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="txJenis">Nama Transaksi</label>
            <input class="form-input" type="text" id="txJenis" placeholder="Contoh: Infaq Jumat">
          </div>
          <div class="form-group">
            <label class="form-label" for="txKategori">Kategori</label>
            <input class="form-input" type="text" id="txKategori" placeholder="Contoh: Operasional">
          </div>
          <div class="form-group">
            <label class="form-label" for="txKeterangan">Keterangan</label>
            <textarea class="form-textarea" id="txKeterangan" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-outline" type="button" id="cancelTransaksiModal">{{ __('general.batal') }}</button>
          <button class="btn btn-primary" type="submit" id="transaksiSubmitBtn"><i class="fa-solid fa-check"></i> {{ __('general.simpan') }} {{ __('general.transaksi') }}</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: DETAIL TRANSAKSI -->
  <div class="modal-overlay" id="detailModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2>{{ __('general.detail_transaksi') }}</h2>
        <button class="modal-close" id="closeDetailModal" type="button"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="detail-table">
          <div class="detail-row">
            <span class="detail-label">Jenis Transaksi</span>
            <span class="detail-value" id="detailTipe">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Tanggal</span>
            <span class="detail-value" id="detailTanggal">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Nama Transaksi</span>
            <span class="detail-value" id="detailJenis">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Kategori</span>
            <span class="detail-value" id="detailKategori">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Jumlah</span>
            <span class="detail-value" id="detailJumlah">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Keterangan</span>
            <span class="detail-value" id="detailKeterangan">-</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Dibuat Oleh</span>
            <span class="detail-value" id="detailOleh">-</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="closeDetailModalBtn" type="button">{{ __('general.tutup') }}</button>
        <button class="btn btn-primary" id="editFromDetailBtn" type="button"><i class="fa-solid fa-pen"></i> {{ __('general.edit') }} {{ __('general.transaksi') }}</button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    window.__KAS_DATA__ = @json($dataTransaksi);
    window.__AKTIVITAS_DATA__ = @json($dataAktivitas);
  </script>
  <script src="{{ asset('assets/js/kas-masjid.js') }}"></script>
@endpush