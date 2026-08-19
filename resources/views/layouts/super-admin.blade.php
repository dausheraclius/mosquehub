<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Super Admin - MosqueHub')</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  @vite([
    'resources/assets/css/variables.css',
    'resources/assets/css/reset.css',
    'resources/assets/css/style.css',
    'resources/assets/css/components.css',
    'resources/assets/css/dashboard.css',
    'resources/assets/css/data-jamaah.css',
    'resources/assets/css/responsive.css',
  ])
</head>
<body>
  <header class="topbar" style="background: #1a1a2e;">
    <div class="topbar-left">
      <i class="fa-solid fa-user-shield topbar-icon"></i>
      <span class="topbar-text">Super Admin Panel — MosqueHub</span>
    </div>
    <div class="topbar-right">
      <a href="{{ route('dashboard') }}" class="btn-daftar" style="text-decoration:none;">Ke Dashboard Masjid</a>
      <form action="{{ route('logout', ['locale' => App::getLocale()]) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="topbar-icon-btn" title="Keluar"><i class="fa-solid fa-power-off"></i></button>
      </form>
    </div>
  </header>

  <div style="display:flex;">
    <aside class="sidebar" style="position: static;">
      <nav class="sidebar-nav">
        <div class="sidebar-section">
          <span class="sidebar-section-label">SUPER ADMIN</span>
          <ul class="sidebar-menu">
            <li class="sidebar-item">
              <a href="{{ route('super.dashboard') }}" class="sidebar-link {{ request()->routeIs('super.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge sidebar-icon"></i><span>Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('super.mosques') }}" class="sidebar-link {{ request()->routeIs('super.mosques*') ? 'active' : '' }}">
                <i class="fa-solid fa-mosque sidebar-icon"></i><span>Kelola Masjid</span>
              </a>
            </li>
            <li class="sidebar-item">
              <a href="{{ route('super.users') }}" class="sidebar-link {{ request()->routeIs('super.users') ? 'active' : '' }}">
                <i class="fa-solid fa-users sidebar-icon"></i><span>Semua User</span>
              </a>
            </li>
          </ul>
        </div>
      </nav>
    </aside>

    <main class="main-content" style="flex:1;">
      @if (session('success'))
        <div class="status-badge status-aktif" style="display:inline-flex;margin-bottom:16px;">{{ session('success') }}</div>
      @endif
      @yield('content')
    </main>
  </div>
</body>
</html>
