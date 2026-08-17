@extends('layouts.public')

@section('title', 'MosqueHub - ' . $siteMosque->name)

@section('content')
  <section class="lp-hero">
    <div class="lp-hero-overlay"></div>
    <div class="lp-hero-content">
      <h1>Selamat Datang di<br />{{ $siteMosque->name }}</h1>
      <p>MosqueHub memberikan informasi, agenda, kegiatan, pengumuman, dan layanan komunitas masjid secara terpusat.</p>
    </div>

    <div class="lp-info-cards">
      <div class="lp-info-card">
        <div class="lp-info-icon"><i class="fa-regular fa-clock"></i></div>
        <div class="lp-info-title">Jadwal Sholat Hari Ini</div>
        <div class="lp-info-desc">
          @foreach ($sholatJadwal as $key => $s)
            @if ($s['tampil'])
              {{ $s['label'] }} &nbsp;{{ $s['waktu'] }}@if (!$loop->last) &nbsp;·&nbsp;@endif
            @endif
          @endforeach
        </div>
        <a href="#jadwal-sholat" class="lp-info-link">Lihat Jadwal</a>
      </div>
      <div class="lp-info-card">
        <div class="lp-info-icon"><i class="fa-regular fa-calendar-days"></i></div>
        <div class="lp-info-title">Kegiatan Mendatang</div>
        <div class="lp-info-desc">Info kegiatan terbaru dari pengurus {{ $siteMosque->name }}.</div>
        <a href="#agenda" class="lp-info-link">Pelajari Lebih Lanjut</a>
      </div>
      <div class="lp-info-card">
        <div class="lp-info-icon"><i class="fa-solid fa-camera"></i></div>
        <div class="lp-info-title">Galeri</div>
        <div class="lp-info-desc">Dokumentasi kegiatan dan acara {{ $siteMosque->name }}.</div>
        <a href="{{ route('public.galeri') }}" class="lp-info-link">Lihat Galeri</a>
      </div>
    </div>
  </section>

  <section class="lp-section lp-section-jadwal" id="jadwal-sholat">
    <div class="lp-section-inner">
      <h2 class="lp-section-title">Jadwal Sholat Hari Ini</h2>
      @php
        $sholatIcons = [
            'subuh' => 'fa-sunrise',
            'dzuhur' => 'fa-sun',
            'ashar' => 'fa-cloud-sun',
            'maghrib' => 'fa-cloud-moon',
            'isya' => 'fa-moon',
        ];
      @endphp
      <div class="lp-sholat-grid">
        @foreach ($sholatJadwal as $key => $s)
          @if ($s['tampil'])
            <div class="lp-sholat-item">
              <span class="lp-sholat-icon"><i class="fa-solid {{ $sholatIcons[$key] ?? 'fa-clock' }}"></i></span>
              <span class="lp-sholat-name">{{ $s['label'] }}</span>
              <span class="lp-sholat-time">{{ $s['waktu'] }}</span>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </section>

  <section class="lp-section" id="agenda">
    <div class="lp-section-inner">
      <h2 class="lp-section-title">Kegiatan Mendatang</h2>
      <div class="lp-activity-grid">
        @forelse ($activities as $a)
          <div class="lp-activity-card">
            <div class="lp-activity-thumb"><i class="fa-solid fa-image"></i></div>
            <div class="lp-activity-body">
              <div class="lp-activity-title">{{ $a['title'] }}</div>
              <div class="lp-activity-meta">
                <span><i class="fa-regular fa-calendar"></i> {{ $a['date'] }}</span>
                <span><i class="fa-solid fa-location-dot"></i> {{ $a['location'] }}</span>
              </div>
              <div class="lp-activity-desc">{{ \Illuminate\Support\Str::limit($a['desc'], 80) }}</div>
              <a href="{{ route('public.jadwal') }}" class="btn-lp-view-details">Lihat Detail</a>
            </div>
          </div>
        @empty
          <p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">Belum ada kegiatan mendatang.</p>
        @endforelse
      </div>
    </div>
  </section>
@endsection