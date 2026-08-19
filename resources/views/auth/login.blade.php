<!DOCTYPE html>
<html lang="{{ session('locale', 'id') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('auth.masuk') }} - MosqueHub</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  @vite('resources/assets/css/variables.css')
  @vite('resources/assets/css/reset.css')
  @vite('resources/assets/css/components.css')
  @vite('resources/assets/css/login.css')
</head>
<body>

  <div class="login-page">
    <div class="login-shell">

      {{-- Panel kiri: identitas masjid --}}
      <div class="login-brand">
        <div>
          <div class="login-brand-top">
            <div class="login-brand-logo">
              @if ($siteMosque->getFirstMediaUrl('logo'))
                <img src="{{ $siteMosque->getFirstMediaUrl('logo') }}" alt="Logo {{ $siteMosque->name }}">
              @else
                <i class="fa-solid fa-mosque"></i>
              @endif
            </div>
            <div>
              <div class="login-brand-name">{{ $siteMosque->name }}</div>
              <div class="login-brand-sub">{{ __('general.sistem_informasi') }}</div>
            </div>
          </div>

          <div class="login-brand-mid">
            <h1>{{ __('auth.kelola_rapi') }}</h1>
            <p>{{ __('auth.satu_sistem') }}</p>
          </div>

          <div class="login-brand-features">
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> {{ __('auth.fitur_1') }}
            </div>
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> {{ __('auth.fitur_2') }}
            </div>
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> {{ __('auth.fitur_3') }}
            </div>
          </div>
        </div>

        <div class="login-brand-footer">
          &copy; {{ date('Y') }} MosqueHub. {{ __('auth.hak_cipta') }}
        </div>
      </div>

      {{-- Panel kanan: form login --}}
      <div class="login-form-panel">
        <div class="login-form-header">
          <h2>{{ __('auth.selamat_datang') }}</h2>
          <p>{{ __('auth.masuk_desc') }}</p>
        </div>

        {{-- LANGUAGE TOGGLE --}}
        <form method="POST" action="{{ route('set-locale') }}" style="display:inline; margin-bottom: 16px;">
          @csrf
          <div style="display:flex; gap:4px; justify-content:flex-end;">
            <button type="submit" name="locale" value="id"
              class="btn btn-sm {{ session('locale', 'id') === 'id' ? 'btn-primary' : 'btn-outline' }}"
              style="font-size:11px; padding:4px 10px; border-radius:6px; min-width:auto;">
              ID
            </button>
            <button type="submit" name="locale" value="en"
              class="btn btn-sm {{ session('locale', 'id') === 'en' ? 'btn-primary' : 'btn-outline' }}"
              style="font-size:11px; padding:4px 10px; border-radius:6px; min-width:auto;">
              EN
            </button>
          </div>
        </form>

        @if ($errors->any())
          <div class="login-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="login-form">
          @csrf

          <div class="form-group">
            <label for="email" class="form-label">{{ __('general.email') }}</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-envelope login-input-icon"></i>
              <input
                type="email"
                name="email"
                id="email"
                class="form-control @error('email') input-error @enderror"
                placeholder="nama@email.com"
                value="{{ old('email') }}"
                required
                autofocus
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">{{ __('auth.kata_sandi') }}</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password"
                id="password"
                class="form-control @error('password') input-error @enderror"
                placeholder="{{ __('auth.masukkan_sandi') }}"
                required
              >
              <button type="button" class="login-toggle-password" id="togglePassword" tabindex="-1">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <div class="login-row-between">
            <label class="login-remember">
              <input type="checkbox" name="remember">
              {{ __('auth.ingat_saya') }}
            </label>
            <a href="{{ route('password.request') }}" class="login-forgot">{{ __('auth.lupa_password') }}</a>
          </div>

          <button type="submit" class="btn btn-primary login-submit-btn">
            <i class="fa-solid fa-right-to-bracket"></i> {{ __('auth.masuk') }}
          </button>
        </form>

        <div class="login-footer-note">
          {{ __('auth.butuh_bantuan') }}
        </div>

        <a href="{{ route('public.beranda') }}" class="login-public-link" target="_blank" rel="noopener">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('auth.lihat_website') }}
        </a>
      </div>

    </div>
  </div>

  <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
