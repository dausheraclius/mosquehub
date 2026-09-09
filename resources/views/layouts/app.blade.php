<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'MosqueHub')</title>

  <script>
    window.__T = @json(__('messages'));
    window.__LOCALE = '{{ App::getLocale() }}';
    window.__ADMIN_BASE = '/{{ App::getLocale() }}';
  </script>

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
  @vite('resources/assets/js/include.js')
  @vite('resources/assets/js/header.js')
  @vite('resources/assets/js/sidebar.js')
  @stack('styles-final')
</head>
<body>

@if (session('impersonator_id'))
  <div style="background:#1a1a2e;color:#fff;padding:8px 20px;text-align:center;font-size:13px;">
    Lo lagi login sebagai <strong>{{ auth()->user()->name }}</strong>.
    <form action="{{ route('stop-impersonate', ['locale' => App::getLocale()]) }}" method="POST" style="display:inline;">
      @csrf
      <button type="submit" style="background:none;border:none;color:#7dd3c0;text-decoration:underline;cursor:pointer;font-size:13px;">Kembali ke akun Super Admin</button>
    </form>
  </div>
@endif

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
      <h2 class="confirm-dialog-title" id="confirmDeleteTitle">{{ __('messages.yakin_hapus') }}</h2>
      <p class="confirm-dialog-desc" id="confirmDeleteMessage">{{ __('messages.yakin_hapus_desc') }}</p>
      <div class="confirm-dialog-actions">
        <button class="confirm-dialog-btn confirm-dialog-cancel" id="confirmDeleteCancelBtn" type="button">{{ __('general.batal') }}</button>
        <button class="confirm-dialog-btn confirm-dialog-danger" id="confirmDeleteConfirmBtn" type="button">
          <i class="fa-solid fa-trash"></i> {{ __('general.hapus') }}
        </button>
      </div>
    </div>
  </div>

  <!-- GLOBAL TOAST NOTIFICATION -->
  <div class="toast" id="appToast"></div>

  @yield('modals')

  <script src="{{ asset('assets/js/app.js') }}"></script>
  @stack('scripts')
  @stack('scripts-late')

  {{-- Safety net: pastikan halaman visible meski Vite module JS gagal load --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(function () {
        document.body.classList.add('components-ready');
      }, 3000);
    });
  </script>
</body>
</html>
