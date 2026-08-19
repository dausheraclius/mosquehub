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
  @vite('resources/assets/css/custom-select.css')
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

        <div class="lp-nav-drop {{ request()->routeIs('public.jadwal') || request()->routeIs('public.petugas') ? 'active' : '' }}">
          <a href="#" class="lp-nav-drop-toggle">Jadwal <i class="fa-solid fa-chevron-down"></i></a>
          <div class="lp-nav-drop-menu">
            <a href="{{ route('public.jadwal') }}">Jadwal Kegiatan</a>
            <a href="{{ route('public.petugas') }}">Petugas Sholat</a>
          </div>
        </div>

        <div class="lp-nav-drop {{ request()->routeIs('public.pengumuman') || request()->routeIs('public.galeri') ? 'active' : '' }}">
          <a href="#" class="lp-nav-drop-toggle">Informasi <i class="fa-solid fa-chevron-down"></i></a>
          <div class="lp-nav-drop-menu">
            <a href="{{ route('public.pengumuman') }}">Pengumuman</a>
            <a href="{{ route('public.galeri') }}">Galeri</a>
          </div>
        </div>

        <div class="lp-nav-drop {{ request()->routeIs('public.tentang') || request()->routeIs('public.pengurus') || request()->routeIs('public.keuangan') ? 'active' : '' }}">
          <a href="#" class="lp-nav-drop-toggle">Tentang <i class="fa-solid fa-chevron-down"></i></a>
          <div class="lp-nav-drop-menu">
            <a href="{{ route('public.tentang') }}">Tentang Masjid</a>
            <a href="{{ route('public.pengurus') }}">Pengurus</a>
            <a href="{{ route('public.keuangan') }}">Keuangan</a>
          </div>
        </div>
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
            <a href="{{ route('public.keuangan') }}">Keuangan</a>
            <a href="{{ route('public.pengurus') }}">Struktur Pengurus</a>
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

  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const drops = document.querySelectorAll('.lp-nav-drop')
      drops.forEach((drop) => {
        const toggle = drop.querySelector('.lp-nav-drop-toggle')
        if (!toggle) return
        toggle.addEventListener('click', (e) => {
          e.preventDefault()
          drops.forEach((d) => d !== drop && d.classList.remove('open'))
          drop.classList.toggle('open')
        })
      })
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.lp-nav-drop')) {
          drops.forEach((d) => d.classList.remove('open'))
        }
      })
    })
  </script>
  @yield('scripts')
</body>
</html>
