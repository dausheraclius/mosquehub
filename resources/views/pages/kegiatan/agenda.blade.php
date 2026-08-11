@extends('layouts.app')

@section('title', 'MosqueHub - Agenda')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('styles-after-components')
  <link rel="stylesheet" href="{{ asset('assets/css/agenda.css') }}">
@endpush

@section('content')

  <x-page-header crumb="Kegiatan" active="Agenda" title="Agenda" subtitle="Kelola seluruh kegiatan dan agenda masjid." />

  <!-- HIGHLIGHT AGENDA HARI INI -->
  @if ($highlight)
    <div class="agenda-highlight-card">
      <div>
        <div class="agenda-highlight-label">Agenda Hari Ini</div>
        <div class="agenda-highlight-title">{{ $highlight['nama'] }}</div>
      </div>
      <div class="agenda-highlight-meta">
        <div class="agenda-meta-item"><i class="fa-regular fa-clock"></i> {{ $highlight['jamMulai'] ? $highlight['jamMulai'] . ' WIB' : '-' }}</div>
        <div class="agenda-meta-item"><i class="fa-solid fa-location-dot"></i> {{ $highlight['lokasi'] ?: '-' }}</div>
        <div class="agenda-meta-item"><i class="fa-solid fa-volume-high"></i> {{ $highlight['pemateri'] ?: '-' }}</div>
      </div>
      <span class="status-badge {{ $highlight['statusClass'] }}">{{ $highlight['status'] }}</span>
    </div>
  @endif

  <!-- 4 STAT CARDS (reuse style Dashboard) -->
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Agenda Hari Ini</span>
      </div>
      <span class="stat-value">{{ $stats['hariIni'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Agenda Minggu Ini</span>
      </div>
      <span class="stat-value">{{ $stats['mingguIni'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Agenda Bulan Ini</span>
      </div>
      <span class="stat-value">{{ $stats['bulanIni'] }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Agenda Selesai</span>
      </div>
      <span class="stat-value">{{ $stats['selesai'] }}</span>
    </div>
  </div>

  <!-- SEARCH + FILTER -->
  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari agenda..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="kategoriFilter">
        <option value="">Semua Kategori</option>
        <option value="Kajian">Kajian</option>
        <option value="Rapat">Rapat</option>
        <option value="Sosial">Sosial</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Status</span>
      <select class="filter-select" id="statusFilter">
        <option value="">Semua Status</option>
        <option value="Akan Datang">Akan Datang</option>
        <option value="Berlangsung">Berlangsung</option>
        <option value="Selesai">Selesai</option>
        <option value="Dibatalkan">Dibatalkan</option>
      </select>
    </div>
  </div>

  <!-- KALENDER (fokus utama) + PANEL AKTIVITAS -->
  <div class="dashboard-grid">
    <div class="card calendar-card">
      <div class="calendar-toolbar">
        <div class="calendar-nav">
          <button class="calendar-nav-btn" id="prevMonthBtn"><i class="fa-solid fa-chevron-left"></i></button>
          <select class="calendar-month-select" id="monthSelect"></select>
          <select class="calendar-year-select" id="yearSelect"></select>
          <button class="calendar-nav-btn" id="nextMonthBtn"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <button class="btn btn-outline" id="todayBtn" style="font-size: 11.5px">Hari Ini</button>
      </div>
      <div class="calendar-days-header">
        <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span
        ><span>Sab</span>
      </div>
      <div class="calendar-grid" id="calendarGrid"></div>
    </div>

    <div class="card agenda-card">
      <div class="card-header">
        <h2 class="card-title">Aktivitas</h2>
      </div>
      <div class="timeline-tabs">
        <div class="timeline-tab active" data-tab="hari-ini">Hari Ini</div>
        <div class="timeline-tab" data-tab="besok">Besok</div>
        <div class="timeline-tab" data-tab="minggu-ini">Minggu Ini</div>
      </div>
      <ul class="jadwal-list" id="jadwalList"></ul>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT AGENDA -->
  <div class="modal-overlay" id="formModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title" id="formModalTitle">Tambah Agenda</span>
        <button class="modal-close" id="closeFormModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Agenda</label>
          <input type="text" class="form-control" id="formNama" placeholder="Contoh: Kajian Rutin Subuh" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select class="form-control" id="formKategori">
              <option value="Ibadah">Ibadah</option>
              <option value="Pengajian Umum">Pengajian Umum</option>
              <option value="Pengajian Rutin">Pengajian Rutin</option>
              <option value="Pengajian Madrasah">Pengajian Madrasah</option>
              <option value="Kajian">Kajian</option>
              <option value="Rapat">Rapat</option>
              <option value="Sosial">Sosial</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="formTanggal" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jam Mulai</label>
            <div class="time-picker">
              <input type="hidden" id="formJamMulai" value=""/>
              <select class="form-control time-select" id="formJamMulaiHour" aria-label="Jam mulai"></select>
              <span class="time-sep">:</span>
              <select class="form-control time-select" id="formJamMulaiMin" aria-label="Menit mulai"></select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Jam Selesai</label>
            <div class="time-picker">
              <input type="hidden" id="formJamSelesai" value=""/>
              <select class="form-control time-select" id="formJamSelesaiHour" aria-label="Jam selesai"></select>
              <span class="time-sep">:</span>
              <select class="form-control time-select" id="formJamSelesaiMin" aria-label="Menit selesai"></select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Lokasi</label>
          <input
            type="text"
            class="form-control"
            id="formLokasi"
            placeholder="Contoh: YMBPK Baiturrahim (Aula)"
          />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Pemateri</label>
            <input type="text" class="form-control" id="formPemateri" placeholder="Nama pemateri (opsional)" />
          </div>
          <div class="form-group">
            <label class="form-label">Penanggung Jawab</label>
            <input
              type="text"
              class="form-control"
              id="formPJ"
              list="jamaahList"
              placeholder="Pilih dari jemaah atau ketik sendiri"
            />
            <datalist id="jamaahList">
              @foreach ($jamaahNames as $nama)
                <option value="{{ $nama }}"></option>
              @endforeach
            </datalist>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Estimasi Peserta</label>
            <input type="number" class="form-control" id="formPeserta" placeholder="Contoh: 50" />
          </div>
          <div class="form-group">
            <label class="form-label">Status Agenda</label>
            <select class="form-control" id="formStatus">
              <option value="Akan Datang">Akan Datang</option>
              <option value="Berlangsung">Berlangsung</option>
              <option value="Selesai">Selesai</option>
              <option value="Dibatalkan">Dibatalkan</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi</label>
          <textarea
            class="form-control"
            id="formDeskripsi"
            placeholder="Catatan tambahan tentang agenda ini..."
          ></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Ulangi Acara</label>
            <select class="form-control" id="formRepeat">
              <option value="Tidak Berulang">Tidak Berulang</option>
              <option value="Mingguan">Mingguan</option>
              <option value="Bulanan">Bulanan</option>
              <option value="Tahunan">Tahunan</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Pengingat</label>
            <select class="form-control" id="formReminder">
              <option value="30 Menit Sebelum">30 Menit Sebelum</option>
              <option value="1 Jam Sebelum">1 Jam Sebelum</option>
              <option value="1 Hari Sebelum">1 Hari Sebelum</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Lampiran</label>
          <input type="file" class="form-control" id="formLampiran" />
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" id="cancelFormBtn">Batal</button>
        <button class="btn btn-primary" id="saveFormBtn">Simpan</button>
      </div>
    </div>
  </div>

  <!-- MODAL: DETAIL AGENDA -->
  <div class="modal-overlay" id="detailModal">
    <div class="modal-box">
      <div class="modal-header">
        <span class="modal-title">Detail Agenda</span>
        <button class="modal-close" id="closeDetailModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="detail-list" id="detailContent"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger-outline" id="deleteAgendaBtn">
          <i class="fa-solid fa-trash"></i> Hapus
        </button>
        <button class="btn btn-primary" id="editAgendaBtn"><i class="fa-solid fa-pen"></i> Edit</button>
      </div>
    </div>
  </div>

  <!-- TOAST NOTIFIKASI -->
  <div class="toast" id="appToast"></div>

@endsection

@push('scripts')
  <script>
    window.__AGENDA_DATA__ = @json($agendaList);
  </script>
  <script src="{{ asset('assets/js/agenda.js') }}"></script>
@endpush