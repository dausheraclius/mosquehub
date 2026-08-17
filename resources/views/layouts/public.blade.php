<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'MosqueHub - ' . $siteMosque->name)</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  @vite('resources/assets/css/variables.css')
  @vite('resources/assets/css/reset.css')
  @vite('resources/assets/css/landing.css')
</head>
<body>
  <header class="lp-navbar">
    <div class="lp-navbar-inner">
      <div class="lp-logo">
        <div class="lp-logo-icon"><i class="fa-solid fa-mosque"></i></div>
        <div>
          <div class="lp-logo-text">MosqueHub</div>
          <div class="lp-logo-sub">{{ $siteMosque->name }}</div>
        </div>
      </div>
      <nav class="lp-nav-links">
        <a href="{{ route('public.beranda') }}" class="{{ request()->routeIs('public.beranda') ? 'active' : '' }}">Beranda</a>
        <a href="{{ route('public.jadwal') }}" class="{{ request()->routeIs('public.jadwal') ? 'active' : '' }}">Jadwal Kegiatan</a>
        <a href="{{ route('public.pengumuman') }}" class="{{ request()->routeIs('public.pengumuman') ? 'active' : '' }}">Pengumuman</a>
        <a href="{{ route('public.galeri') }}" class="{{ request()->routeIs('public.galeri') ? 'active' : '' }}">Galeri</a>
        <a href="{{ route('public.tentang') }}" class="{{ request()->routeIs('public.tentang') ? 'active' : '' }}">Tentang Masjid</a>
        <a href="{{ route('public.kontak') }}" class="{{ request()->routeIs('public.kontak') ? 'active' : '' }}">Kontak</a>
      </nav>
    </div>
  </header>

  @yield('content')

  <footer class="lp-footer">
    <div class="lp-footer-inner">
      <div class="lp-footer-grid">
        <div>
          <div class="lp-footer-title">MosqueHub</div>
          <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.6">
            Sistem Informasi Manajemen {{ $siteMosque->name }}.
          </p>
        </div>
        <div>
          <div class="lp-footer-title">Tautan Cepat</div>
          <div class="lp-footer-links">
            <a href="{{ route('public.jadwal') }}">Jadwal Kegiatan</a>
            <a href="{{ route('public.pengumuman') }}">Pengumuman</a>
            <a href="{{ route('public.galeri') }}">Galeri</a>
          </div>
        </div>
        <div>
          <div class="lp-footer-title">Kontak</div>
          <div class="lp-footer-links">
            @if ($siteMosque->phone)
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteMosque->phone) }}">{{ $siteMosque->phone }}</a>
            @else
              <span>-</span>
            @endif
            @if ($siteMosque->email)
              <a href="mailto:{{ $siteMosque->email }}">{{ $siteMosque->email }}</a>
            @else
              <span>-</span>
            @endif
          </div>
        </div>
      </div>
      <div class="lp-footer-bottom">Hak Cipta {{ now()->year }} MosqueHub. Seluruh hak cipta dilindungi.</div>
    </div>
  </footer>

  @yield('scripts')
</body>
</html>