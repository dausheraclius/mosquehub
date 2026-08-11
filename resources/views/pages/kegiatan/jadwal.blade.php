@extends('layouts.app')

@section('title', 'MosqueHub - Jadwal Kegiatan')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush

@push('styles-after-components')
  <link rel="stylesheet" href="{{ asset('assets/css/jadwal-kegiatan.css') }}">
@endpush

@section('content')

  <x-page-header crumb="Kegiatan" active="Jadwal Kegiatan" title="Jadwal Kegiatan" subtitle="Pantau seluruh kegiatan masjid yang telah dijadwalkan hari ini." />

  <div class="stat-cards" id="jkStatCards">
    <div class="stat-card jk-stat-card active" data-status-filter="">
      <div class="stat-card-top"><span class="stat-label">Total Kegiatan Hari Ini</span></div>
      <span class="stat-value">{{ $stats['total'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="berlangsung">
      <div class="stat-card-top"><span class="stat-label">Sedang Berlangsung</span></div>
      <span class="stat-value">{{ $stats['berlangsung'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="akan-datang">
      <div class="stat-card-top"><span class="stat-label">Akan Datang</span></div>
      <span class="stat-value">{{ $stats['akanDatang'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="selesai">
      <div class="stat-card-top"><span class="stat-label">Selesai</span></div>
      <span class="stat-value">{{ $stats['selesai'] }}</span>
    </div>
  </div>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">
      <span class="filter-label">Cari</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="jkSearch" placeholder="Cari nama kegiatan..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Tanggal</span>
      <input type="date" class="filter-select" id="jkDate" value="{{ now()->format('Y-m-d') }}" />
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="jkKategori">
        <option value="">Semua Kategori</option>
        <option value="kajian">Kajian</option>
        <option value="rapat">Rapat</option>
        <option value="sosial">Sosial</option>
        <option value="operasional">Operasional</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Lokasi</span>
      <select class="filter-select" id="jkLokasi">
        <option value="">Semua Lokasi</option>
        <option value="aula-utama">Aula Utama</option>
        <option value="ruang-sekretariat">Ruang Sekretariat</option>
        <option value="halaman-masjid">Halaman Masjid</option>
        <option value="ruang-serbaguna">Ruang Serbaguna</option>
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Status</span>
      <select class="filter-select" id="jkStatus">
        <option value="">Semua Status</option>
        <option value="berlangsung">Sedang Berlangsung</option>
        <option value="akan-datang">Akan Datang</option>
        <option value="selesai">Selesai</option>
      </select>
    </div>
    <div class="filter-actions">
      <button class="btn-outline" id="jkReset"><i class="fa-solid fa-rotate-left"></i> Reset Filter</button>
    </div>
  </div>

  <div class="jk-layout">
    <div class="jk-timeline" id="jkTimeline">
      @forelse ($kegiatanHariIni as $k)
        <div
          class="jk-timeline-item status-{{ $k['statusSlug'] }}"
          data-name="{{ strtolower($k['nama']) }}"
          data-category="{{ $k['kategoriSlug'] }}"
          data-location="{{ $k['lokasiSlug'] }}"
          data-status="{{ $k['statusSlug'] }}"
        >
          <div class="jk-timeline-time">{{ $k['jamMulaiDot'] }}</div>
          <div class="jk-timeline-dot-col"><span class="jk-timeline-dot"></span></div>
          <div class="jk-activity-card">
            <div class="jk-activity-top">
              <div>
                <div class="jk-activity-name">{{ $k['nama'] }}</div>
                <div class="jk-activity-meta">
                  <span><i class="fa-regular fa-clock"></i>{{ $k['jamMulai'] }} - {{ $k['jamSelesai'] }}</span>
                  <span><i class="fa-solid fa-location-dot"></i>{{ $k['lokasi'] ?: '-' }}</span>
                  <span><i class="fa-solid fa-user"></i>{{ $k['pemateri'] ?: '-' }}</span>
                </div>
              </div>
              <div class="jk-badges">
                <span class="jk-badge cat-{{ $k['kategoriSlug'] }}">{{ $k['kategori'] }}</span>
                <span class="jk-badge status-{{ $k['statusSlug'] }}">{{ $k['status'] }}</span>
              </div>
            </div>
            <div class="jk-activity-bottom">
              <button type="button" class="btn-sm btn-detail" onclick="jkToggleDetail(this)">Detail</button>
            </div>
            <div class="jk-detail-panel">
              <div class="jk-detail-row">
                <span class="label">Deskripsi</span><span>{{ $k['deskripsi'] ?: '-' }}</span>
              </div>
              <div class="jk-detail-row">
                <span class="label">Pemateri</span><span>{{ $k['pemateri'] ?: '-' }}</span>
              </div>
              <div class="jk-detail-row">
                <span class="label">Estimasi Peserta</span><span>{{ $k['peserta'] ?? '-' }}</span>
              </div>
            </div>
          </div>
        </div>
      @empty
      @endforelse

      <div class="jk-empty {{ $kegiatanHariIni->isEmpty() ? 'show' : '' }}" id="jkEmpty">
        {{ $kegiatanHariIni->isEmpty() ? 'Tidak ada kegiatan hari ini.' : 'Tidak ada kegiatan yang cocok dengan filter yang dipilih.' }}
      </div>
    </div>

    <aside class="jk-rail">
      <div class="card jk-side-card">
        <div class="card-header"><span class="card-title">Kegiatan Berikutnya</span></div>
        @if ($kegiatanBerikutnya)
          <div class="jk-next-activity">
            <div class="jk-next-time">{{ $kegiatanBerikutnya->jam_mulai ? str_replace(':', '.', substr($kegiatanBerikutnya->jam_mulai, 0, 5)) : '-' }}</div>
            <div class="jk-next-info">
              <div class="jk-next-title">{{ $kegiatanBerikutnya->nama }}</div>
              <div class="jk-next-sub">{{ $kegiatanBerikutnya->lokasi ?: '-' }}<br>{{ $kegiatanBerikutnya->pemateri ?: $kegiatanBerikutnya->pj }}</div>
            </div>
          </div>
        @else
          <p style="font-size:13px;color:var(--text-muted);padding:8px 0;">Tidak ada kegiatan lagi hari ini.</p>
        @endif
      </div>

      <div class="card jk-side-card">
        <div class="card-header"><span class="card-title">Jadwal Besok</span></div>
        <div class="jk-mini-list">
          @forelse ($jadwalBesok as $jb)
            <div class="jk-mini-item">
              <span class="time">{{ $jb->jam_mulai ? str_replace(':', '.', substr($jb->jam_mulai, 0, 5)) : '-' }}</span>
              <span class="name">{{ $jb->nama }}</span>
              <span class="loc">{{ $jb->lokasi ?: '-' }}</span>
            </div>
          @empty
            <p style="font-size:13px;color:var(--text-muted);">Belum ada jadwal besok.</p>
          @endforelse
        </div>
      </div>

      <div class="card jk-side-card">
        <div class="card-header"><span class="card-title">Kegiatan Minggu Ini</span></div>
        <div class="jk-week-count">{{ $mingguIni }}</div>
        <div class="jk-week-label">kegiatan terjadwal minggu ini</div>
      </div>
    </aside>
  </div>

  <div class="card jk-history">
    <div class="card-header"><span class="card-title">Riwayat Kegiatan Hari Ini</span></div>
    <div class="jk-history-list">
      @forelse ($riwayatSelesai as $r)
        <div class="jk-history-item">
          <div class="jk-history-check"><i class="fa-solid fa-check"></i></div>
          <div class="jk-history-info">
            <div class="jk-history-title">{{ $r->nama }}</div>
            <div class="jk-history-sub">
              {{ $r->jam_mulai ? substr($r->jam_mulai, 0, 5) : '-' }} - {{ $r->jam_selesai ? substr($r->jam_selesai, 0, 5) : '-' }} · {{ $r->lokasi ?: '-' }} · {{ $r->pemateri ?: $r->pj }}
            </div>
          </div>
        </div>
      @empty
        <p style="font-size:13px;color:var(--text-muted);">Belum ada kegiatan yang selesai hari ini.</p>
      @endforelse
    </div>
  </div>

@endsection

@push('scripts')
  <script src="{{ asset('assets/js/jadwal-kegiatan.js') }}"></script>
@endpush