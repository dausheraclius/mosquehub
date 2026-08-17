<!-- SIDEBAR -->
@php
  $u = auth()->user();
  $fullAccess = $u->role === 'Ketua YMBPK';
  $menuKeys = $fullAccess ? ['*'] : (array) ($u->permissions ?? []);
  $can = fn ($key) => in_array('*', $menuKeys, true) || in_array($key, $menuKeys, true);
@endphp
<aside class="sidebar" id="sidebar">
  <nav class="sidebar-nav">
    <div class="sidebar-section">
      <span class="sidebar-section-label">MENU ADMIN</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route('dashboard') }}" class="sidebar-link">
            <i class="fa-solid fa-house sidebar-icon"></i>
            <span>Beranda</span>
          </a>
        </li>

        @if ($can('data-jamaah'))
          <li class="sidebar-item">
            <a href="{{ route('jamaah.index') }}" class="sidebar-link">
              <i class="fa-solid fa-users sidebar-icon"></i>
              <span>Data Jemaah</span>
            </a>
          </li>
        @endif

        @if ($can('keuangan') || $can('Kas Masjid') || $can('Infaq / Sodaqoh (Zakat)'))
          <li class="sidebar-item has-submenu">
            <a href="#" class="sidebar-link submenu-toggle">
              <i class="fa-solid fa-wallet sidebar-icon"></i>
              <span>Keuangan</span>
              <i class="fa-solid fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu">
              @if ($can('Infaq / Sodaqoh (Zakat)') || $can('keuangan'))
                <li><a href="{{ route('keuangan.infaq') }}" class="submenu-link">Ziswaf</a></li>
              @endif
              @if ($can('Kas Masjid') || $can('keuangan'))
                <li><a href="{{ route('keuangan.kas') }}" class="submenu-link">Kas Masjid</a></li>
              @endif
            </ul>
          </li>
        @endif

        @if ($can('kegiatan') || $can('Agenda') || $can('Jadwal Kegiatan') || $can('Galeri') || $can('Jadwal Petugas Sholat'))
          <li class="sidebar-item has-submenu">
            <a href="#" class="sidebar-link submenu-toggle">
              <i class="fa-solid fa-calendar-days sidebar-icon"></i>
              <span>Kegiatan</span>
              <i class="fa-solid fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu">
              @if ($can('Agenda') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.agenda') }}" class="submenu-link">Agenda</a></li>
              @endif
              @if ($can('Jadwal Kegiatan') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.jadwal') }}" class="submenu-link">Jadwal Kegiatan</a></li>
              @endif
              @if ($can('Jadwal Petugas Sholat') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.petugas') }}" class="submenu-link">Jadwal Petugas Sholat</a></li>
              @endif
              @if ($can('Galeri') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.galeri') }}" class="submenu-link">Galeri</a></li>
              @endif
            </ul>
          </li>
        @endif

        @if ($can('pengumuman'))
          <li class="sidebar-item">
            <a href="{{ route('pengumuman') }}" class="sidebar-link">
              <i class="fa-solid fa-bullhorn sidebar-icon"></i>
              <span>Pengumuman</span>
            </a>
          </li>
        @endif
      </ul>
    </div>

    <div class="sidebar-section">
      <span class="sidebar-section-label">PENGELOLAAN</span>
      <ul class="sidebar-menu">
        @if ($can('kepengurusan'))
          <li class="sidebar-item">
            <a href="{{ route('kepengurusan') }}" class="sidebar-link">
              <i class="fa-solid fa-users sidebar-icon"></i>
              <span>Kepengurusan</span>
            </a>
          </li>
        @endif
        @if ($can('relawan'))
          <li class="sidebar-item">
            <a href="{{ route('relawan') }}" class="sidebar-link">
              <i class="fa-solid fa-hand-holding-heart sidebar-icon"></i>
              <span>Relawan</span>
            </a>
          </li>
        @endif
        @if ($can('surat'))
          <li class="sidebar-item">
            <a href="{{ route('surat') }}" class="sidebar-link">
              <i class="fa-solid fa-envelope sidebar-icon"></i>
              <span>Surat</span>
            </a>
          </li>
        @endif
        @if ($can('inventaris'))
          <li class="sidebar-item">
            <a href="{{ route('inventaris') }}" class="sidebar-link">
              <i class="fa-solid fa-boxes-stacked sidebar-icon"></i>
              <span>Inventaris</span>
            </a>
          </li>
        @endif
      </ul>
    </div>

    @if ($can('laporan'))
      <div class="sidebar-section">
        <span class="sidebar-section-label">LAPORAN</span>
        <ul class="sidebar-menu">
          <li class="sidebar-item">
            <a href="{{ route('laporan') }}" class="sidebar-link">
              <i class="fa-solid fa-chart-column sidebar-icon"></i>
              <span>Laporan</span>
            </a>
          </li>
        </ul>
      </div>
    @endif

    @if ($fullAccess)
      <div class="sidebar-section">
        <span class="sidebar-section-label">PENGAWASAN</span>
        <ul class="sidebar-menu">
          <li class="sidebar-item">
            <a href="{{ route('activity-log') }}" class="sidebar-link">
              <i class="fa-solid fa-clipboard-list sidebar-icon"></i>
              <span>Log Aktivitas</span>
            </a>
          </li>
        </ul>
      </div>
    @endif

    @if ($can('pengaturan') || $can('Profile Masjid') || $can('Umum') || $fullAccess)
      <div class="sidebar-section">
        <span class="sidebar-section-label">PENGATURAN</span>
        <ul class="sidebar-menu">
          @if ($can('Profile Masjid') || $can('pengaturan'))
            <li class="sidebar-item">
              <a href="{{ route('pengaturan.profil') }}" class="sidebar-link">
                <i class="fa-solid fa-mosque sidebar-icon"></i>
                <span>Profil Masjid</span>
              </a>
            </li>
          @endif
          @if ($can('Umum') || $can('pengaturan'))
            <li class="sidebar-item">
              <a href="{{ route('pengaturan.umum') }}" class="sidebar-link">
                <i class="fa-solid fa-gear sidebar-icon"></i>
                <span>Umum</span>
              </a>
            </li>
          @endif
          @if ($fullAccess)
            <li class="sidebar-item">
              <a href="{{ route('pengaturan.user-management') }}" class="sidebar-link">
                <i class="fa-solid fa-user-shield sidebar-icon"></i>
                <span>Manajemen Pengguna</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
    @endif

    <div class="sidebar-section">
      <span class="sidebar-section-label">AKUN SAYA</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route('profil') }}" class="sidebar-link">
            <i class="fa-solid fa-circle-user sidebar-icon"></i>
            <span>Profil Saya</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-section sidebar-logout">
      <span class="sidebar-section-label">KELUAR APLIKASI</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="logout" class="sidebar-link" id="sidebarLogout">
            <i class="fa-solid fa-right-from-bracket sidebar-icon"></i>
            <span>Keluar</span>
          </a>
        </li>
      </ul>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
    </form>

    <div class="logout-modal-overlay" id="logoutModalOverlay">
      <div class="logout-modal-box">
        <div class="logout-modal-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
        <div class="logout-modal-title">Yakin mau keluar?</div>
        <div class="logout-modal-desc">Sesi anda akan berakhir dan harus login ulang untuk kembali ke MosqueHub.</div>
        <div class="logout-modal-actions">
          <button type="button" class="logout-modal-cancel" id="logoutCancelBtn">Batal</button>
          <button type="button" class="logout-modal-confirm" id="logoutConfirmBtn">
            <i class="fa-solid fa-right-from-bracket"></i> Ya, Keluar
          </button>
        </div>
      </div>
    </div>
    <div class="sidebar-section">
      <span class="sidebar-section-label">WEBSITE PUBLIK</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route('public.beranda') }}" class="sidebar-link" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square sidebar-icon"></i>
            <span>Lihat Website</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- HANYA SATU OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
</aside>
