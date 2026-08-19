@extends('layouts.app')

@section('title', 'MosqueHub - Dashboard')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@section('content')

  <x-content-header title="{{ __('dashboard.assalam', ['name' => auth()->user()->name]) }}" subtitle="{{ $siteMosque->name }} · {{ now()->translatedFormat('d F Y') }}">
    <a href="{{ route('jamaah.index', ['locale' => App::getLocale()]) }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> {{ __('general.tambah_jemaah') }}</a>
    <a href="{{ route('keuangan.infaq', ['locale' => App::getLocale()]) }}" class="btn btn-outline"><i class="fa-regular fa-note-sticky"></i> {{ __('general.catat_infaq') }}</a>
    <a href="{{ route('kegiatan.agenda', ['locale' => App::getLocale()]) }}" class="btn btn-outline"><i class="fa-regular fa-calendar-plus"></i> {{ __('general.buat_agenda') }}</a>
  </x-content-header>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('dashboard.total_jemaah') }}</span>
      </div>
      <span class="stat-value">{{ number_format($stats['totalJemaah']) }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('dashboard.saldo_kas') }}</span>
      </div>
      <span class="stat-value">Rp {{ number_format((int) $stats['saldoKas'], 0, ',', '.') }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('dashboard.infaq_bulan_ini') }}</span>
      </div>
      <span class="stat-value">Rp {{ number_format((int) $stats['infaqBulanIni'], 0, ',', '.') }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top">
        <span class="stat-label">{{ __('dashboard.relawan_aktif') }}</span>
      </div>
      <span class="stat-value">{{ $stats['relawanAktif'] }} {{ __('general.orang') }}</span>
    </div>
  </div>

  <div class="dashboard-grid">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">{{ __('dashboard.arus_kas', ['name' => $siteMosque->name]) }}</h2>
        <div class="chart-legend">
          <span class="legend-item"><span class="legend-dot dot-green"></span>{{ __('dashboard.pemasukan') }}</span>
          <span class="legend-item"><span class="legend-dot dot-gray"></span>{{ __('dashboard.pengeluaran') }}</span>
        </div>
      </div>
      <div class="chart-wrapper">
        <canvas id="arusKasChart"></canvas>
      </div>
    </div>

    <div class="card agenda-card">
      <div class="card-header">
        <h2 class="card-title">{{ __('dashboard.agenda_terdekat') }}</h2>
        <div class="card-menu-wrap">
          <button class="icon-btn-plain" id="agendaCardMenuBtn" aria-label="Opsi agenda">
            <i class="fa-solid fa-ellipsis"></i>
          </button>
          <div class="card-menu" id="agendaCardMenu">
            <a href="{{ route('kegiatan.agenda', ['locale' => App::getLocale()]) }}" class="card-menu-item">
              <i class="fa-solid fa-calendar-days"></i> {{ __('general.lihat_semua_agenda') }}
            </a>
            <a href="{{ route('kegiatan.agenda', ['locale' => App::getLocale()]) }}?tambah=1" class="card-menu-item">
              <i class="fa-solid fa-plus"></i> {{ __('general.buat_agenda_baru') }}
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
          <li class="agenda-item" style="color: var(--text-muted);">{{ __('dashboard.belum_ada_agenda') }}</li>
        @endforelse
      </ul>
    </div>
  </div>

  <div class="dashboard-grid dashboard-grid-3">
    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">{{ __('dashboard.donasi_infaq') }}</h2>
      </div>
      <div class="chart-wrapper">
        <canvas id="donasiChart"></canvas>
      </div>
    </div>

    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">{{ __('dashboard.pertumbuhan_jemaah') }}</h2>
      </div>
      <div class="chart-wrapper">
        <canvas id="jamaahChart"></canvas>
      </div>
    </div>

    <div class="card chart-card">
      <div class="card-header">
        <h2 class="card-title">{{ __('dashboard.distribusi_ziswaf') }}</h2>
      </div>
      <div class="chart-wrapper chart-wrapper-donut">
        <canvas id="ziswafChart"></canvas>
      </div>
    </div>
  </div>

  <div class="card table-card">
    <div class="card-header">
      <h2 class="card-title">{{ __('dashboard.aktivitas_jemaah_terakhir') }}</h2>
    </div>
    <div class="filter-bar" style="padding: 0 20px 16px">
      <div class="filter-group filter-search-group">
        <span class="filter-label">{{ __('general.cari') }}</span>
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="{{ __('general.cari') }}" id="tableSearch" />
        </div>
      </div>
      <div class="filter-group">
        <span class="filter-label">{{ __('general.urutkan') }}</span>
        <select class="filter-select" id="sortFilter">
          <option value="nama-asc">{{ __('general.nama_az') }}</option>
          <option value="nama-desc">{{ __('general.nama_za') }}</option>
          <option value="terakhir-baru">{{ __('general.terbaru') }}</option>
          <option value="terakhir-lama">{{ __('general.terlama') }}</option>
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">{{ __('general.saring_status') }}</span>
        <select class="filter-select" id="statusFilter">
          <option value="">{{ __('general.semua_status') }}</option>
          <option value="Aktif">{{ __('general.aktif') }}</option>
          <option value="Tidak Aktif">{{ __('general.tidak_aktif') }}</option>
          <option value="Pindah">{{ __('general.pindah') }}</option>
          <option value="Wafat">{{ __('general.wafat') }}</option>
        </select>
      </div>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th><input type="checkbox" /></th>
          <th>{{ __('general.foto') }}</th>
          <th>{{ __('general.nama') }}</th>
          <th>{{ __('general.status') }}</th>
          <th>{{ __('dashboard.terakhir_hadir') }}</th>
          <th>{{ __('dashboard.riwayat_infaq') }}</th>
          <th>{{ __('general.aksi') }}</th>
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
              <button class="btn-sm btn-detail">{{ __('general.detail') }}</button>
              <button class="btn-sm btn-edit">{{ __('general.edit') }}</button>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="empty-state">{{ __('dashboard.belum_ada_data') }}</td></tr>
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
