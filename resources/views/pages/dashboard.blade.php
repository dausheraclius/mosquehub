@extends('layouts.app')

@section('title', 'MosqueHub - Dashboard')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@section('content')

  <x-content-header title="Assalamu'alaikum, Ust. {{ auth()->user()->name }}" subtitle="{{ $siteMosque->name }} · {{ now()->translatedFormat('d F Y') }}">
    <a href="{{ route('jamaah.index') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Jemaah</a>
    <a href="{{ route('keuangan.infaq') }}" class="btn btn-outline"><i class="fa-regular fa-note-sticky"></i> Catat Infaq</a>
    <a href="{{ route('kegiatan.agenda') }}" class="btn btn-outline"><i class="fa-regular fa-calendar-plus"></i> Buat Agenda</a>
  </x-content-header>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Total Jemaah</span>
      </div>
      <span class="stat-value">{{ number_format($stats['totalJemaah']) }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Saldo Kas</span>
      </div>
      <span class="stat-value">Rp {{ number_format((int) $stats['saldoKas'], 0, ',', '.') }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Infaq Bulan Ini</span>
      </div>
      <span class="stat-value">Rp {{ number_format((int) $stats['infaqBulanIni'], 0, ',', '.') }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Relawan Aktif</span>
      </div>
      <span class="stat-value">{{ $stats['relawanAktif'] }} Orang</span>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Arus Kas {{ $siteMosque->name }}</h2>
        <div class="chart-legend">
          <span class="legend-item"><span class="legend-dot dot-green"></span>Pemasukan</span>
          <span class="legend-item"><span class="legend-dot dot-gray"></span>Pengeluaran</span>
        </div>
      </div>
      <div class="chart-wrapper">
        <canvas id="arusKasChart"></canvas>
      </div>
    </div>

    <div class="card agenda-card">
      <div class="card-header">
        <h2 class="card-title">Agenda Terdekat</h2>
        <div class="card-menu-wrap">
          <button class="icon-btn-plain" id="agendaCardMenuBtn" aria-label="Opsi agenda">
            <i class="fa-solid fa-ellipsis"></i>
          </button>
          <div class="card-menu" id="agendaCardMenu">
            <a href="{{ route('kegiatan.agenda') }}" class="card-menu-item">
              <i class="fa-solid fa-calendar-days"></i> Lihat Semua Agenda
            </a>
            <a href="{{ route('kegiatan.agenda') }}?tambah=1" class="card-menu-item">
              <i class="fa-solid fa-plus"></i> Buat Agenda Baru
            </a>
          </div>
        </div>
      </div>
      <ul class="agenda-list">
        @forelse ($agendaTerdekat as $a)
          <li class="agenda-item">
            <div class="agenda-date"><span class="agenda-day">{{ $a['day'] }}</span><span class="agenda-month">{{ $a['month'] }}</span></div>
            <div class="agenda-info">
              <span class="agenda-title">{{ $a['title'] }}</span>
              <span class="agenda-sub">{{ $a['sub'] }}</span>
            </div>
          </li>
        @empty
          <li class="agenda-item" style="color: var(--text-muted);">Belum ada agenda terdekat.</li>
        @endforelse
      </ul>
    </div>
  </div>

  <div class="dashboard-grid dashboard-grid-3">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Donasi &amp; Infaq 6 Bulan</h2>
      </div>
      <div class="chart-wrapper">
        <canvas id="donasiChart"></canvas>
      </div>
    </div>

    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Pertumbuhan Jemaah</h2>
      </div>
      <div class="chart-wrapper">
        <canvas id="jamaahChart"></canvas>
      </div>
    </div>

    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Distribusi Ziswaf</h2>
      </div>
      <div class="chart-wrapper chart-wrapper-donut">
        <canvas id="ziswafChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card table-card">
    <div class="card-header">
      <h2 class="card-title">Aktivitas Jemaah Terakhir</h2>
    </div>
    <div class="filter-bar" style="padding: 0 20px 16px">
      <div class="filter-group filter-search-group">
        <span class="filter-label">Cari</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Cari" id="tableSearch" />
        </div>
      </div>
      <div class="filter-group">
        <span class="filter-label">Urutkan</span>
        <select class="filter-select" id="sortFilter">
          <option value="nama-asc">Nama (A-Z)</option>
          <option value="nama-desc">Nama (Z-A)</option>
          <option value="terakhir-baru">Terakhir Hadir Terbaru</option>
          <option value="terakhir-lama">Terakhir Hadir Terlama</option>
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">Saring Status</span>
        <select class="filter-select" id="statusFilter">
          <option value="">Semua Status</option>
          <option value="Aktif">Aktif</option>
          <option value="Tidak Aktif">Tidak Aktif</option>
          <option value="Pindah">Pindah</option>
          <option value="Wafat">Wafat</option>
        </select>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th><input type="checkbox" /></th>
          <th>Foto</th>
          <th>Nama</th>
          <th>Status</th>
          <th>Terakhir Hadir</th>
          <th>Riwayat Infaq</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="jamaahTableBody">
        @forelse ($jamaahTerbaru as $j)
          <tr data-status="{{ $j['status'] }}" data-tanggal="{{ $j['tanggalBergabungIso'] }}">
            <td><input type="checkbox" /></td>
            <td><div class="table-avatar"><i class="fa-solid fa-user"></i></div></td>
            <td>{{ $j['nama'] }}</td>
            <td><span class="status-badge status-aktif">{{ $j['status'] }}</span></td>
            <td>{{ $j['tanggalBergabung'] }}</td>
            <td>{{ $j['infaq'] }}</td>
            <td>
              <button class="btn-sm btn-detail">Detail</button>
              <button class="btn-sm btn-edit">Edit</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty-state">Belum ada data jemaah.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    window.__DASHBOARD_DATA__ = {
      chart: @json($chart),
      donasiChart: @json($donasiChart),
      jamaahChart: @json($jamaahChart),
      ziswafDist: @json($ziswafDist),
      jamaahTerbaru: @json($jamaahTerbaru),
    }
  </script>
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush