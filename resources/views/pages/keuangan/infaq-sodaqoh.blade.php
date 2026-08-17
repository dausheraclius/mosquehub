@extends('layouts.app')

@section('title', 'MosqueHub - Infaq & Sodaqoh')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/infaq-sodaqoh.css')
@endpush

@section('content')

  <!-- BREADCRUMB GLOBAL (di luar kedua view) — tombol collapse sidebar dari include.js
       disisipkan ke sini, jadi tetap terlihat di tab manapun (tidak ikut tersembunyi
       saat view standar di-hide ketika tab Qurban aktif). -->
  <div class="breadcrumb">Keuangan / <span id="ziswafBreadcrumbLabel">Ziswaf</span></div>

  <!-- PAGE HEADER GLOBAL (judul & tombol aksi berubah sesuai tab aktif via JS) -->
  <x-page-header crumb="Keuangan" active="Infaq & Sodaqoh" title="Ringkasan Ziswaf" subtitle="Kelola seluruh penerimaan Zakat, Infaq, Sodaqoh, Waqaf dan Donasi jemaah." titleId="ziswafPageTitle" subtitleId="ziswafPageSubtitle">
    <button class="btn btn-primary" id="btnCatatDonasi"><i class="fa-solid fa-plus"></i> Catat Donasi</button>
    <button class="btn btn-primary" id="btnTambahPeserta" style="display:none"><i class="fa-solid fa-user-plus"></i> Tambah Peserta</button>
  </x-page-header>

  <!-- TAB KATEGORI: Ringkasan / Zakat / Infaq / Sodaqoh / Waqaf / Donasi / Qurban -->
  <!-- Diletakkan DI LUAR #ziswafStandardView supaya tidak ikut tersembunyi saat tab Qurban aktif -->
  <div class="ziswaf-tabs" id="ziswafTabs">
    <button type="button" class="ziswaf-tab active" data-tab="ringkasan">
      <i class="fa-solid fa-chart-pie"></i> Ringkasan
    </button>
    <button type="button" class="ziswaf-tab" data-tab="zakat">
      <i class="fa-solid fa-hand-holding-dollar"></i> Zakat
    </button>
    <button type="button" class="ziswaf-tab" data-tab="infaq">
      <i class="fa-solid fa-hand-holding-heart"></i> Infaq
    </button>
    <button type="button" class="ziswaf-tab" data-tab="sedekah"><i class="fa-solid fa-heart"></i> Sodaqoh</button>
    <button type="button" class="ziswaf-tab" data-tab="wakaf">
      <i class="fa-solid fa-building-columns"></i> Waqaf
    </button>
    <button type="button" class="ziswaf-tab" data-tab="donasi"><i class="fa-solid fa-gift"></i> Donasi</button>
    <button type="button" class="ziswaf-tab" data-tab="qurban"><i class="fa-solid fa-piggy-bank"></i> Qurban</button>
  </div>

  <!-- ============ VIEW STANDAR: Ringkasan/Zakat/Infaq/Sodaqoh/Waqaf/Donasi ============ -->
  <div id="ziswafStandardView">

    <!-- DONASI SUMMARY -->
    <div class="donasi-summary-card">
      <div>
        <div class="donasi-summary-label" id="summaryLabel">Total Donasi Bulan Ini (Semua Kategori)</div>
        <div class="donasi-summary-value" id="totalDonasi">Rp 58.700.000</div>
      </div>
      <div class="donasi-summary-trend">
        <i class="fa-solid fa-arrow-trend-up"></i>
        <div>
          <div class="donasi-summary-trend-value" id="summaryTrendValue">+18%</div>
          <div class="donasi-summary-trend-label">Dibanding bulan lalu</div>
        </div>
      </div>
    </div>

    <!-- STATISTIK: ganti isi sesuai tab yg aktif -->
    <div class="stat-cards" id="statCardsRow"></div>

    <!-- INSIGHT DONASI: ganti isi sesuai tab yg aktif -->
    <div class="card insight-card">
      <div class="card-header">
        <h2 class="card-title" id="insightCardTitle">Wawasan Donasi</h2>
      </div>
      <div class="insight-grid" id="insightGrid"></div>
    </div>

    <!-- GRAFIK + DONASI TERBARU -->
    <div class="dashboard-grid">
      <div class="card chart-card">
        <div class="card-header">
          <h2 class="card-title" id="chartCardTitle">Komposisi Donasi (Semua Kategori)</h2>
        </div>
        <div class="doughnut-wrapper">
          <canvas id="donasiDoughnutChart"></canvas>
        </div>
        <div class="doughnut-legend" id="doughnutLegend"></div>
      </div>

      <div class="card agenda-card">
        <div class="card-header">
          <h2 class="card-title" id="donorListTitle">Donasi Terbaru</h2>
        </div>
        <ul class="donor-list" id="donorList"></ul>
      </div>
    </div>

    <!-- REKAP PER KATEGORI (Ringkasan) -->
    <div class="card" id="rekapKategoriCard" hidden>
      <div class="card-header">
        <h2 class="card-title">Rekap Penerimaan per Kategori</h2>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table rekap-kategori-table">
          <thead id="rekapKategoriHead"></thead>
          <tbody id="rekapKategoriBody"></tbody>
        </table>
      </div>
    </div>

    <!-- TABEL DONASI -->
    <div class="card table-card">
      <div class="filter-bar">
        <div class="filter-group filter-search-group">
          <span class="filter-label">Cari</span>
          <div class="filter-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari penyumbang..." />
          </div>
        </div>
        <div class="filter-group">
          <span class="filter-label" id="jenisFilterLabel">Jenis Donasi</span>
          <select class="filter-select" id="jenisFilter">
            <option value="">Semua Jenis</option>
          </select>
        </div>
        <div class="filter-group">
          <span class="filter-label">Periode</span>
          <select class="filter-select" id="periodeFilter">
            <option value="">Semua Periode</option>
          </select>
        </div>
        <div class="filter-group">
          <span class="filter-label">Metode</span>
          <select class="filter-select" id="metodeFilter">
            <option value="">Semua Metode</option>
            <option value="Tunai">Tunai</option>
            <option value="Transfer">Transfer</option>
            <option value="QRIS">QRIS</option>
          </select>
        </div>
        <div class="filter-actions">
          <button class="btn-outline" id="btnExport"><i class="fa-solid fa-download"></i> Ekspor</button>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th id="thDonatur">Penyumbang</th>
            <th>Kategori</th>
            <th id="thJenis">Jenis Donasi</th>
            <th id="thNominal">Nominal / Taksiran</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="donasiTableBody"></tbody>
      </table>
      <div class="table-pager" id="tablePager"></div>
    </div>
  </div>

  <!-- ============ VIEW: TABUNGAN QURBAN ============ -->
  <div id="qurbanView" hidden>

    <div class="stat-cards" id="qurbanStatCards"></div>

    <div class="card" style="margin-top:20px;">
      <div class="card-header">
        <h2 class="card-title">Daftar Peserta</h2>
        <span class="qurban-card-sub" id="qurbanTotalSetoran"></span>
      </div>
      <div class="qurban-peserta-list" id="qurbanPesertaList"></div>
    </div>
  </div>

@endsection

@section('modals')
  <!-- MODAL: CATAT DONASI -->
  <div class="modal-overlay" id="donasiModalOverlay">
    <div class="modal-box donasi-modal" id="donasiModalBox">
      <div class="modal-header">
        <h2 id="donasiModalTitle">Catat Donasi</h2>
        <button class="modal-close" id="donasiModalCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group form-group-wide" id="formGroupDonasiDonatur">
            <label class="form-label" for="inputDonasiDonatur">Nama Donatur</label>
            <input class="form-input" type="text" id="inputDonasiDonatur" placeholder="cth: Bpk. Ahmad Fauzi">
          </div>
          <div class="form-group" id="formGroupDonasiKategori">
            <label class="form-label" for="inputDonasiKategori">Kategori</label>
            <select class="form-select" id="inputDonasiKategori" disabled>
              <option value="Zakat">Zakat</option>
              <option value="Infaq">Infaq</option>
              <option value="Sodaqoh">Sodaqoh</option>
              <option value="Wakaf">Wakaf</option>
              <option value="Donasi">Donasi</option>
            </select>
          </div>
          <div class="form-group" id="formGroupDonasiTipe">
            <label class="form-label" for="inputDonasiTipe">Tipe</label>
            <select class="form-select" id="inputDonasiTipe">
              <option value="Uang">Uang</option>
              <option value="Barang">Barang</option>
            </select>
          </div>
          <div class="form-group form-group-wide" id="formGroupDonasiJenis">
            <label class="form-label" for="inputDonasiJenis">Jenis Donasi</label>
            <input class="form-input" type="text" id="inputDonasiJenis" placeholder="cth: Donasi Bencana, Pendidikan, Kesehatan, dll">
          </div>
          <div class="form-group" id="formGroupDonasiNominal">
            <label class="form-label" id="formLabelDonasiNominal" for="inputDonasiNominal">Nominal / Taksiran (Rp)</label>
            <input class="form-input" type="number" id="inputDonasiNominal" placeholder="cth: 500000">
          </div>
          <div class="form-group" id="formGroupDonasiTanggal">
            <label class="form-label" for="inputDonasiTanggal">Tanggal</label>
            <input class="form-input" type="date" id="inputDonasiTanggal">
          </div>
          <div class="form-group-wide" id="formGroupDonasiPeriode">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label" for="inputDonasiDari">Periode Dari</label>
                <input class="form-input" type="date" id="inputDonasiDari">
              </div>
              <div class="form-group">
                <label class="form-label" for="inputDonasiSampai">Sampai</label>
                <input class="form-input" type="date" id="inputDonasiSampai">
              </div>
            </div>
          </div>
          <div class="form-group-wide">
            <div class="form-grid-3">
              <div class="form-group" id="formGroupDonasiMetode">
                <label class="form-label" for="inputDonasiMetode">Metode</label>
                <select class="form-select" id="inputDonasiMetode">
                  <option value="Tunai">Tunai</option>
                  <option value="Transfer">Transfer</option>
                  <option value="QRIS">QRIS</option>
                </select>
              </div>
              <div class="form-group" id="formGroupDonasiPetugas">
                <label class="form-label" for="inputDonasiPetugas">Petugas</label>
                <select class="form-select" id="inputDonasiPetugas"></select>
              </div>
              <div class="form-group" id="formGroupDonasiStatus">
                <label class="form-label" for="inputDonasiStatus">Status</label>
                <select class="form-select" id="inputDonasiStatus">
                  <option value="Berhasil">Berhasil</option>
                  <option value="Pending">Pending</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group form-group-wide" id="formGroupDonasiKeterangan">
            <label class="form-label" for="inputDonasiKeterangan">Keterangan</label>
            <textarea class="form-textarea" id="inputDonasiKeterangan" rows="2" placeholder="Catatan tambahan (opsional, penting buat Wakaf Barang)"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="donasiModalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="donasiModalSaveBtn"><i class="fa-solid fa-check"></i> Simpan Donasi</button>
      </div>
    </div>
  </div>

  <!-- MODAL: TAMBAH PESERTA QURBAN -->
  <div class="modal-overlay" id="pesertaModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2 id="pesertaModalTitle">Tambah Peserta Tabungan Qurban</h2>
        <button class="modal-close" id="pesertaModalCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group form-group-wide" id="formGroupPesertaNama">
            <label class="form-label" for="inputPesertaNama">Nama Peserta</label>
            <input class="form-input" type="text" id="inputPesertaNama" placeholder="cth: Bpk. Slamet Riyadi" list="datalistJamaah">
          </div>
          <div class="form-group">
            <label class="form-label" for="inputPesertaPaket">Paket Qurban</label>
            <select class="form-select" id="inputPesertaPaket">
              <option value="Patungan Sapi">Patungan Sapi</option>
              <option value="Kambing">Kambing</option>
              <option value="Sapi Utuh">Sapi Utuh</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="inputPesertaTarget">Target (Rp)</label>
            <input class="form-input" type="number" id="inputPesertaTarget" placeholder="cth: 3000000">
          </div>
        </div>

        <div class="qurban-member-section" id="memberSection" hidden>
          <div class="qurban-member-section-head">
            <span class="form-label">Anggota Patungan</span>
            <span class="qurban-member-count" id="memberCount">0/7</span>
          </div>
          <p class="qurban-member-hint">Pilih dari Data Jemaah (ketik lalu pilih) atau tulis nama langsung. Maksimal 7 orang.</p>
          <div id="memberList"></div>
          <button type="button" class="btn-outline btn-sm-outline" id="btnTambahMember"><i class="fa-solid fa-plus"></i> Tambah Anggota</button>
        </div>

        <div class="form-group" style="margin-top:4px;">
          <label class="form-label" for="inputPesertaMulai">Tanggal Mulai Nabung</label>
          <input class="form-input" type="date" id="inputPesertaMulai">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="pesertaModalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="pesertaModalSaveBtn"><i class="fa-solid fa-check"></i> Simpan Peserta</button>
      </div>
    </div>
  </div>

  <!-- MODAL: TAMBAH ANGGOTA PATUNGAN (isi slot sampai 7/7) -->
  <div class="modal-overlay" id="memberModalOverlay">
    <div class="modal-box confirm-modal">
      <div class="modal-header">
        <h2>Tambah Anggota Patungan</h2>
        <button class="modal-close" id="memberModalCloseBtn" type="button" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="inputMemberNama">Nama Anggota</label>
          <input class="form-input" type="text" id="inputMemberNama" placeholder="Ketik atau pilih dari Data Jemaah" list="datalistJamaah">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="memberModalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="memberModalSaveBtn"><i class="fa-solid fa-check"></i> Simpan</button>
      </div>
    </div>
  </div>

  <datalist id="datalistJamaah"></datalist>

  <!-- MODAL: CATAT SETORAN -->
  <div class="modal-overlay" id="setoranModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2><span id="setoranModalTitle">Catat Setoran</span> - <span id="setoranPesertaNama">-</span></h2>
        <button class="modal-close" id="setoranModalCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group" id="formGroupSetoranMember">
          <label class="form-label" for="inputSetoranMember">Penyetor (Anggota Patungan)</label>
          <select class="form-select" id="inputSetoranMember"></select>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="inputSetoranJumlah">Jumlah Setoran (Rp)</label>
            <input class="form-input" type="number" id="inputSetoranJumlah" placeholder="cth: 200000">
          </div>
          <div class="form-group">
            <label class="form-label" for="inputSetoranTanggal">Tanggal Setoran</label>
            <input class="form-input" type="date" id="inputSetoranTanggal">
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="inputSetoranMetode">Metode</label>
            <select class="form-select" id="inputSetoranMetode">
              <option value="Tunai">Tunai</option>
              <option value="Transfer">Transfer</option>
              <option value="QRIS">QRIS</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="inputSetoranPetugas">Petugas</label>
            <select class="form-select" id="inputSetoranPetugas"></select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="setoranModalCancelBtn">Batal</button>
        <button class="btn btn-primary" id="setoranModalSaveBtn"><i class="fa-solid fa-check"></i> Simpan Setoran</button>
      </div>
    </div>
  </div>

  <!-- MODAL: DETAIL TRANSAKSI -->
  <div class="modal-overlay" id="detailTransaksiModalOverlay">
    <div class="modal-box">
      <div class="modal-header">
        <h2>Detail Transaksi</h2>
        <button class="modal-close" id="detailTransaksiCloseBtn"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="detail-transaksi-grid">
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Donatur</span>
            <span class="detail-transaksi-value" id="dtDonatur">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Tanggal</span>
            <span class="detail-transaksi-value" id="dtTanggal">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Kategori</span>
            <span class="detail-transaksi-value" id="dtKategori">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Jenis Donasi</span>
            <span class="detail-transaksi-value" id="dtJenis">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Tipe</span>
            <span class="detail-transaksi-value" id="dtTipe">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Nominal / Taksiran</span>
            <span class="detail-transaksi-value highlight" id="dtNominal">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Metode Pembayaran</span>
            <span class="detail-transaksi-value" id="dtMetode">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Petugas</span>
            <span class="detail-transaksi-value" id="dtPetugas">-</span>
          </div>
          <div class="detail-transaksi-item">
            <span class="detail-transaksi-label">Status</span>
            <span class="detail-transaksi-value" id="dtStatus">-</span>
          </div>
        </div>
        <div class="detail-transaksi-keterangan">
          <span class="detail-transaksi-label"><i class="fa-solid fa-note-sticky"></i> Keterangan</span>
          <p id="dtKeterangan">-</p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-outline" id="detailTransaksiCloseBtn2">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Modal konfirmasi hapus sudah global di app.blade.php -->
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    window.__DONASI_DATA__ = @json($donasiList);
    window.__QURBAN_DATA__ = @json($qurbanList);
    window.__PETUGAS_OPTIONS__ = @json($petugasList);
    window.__JAMAAH_OPTIONS__ = @json($jamaahList);
  </script>
  <script src="{{ asset('assets/js/infaq-sodaqoh.js') }}"></script>
@endpush