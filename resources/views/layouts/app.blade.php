<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'MosqueHub')</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  @vite('resources/assets/css/variables.css')
  @vite('resources/assets/css/reset.css')
  @vite('resources/assets/css/style.css')
  @vite('resources/assets/css/topbar.css')
  @vite('resources/assets/css/header.css')
  @vite('resources/assets/css/sidebar.css')
  @vite('resources/assets/css/content-header.css')
  @stack('styles-before-components')
  @vite('resources/assets/css/components.css')
  @stack('styles-after-components')
  @stack('styles-late')
  @vite('resources/assets/css/responsive.css')
  @stack('styles-final')
</head>
<body>

  <div id="topbar-placeholder">
    @include('layouts.partials.topbar')
  </div>
  <div id="header-placeholder">
    @include('layouts.partials.header')
  </div>

  <div class="app-wrapper">
    <div id="sidebar-placeholder">
      @include('layouts.partials.sidebar')
    </div>

    <main class="main-content" id="main-content">
      @yield('content')
    </main>
  </div>

  <!-- GLOBAL CONFIRM DELETE DIALOG -->
  <div class="confirm-overlay" id="confirmDeleteOverlay">
    <div class="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirmDeleteTitle">
      <button class="confirm-dialog-close" id="confirmDeleteCloseBtn" type="button" aria-label="Tutup">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="confirm-dialog-icon"><i class="fa-solid fa-trash"></i></div>
      <h2 class="confirm-dialog-title" id="confirmDeleteTitle">Hapus Data?</h2>
      <p class="confirm-dialog-desc" id="confirmDeleteMessage">Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.</p>
      <div class="confirm-dialog-actions">
        <button class="confirm-dialog-btn confirm-dialog-cancel" id="confirmDeleteCancelBtn" type="button">Batal</button>
        <button class="confirm-dialog-btn confirm-dialog-danger" id="confirmDeleteConfirmBtn" type="button">
          <i class="fa-solid fa-trash"></i> Hapus
        </button>
      </div>
    </div>
  </div>

  <!-- GLOBAL TOAST NOTIFICATION -->
  <div class="toast" id="appToast"></div>

  @yield('modals')

  <script src="{{ asset('assets/js/include.js') }}"></script>
  <script src="{{ asset('assets/js/header.js') }}"></script>
  <script src="{{ asset('assets/js/sidebar.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  @stack('scripts')
  @stack('scripts-late')
</body>
</html>