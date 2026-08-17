<!-- HEADER UTAMA -->
<header class="main-header">    <div class="header-left">
      <button class="hamburger-btn" id="hamburgerBtn">
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- 🖼️ GANTI: foto profil user asli. Ganti <div class="header-avatar"> jadi <img src="assets/img/avatar-user.png" class="header-avatar"> -->
      <a href="{{ route('profil') }}" class="header-user-link" title="Profil Saya">
        <div class="header-avatar">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="header-user-info">
          <span class="header-user-name">{{ auth()->user()->name }}</span>
          <span class="header-user-role">{{ auth()->user()->role ?? 'Ketua YMBPK' }}</span>
        </div>
      </a>
    </div>

  <div class="header-center">
    {{-- Logo masjid dari Pengaturan → Profil Masjid; kalau belum ada / dihapus, pakai ikon default. --}}
    <div class="header-institution-logo" id="headerInstitutionLogo">
      @if ($siteMosque->getFirstMediaUrl('logo'))
        <img src="{{ $siteMosque->getFirstMediaUrl('logo') }}" alt="Logo {{ $siteMosque->name }}">
      @else
        <i class="fa-solid fa-mosque" id="headerInstitutionIcon"></i>
      @endif
    </div>
    {{-- Nama header mengikuti nama masjid (Pengaturan → Profil Masjid), bukan app_name. --}}
    <span class="header-institution-name">{{ $siteMosque->name ?: $appPengaturan?->app_name }}</span>
  </div>

  <div class="header-right">
    <div class="header-app-info">
      <span class="header-app-label">Aplikasi</span>
      <span class="header-app-name">Sistem Informasi Masjid</span>
    </div>
    <div class="header-divider"></div>
    <div class="header-vendor-logo">
      <!-- 🖼️ GANTI: logo brand MosqueHub asli kalau ada file logonya -->
      <i class="fa-solid fa-house-chimney-crack header-vendor-icon"></i>
      <div class="header-vendor-text">
        <span class="header-vendor-sub">Powered by</span>
        <span class="header-vendor-brand">MosqueHub</span>
      </div>
    </div>
  </div>
</header>