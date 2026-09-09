<header class="topbar">
  @php $locale = App::getLocale(); @endphp
  <div class="topbar-left">
    <i class="fa-solid fa-shield-halved topbar-icon"></i>
    <span class="topbar-text"
      >{{ __('general.topbar_desc') }}</span
    >
  </div>
  <div class="topbar-right">
    <div class="topbar-langs">
      <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => 'id'])) }}"
         class="topbar-icon-btn lang-btn {{ $locale === 'id' ? 'active' : '' }}"
         title="Bahasa Indonesia" aria-label="Bahasa Indonesia">ID</a>
      <a href="{{ route(request()->route()->getName(), array_merge(request()->route()->parameters(), ['locale' => 'en'])) }}"
         class="topbar-icon-btn lang-btn {{ $locale === 'en' ? 'active' : '' }}"
         title="English" aria-label="English">EN</a>
    </div>
    <button class="topbar-icon-btn" id="darkModeToggle" title="{{ __('general.mode_gelap') }}">
      <i class="fa-regular fa-moon"></i>
    </button>
    <button class="topbar-icon-btn" id="logoutBtn" title="{{ __('menu.keluar') }}">
      <i class="fa-solid fa-power-off"></i>
    </button>
    <button class="topbar-icon-btn" id="topbarCollapseBtn" title="{{ __('general.sembunyikan') }}">
      <i class="fa-solid fa-chevron-up"></i>
    </button>
  </div>
</header>