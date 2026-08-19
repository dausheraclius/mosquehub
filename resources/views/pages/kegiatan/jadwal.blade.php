@extends('layouts.app')

@section('title', 'MosqueHub - Jadwal Kegiatan')

@push('styles-before-components')
  @vite('resources/assets/css/dashboard.css')
@endpush

@push('styles-after-components')
  @vite('resources/assets/css/jadwal-kegiatan.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.kegiatan') }}" active="{{ __('menu.jadwal_kegiatan') }}" title="{{ __('pages.kegiatan.jadwal_title') }}" subtitle="{{ __('pages.kegiatan.jadwal_subtitle') }}">
    <a href="{{ route('ekspor.jadwal', ['locale' => App::getLocale()]) }}" class="btn btn-outline" title="{{ __('general.unduh') }}">
      <i class="fa-solid fa-file-excel"></i> {{ __('general.ekspor_excel') }}
    </a>
  </x-page-header>

  <div class="stat-cards" id="jkStatCards">
    <div class="stat-card jk-stat-card active" data-status-filter="">
      <div class="stat-card-top"><span class="stat-label">{{ __('general.total') }} {{ __('menu.agenda') }}</span></div>
      <span class="stat-value">{{ $stats['total'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="berlangsung">
      <div class="stat-card-top"><span class="stat-label">{{ __('general.berlangsung') }}</span></div>
      <span class="stat-value">{{ $stats['berlangsung'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="akan-datang">
      <div class="stat-card-top"><span class="stat-label">{{ __('general.akan_datang') }}</span></div>
      <span class="stat-value">{{ $stats['akanDatang'] }}</span>
    </div>
    <div class="stat-card jk-stat-card" data-status-filter="selesai">
      <div class="stat-card-top"><span class="stat-label">{{ __('general.selesai') }}</span></div>
      <span class="stat-value">{{ $stats['selesai'] }}</span>
    </div>
  </div>

  <div class="filter-bar">
    <div class="filter-group filter-search-group">        <span class="filter-label">{{ __('general.cari') }}</span>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="jkSearch" placeholder="{{ __('general.cari_nama_kategori_lokasi') }}" />
      </div>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.tanggal') }}</span>
      <input type="date" id="jkDate" value="" />
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.kategori') }}</span>
      <select class="filter-select" id="jkKategori">
        <option value="">{{ __('general.semua') }} {{ __('general.kategori') }}</option>
        @foreach ($kategoriOptions as $opt)
          <option value="{{ $opt }}" @selected($filters['kategori'] === $opt)>{{ $opt }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.lokasi') }}</span>
      <select class="filter-select" id="jkLokasi">
        <option value="">{{ __('general.semua') }} {{ __('general.lokasi') }}</option>
        @foreach ($lokasiOptions as $opt)
          <option value="{{ $opt }}" @selected($filters['lokasi'] === $opt)>{{ $opt }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-group">        <span class="filter-label">{{ __('general.status') }}</span>
      <select class="filter-select" id="jkStatus">
        <option value="">{{ __('general.semua_status') }}</option>
        <option value="berlangsung">Sedang Berlangsung</option>
        <option value="akan-datang">Akan Datang</option>
        <option value="selesai">Selesai</option>
      </select>
    </div>
    <div class="filter-actions">
      <button class="btn-outline" id="jkReset"><i class="fa-solid fa-rotate-left"></i> {{ __('general.reset') }} Filter</button>
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
        <div class="card-header"><span class="card-title">{{ __('general.kegiatan_berikutnya') }}</span></div>
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
        <div class="card-header"><span class="card-title">{{ __('general.jadwal_besok') }}</span></div>
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
        <div class="card-header"><span class="card-title">{{ __('general.kegiatan_minggu_ini') }}</span></div>
        <div class="jk-week-count">{{ $mingguIni }}</div>
        <div class="jk-week-label">{{ __('general.kegiatan_terjadwal_minggu_ini') }}</div>
      </div>
    </aside>
  </div>

  <div class="card jk-history">
    <div class="card-header"><span class="card-title">{{ __('general.riwayat_kegiatan_selesai') }}</span></div>
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