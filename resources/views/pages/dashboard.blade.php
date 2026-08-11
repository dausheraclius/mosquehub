@extends('layouts.app')

@section('title', 'MosqueHub - Dashboard')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@section('content')

  <x-content-header title="Assalamu'alaikum, Ust. {{ auth()->user()->name }}" subtitle="Ketua YMBPK · 20 Juli 2026">
    <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Jemaah</button>
    <button class="btn btn-outline"><i class="fa-regular fa-note-sticky"></i> Catat Infaq</button>
    <button class="btn btn-outline"><i class="fa-regular fa-calendar-plus"></i> Buat Agenda</button>
  </x-content-header>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Total Jemaah</span>
        <span class="stat-badge up">+12%</span>
      </div>
      <span class="stat-value">1,485</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Saldo Kas</span>
        <span class="stat-badge up">+8%</span>
      </div>
      <span class="stat-value">Rp 185.450.000</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Infaq Bulan Ini</span>
        <span class="stat-badge up">+15%</span>
      </div>
      <span class="stat-value">Rp 42.300.000</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">Relawan Aktif</span>
        <span class="stat-badge up">+5%</span>
      </div>
      <span class="stat-value">58 Orang</span>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">Arus Kas YMBPK Baiturrahim</h2>
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
        <button class="icon-btn-plain"><i class="fa-solid fa-ellipsis"></i></button>
      </div>
      <ul class="agenda-list">
        <li class="agenda-item">
          <div class="agenda-date"><span class="agenda-day">15</span><span class="agenda-month">Jun</span></div>
          <div class="agenda-info">
            <span class="agenda-title">Kajian Subuh Rutin</span>
            <span class="agenda-sub">Ust. Hakim</span>
          </div>
        </li>
        <li class="agenda-item">
          <div class="agenda-date"><span class="agenda-day">16</span><span class="agenda-month">Jun</span></div>
          <div class="agenda-info">
            <span class="agenda-title">Rapat YMBPK</span>
            <span class="agenda-sub">18 Jun</span>
          </div>
        </li>
        <li class="agenda-item">
          <div class="agenda-date"><span class="agenda-day">17</span><span class="agenda-month">Jun</span></div>
          <div class="agenda-info">
            <span class="agenda-title">Jumsih</span>
            <span class="agenda-sub">17 Jun</span>
          </div>
        </li>
      </ul>
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
      <div class="filter-actions">
        <button class="btn-outline"><i class="fa-solid fa-arrow-up-short-wide"></i> Urutkan</button>
        <button class="btn-outline"><i class="fa-solid fa-filter"></i> Saring</button>
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
        <tr>
          <td><input type="checkbox" /></td>
          <td><div class="table-avatar"><i class="fa-solid fa-user"></i></div></td>
          <td>M. Reza</td>
          <td><span class="status-badge status-aktif">Aktif</span></td>
          <td>15 Jun 2024</td>
          <td>Rp 500k</td>
          <td>
            <button class="btn-sm btn-detail">Detail</button>
            <button class="btn-sm btn-edit">Edit</button>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox" /></td>
          <td><div class="table-avatar"><i class="fa-solid fa-user"></i></div></td>
          <td>Fatimah</td>
          <td><span class="status-badge status-member">Anggota</span></td>
          <td>16 Jun 2024</td>
          <td>Rp 250k</td>
          <td>
            <button class="btn-sm btn-detail">Detail</button>
            <button class="btn-sm btn-edit">Edit</button>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox" /></td>
          <td><div class="table-avatar"><i class="fa-solid fa-user"></i></div></td>
          <td>S. Abdullah</td>
          <td><span class="status-badge status-aktif">Aktif</span></td>
          <td>17 Jun 2024</td>
          <td>Rp 250k</td>
          <td>
            <button class="btn-sm btn-detail">Detail</button>
            <button class="btn-sm btn-edit">Edit</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush