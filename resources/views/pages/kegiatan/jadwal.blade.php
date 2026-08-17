@extends('layouts.app')

@section('title', 'MosqueHub - Jadwal Kegiatan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/jadwal-kegiatan.css')
@endpush

@section('content')

  <x-page-header crumb="Kegiatan" active="Jadwal Kegiatan" title="Jadwal Kegiatan" subtitle="Pantau seluruh agenda masjid lengkap dengan tanggal, waktu, dan detailnya.">
    <a href="{{ route('ekspor.jadwal') }}" class="btn btn-outline" title="Unduh jadwal hari ini sebagai Excel">
      <i class="fa-solid fa-file-excel"></i> Ekspor Excel
    </a>
  </x-page-header>

  <div class="stat-cards" id="jkStatCards">
    <div class="stat-card jk-stat-card active" data-status-filter="">
      <div class="stat-card-top"><span class="stat-label">Total Agenda</span></div>
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
        <input type="text" id="jkSearch" placeholder="Cari nama / kategori / lokasi..." />
      </div>
    </div>
    <div class="filter-group">
      <span class="filter-label">Tanggal</span>
      <input type="date" id="jkDate" value="" />
    </div>
    <div class="filter-group">
      <span class="filter-label">Kategori</span>
      <select class="filter-select" id="jkKategori">
        <option value="">Semua Kategori</option>
        @foreach ($kategoriOptions as $opt)
          <option value="{{ $opt }}" @selected($filters['kategori'] === $opt)>{{ $opt }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">
      <span class="filter-label">Lokasi</span>
      <select class="filter-select" id="jkLokasi">
        <option value="">Semua Lokasi</option>
        @foreach ($lokasiOptions as $opt)
          <option value="{{ $opt }}" @selected($filters['lokasi'] === $opt)>{{ $opt }}</option>
        @endforeach
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
    <div id="jkListWrap">
      @include('pages.kegiatan.partials.jadwal-timeline', [
          'agendaList' => $agendaList,
          'isEmpty' => $isEmpty,
          'paginator' => $paginator,
          'pageWindow' => $pageWindow,
          'hasActiveFilter' => $hasActiveFilter,
      ])
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
    <div class="card-header"><span class="card-title">Riwayat Kegiatan Selesai</span></div>
    <div class="jk-history-list">
      @forelse ($riwayatSelesai as $r)
        <div class="jk-history-item">
          <div class="jk-history-check"><i class="fa-solid fa-check"></i></div>
          <div class="jk-history-info">
            <div class="jk-history-title">{{ $r->nama }}</div>
            <div class="jk-history-sub">
              {{ $r->tanggal?->format('d M Y') }} · {{ $r->jam_mulai ? substr($r->jam_mulai, 0, 5) : '-' }} - {{ $r->jam_selesai ? substr($r->jam_selesai, 0, 5) : '-' }} · {{ $r->lokasi ?: '-' }} · {{ $r->pemateri ?: $r->pj }}
            </div>
          </div>
        </div>
      @empty
        <p style="font-size:13px;color:var(--text-muted);">Belum ada kegiatan yang selesai.</p>
      @endforelse
    </div>
  </div>

@endsection

@push('scripts')
  <script src="{{ asset('assets/js/jadwal-kegiatan.js') }}"></script>
@endpush