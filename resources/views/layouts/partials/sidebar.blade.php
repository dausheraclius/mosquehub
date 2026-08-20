<!-- SIDEBAR -->
@php
  $u = auth()->user();
  $fullAccess = $u->role === 'Ketua YMBPK';
  $menuKeys = $fullAccess ? ['*'] : (array) ($u->permissions ?? []);
  $can = fn ($key) => in_array('*', $menuKeys, true) || in_array($key, $menuKeys, true);
  $locale = App::getLocale();
  $isRoute = fn ($pattern) => request()->routeIs($pattern);
@endphp
<aside class="sidebar" id="sidebar">
  <nav class="sidebar-nav">
    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('menu.menu_admin') }}</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item {{ $isRoute('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard', ['locale' => $locale]) }}" class="sidebar-link">
            <i class="fa-solid fa-house sidebar-icon"></i>
            <span>{{ __('menu.beranda') }}</span>
          </a>
        </li>

        @if ($can('data-jamaah'))
          <li class="sidebar-item {{ $isRoute('jamaah.index') ? 'active' : '' }}">
            <a href="{{ route('jamaah.index', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-users sidebar-icon"></i>
              <span>{{ __('menu.data_jamaah') }}</span>
            </a>
          </li>
        @endif

        @if ($can('keuangan') || $can('Kas Masjid') || $can('Infaq / Sodaqoh (Zakat)'))
          <li class="sidebar-item has-submenu {{ $isRoute('keuangan.*') ? 'active open' : '' }}">
            <a href="#" class="sidebar-link submenu-toggle">
              <i class="fa-solid fa-wallet sidebar-icon"></i>
              <span>{{ __('menu.keuangan') }}</span>
              <i class="fa-solid fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu">
              @if ($can('Infaq / Sodaqoh (Zakat)') || $can('keuangan'))
                <li><a href="{{ route('keuangan.infaq', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('keuangan.infaq') ? 'active' : '' }}">{{ __('menu.ziswaf') }}</a></li>
              @endif
              @if ($can('Kas Masjid') || $can('keuangan'))
                <li><a href="{{ route('keuangan.kas', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('keuangan.kas') ? 'active' : '' }}">{{ __('menu.kas_masjid') }}</a></li>
              @endif
            </ul>
          </li>
        @endif

        @if ($can('kegiatan') || $can('Agenda') || $can('Jadwal Kegiatan') || $can('Galeri') || $can('Jadwal Petugas Sholat'))
          <li class="sidebar-item has-submenu {{ $isRoute('kegiatan.*') ? 'active open' : '' }}">
            <a href="#" class="sidebar-link submenu-toggle">
              <i class="fa-solid fa-calendar-days sidebar-icon"></i>
              <span>{{ __('menu.kegiatan') }}</span>
              <i class="fa-solid fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="submenu">
              @if ($can('Agenda') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.agenda', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('kegiatan.agenda') ? 'active' : '' }}">{{ __('menu.agenda') }}</a></li>
              @endif
              @if ($can('Jadwal Kegiatan') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.jadwal', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('kegiatan.jadwal') ? 'active' : '' }}">{{ __('menu.jadwal_kegiatan') }}</a></li>
              @endif
              @if ($can('Jadwal Petugas Sholat') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.petugas', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('kegiatan.petugas') ? 'active' : '' }}">{{ __('menu.jadwal_petugas_sholat') }}</a></li>
              @endif
              @if ($can('Galeri') || $can('kegiatan'))
                <li><a href="{{ route('kegiatan.galeri', ['locale' => $locale]) }}" class="submenu-link {{ $isRoute('kegiatan.galeri') ? 'active' : '' }}">{{ __('menu.galeri') }}</a></li>
              @endif
            </ul>
          </li>
        @endif

        @if ($can('pengumuman'))
          <li class="sidebar-item {{ $isRoute('pengumuman') ? 'active' : '' }}">
            <a href="{{ route('pengumuman', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-bullhorn sidebar-icon"></i>
              <span>{{ __('menu.pengumuman') }}</span>
            </a>
          </li>
        @endif
      </ul>
    </div>

    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('menu.pengelolaan') }}</span>
      <ul class="sidebar-menu">
        @if ($can('kepengurusan'))
          <li class="sidebar-item {{ $isRoute('kepengurusan') ? 'active' : '' }}">
            <a href="{{ route('kepengurusan', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-users sidebar-icon"></i>
              <span>{{ __('menu.kepengurusan') }}</span>
            </a>
          </li>
        @endif
        @if ($can('relawan'))
          <li class="sidebar-item {{ $isRoute('relawan') ? 'active' : '' }}">
            <a href="{{ route('relawan', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-hand-holding-heart sidebar-icon"></i>
              <span>{{ __('menu.relawan') }}</span>
            </a>
          </li>
        @endif
        @if ($can('surat'))
          <li class="sidebar-item {{ $isRoute('surat') ? 'active' : '' }}">
            <a href="{{ route('surat', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-envelope sidebar-icon"></i>
              <span>{{ __('menu.surat') }}</span>
            </a>
          </li>
        @endif
        @if ($can('inventaris'))
          <li class="sidebar-item {{ $isRoute('inventaris') ? 'active' : '' }}">
            <a href="{{ route('inventaris', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-boxes-stacked sidebar-icon"></i>
              <span>{{ __('menu.inventaris') }}</span>
            </a>
          </li>
        @endif
      </ul>
    </div>

    @if ($can('laporan'))
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('menu.laporan') }}</span>
        <ul class="sidebar-menu">
          <li class="sidebar-item {{ $isRoute('laporan') || $isRoute('laporan.*') ? 'active' : '' }}">
            <a href="{{ route('laporan', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-chart-column sidebar-icon"></i>
              <span>{{ __('menu.laporan_page') }}</span>
            </a>
          </li>
        </ul>
      </div>
    @endif

    @if ($fullAccess)
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('menu.pengawasan') }}</span>
        <ul class="sidebar-menu">
          <li class="sidebar-item {{ $isRoute('activity-log') ? 'active' : '' }}">
            <a href="{{ route('activity-log', ['locale' => $locale]) }}" class="sidebar-link">
              <i class="fa-solid fa-clipboard-list sidebar-icon"></i>
              <span>{{ __('menu.log_aktivitas') }}</span>
            </a>
          </li>
        </ul>
      </div>
    @endif

    @if ($can('pengaturan') || $can('Profile Masjid') || $can('Umum') || $fullAccess)
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('menu.pengaturan') }}</span>
        <ul class="sidebar-menu">
          @if ($can('Profile Masjid') || $can('pengaturan'))
            <li class="sidebar-item {{ $isRoute('pengaturan.profil') ? 'active' : '' }}">
              <a href="{{ route('pengaturan.profil', ['locale' => $locale]) }}" class="sidebar-link">
                <i class="fa-solid fa-mosque sidebar-icon"></i>
                <span>{{ __('menu.profil_masjid') }}</span>
              </a>
            </li>
          @endif
          @if ($can('Umum') || $can('pengaturan'))
            <li class="sidebar-item {{ $isRoute('pengaturan.umum') ? 'active' : '' }}">
              <a href="{{ route('pengaturan.umum', ['locale' => $locale]) }}" class="sidebar-link">
                <i class="fa-solid fa-gear sidebar-icon"></i>
                <span>{{ __('menu.umum') }}</span>
              </a>
            </li>
          @endif
          @if ($fullAccess)
            <li class="sidebar-item {{ $isRoute('pengaturan.user-management') ? 'active' : '' }}">
              <a href="{{ route('pengaturan.user-management', ['locale' => $locale]) }}" class="sidebar-link">
                <i class="fa-solid fa-user-shield sidebar-icon"></i>
                <span>{{ __('menu.manajemen_pengguna') }}</span>
              </a>
            </li>
          @endif
        </ul>
      </div>
    @endif

    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('menu.akun_saya') }}</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item {{ $isRoute('profil') ? 'active' : '' }}">
          <a href="{{ route('profil', ['locale' => $locale]) }}" class="sidebar-link">
            <i class="fa-solid fa-circle-user sidebar-icon"></i>
            <span>{{ __('menu.profil_saya') }}</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- LANGUAGE SWITCHER -->
    <div class="sidebar-section">
      <span class="sidebar-section-label">BAHASA / LANGUAGE</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => 'id'])) }}"
             class="sidebar-link {{ $locale === 'id' ? 'active' : '' }}">
            <i class="fa-solid fa-globe sidebar-icon"></i>
            <span>Bahasa Indonesia</span>
            @if ($locale === 'id')
              <i class="fa-solid fa-check" style="margin-left:auto; color:var(--color-green); font-size:11px;"></i>
            @endif
          </a>
        </li>
        <li class="sidebar-item">
          <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => 'en'])) }}"
             class="sidebar-link {{ $locale === 'en' ? 'active' : '' }}">
            <i class="fa-solid fa-globe sidebar-icon"></i>
            <span>English</span>
            @if ($locale === 'en')
              <i class="fa-solid fa-check" style="margin-left:auto; color:var(--color-green); font-size:11px;"></i>
            @endif
          </a>
        </li>
      </ul>
    </div>

    <div class="sidebar-section sidebar-logout">
      <span class="sidebar-section-label">{{ __('menu.keluar_aplikasi') }}</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route('logout.locale', ['locale' => $locale]) }}" class="sidebar-link" id="sidebarLogout">
            <i class="fa-solid fa-right-from-bracket sidebar-icon"></i>
            <span>{{ __('menu.keluar') }}</span>
          </a>
        </li>
      </ul>
    </div>

    <form id="logoutForm" action="{{ route('logout.locale', ['locale' => $locale]) }}" method="POST" style="display: none;">
    @csrf
    </form>

    <div class="logout-modal-overlay" id="logoutModalOverlay">
      <div class="logout-modal-box">
        <div class="logout-modal-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
        <div class="logout-modal-title">{{ __('messages.yakin_keluar') }}</div>
        <div class="logout-modal-desc">{{ __('messages.yakin_keluar_desc') }}</div>
        <div class="logout-modal-actions">
          <button type="button" class="logout-modal-cancel" id="logoutCancelBtn">{{ __('general.batal') }}</button>
          <button type="button" class="logout-modal-confirm" id="logoutConfirmBtn">
            <i class="fa-solid fa-right-from-bracket"></i> {{ __('general.ya_keluar') }}
          </button>
        </div>
      </div>
    </div>
    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('menu.website_publik') }}</span>
      <ul class="sidebar-menu">
        <li class="sidebar-item">
          <a href="{{ route('public.beranda') }}" class="sidebar-link" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square sidebar-icon"></i>
            <span>{{ __('menu.lihat_website') }}</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- HANYA SATU OVERLAY -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
</aside>
