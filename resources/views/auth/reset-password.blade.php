<!DOCTYPE html>
<html lang="{{ session('locale', 'id') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ __('auth.reset_password_title') }} - MosqueHub</title>

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
              <img src="{{ asset('assets/img/logomasjid.png') }}" alt="Logo Masjid">
            </div>
            <div>
              <div class="login-brand-name">{{ $siteMosque->name }}</div>
              <div class="login-brand-sub">{{ __('general.sistem_informasi') }}</div>
            </div>
          </div>

          <div class="login-brand-mid">
            <h1>{{ __('auth.password_baru') }}</h1>
            <p>{{ __('auth.reset_password_desc') }}</p>
          </div>
        </div>

        <div class="login-brand-footer">
          &copy; {{ date('Y') }} MosqueHub. {{ __('auth.hak_cipta') }}
        </div>
      </div>

      {{-- Panel kanan: form reset --}}
      <div class="login-form-panel">
        <div class="login-form-header">
          <h2>{{ __('auth.password_baru') }}</h2>
          <p>{{ __('auth.minimal_8_karakter') }}. {{ __('auth.reset_password_desc') }}</p>
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

        <form method="POST" action="{{ route('password.update') }}" class="login-form">
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">

          <div class="form-group">
            <label for="email" class="form-label">{{ __('general.email') }}</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-envelope login-input-icon"></i>
              <input
                type="email"
                id="email"
                class="form-control"
                value="{{ $email }}"
                disabled
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="form-label">{{ __('auth.password_baru') }}</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password"
                id="password"
                class="form-control @error('password') input-error @enderror"
                placeholder="{{ __('auth.minimal_8_karakter') }}"
                required
                autofocus
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('auth.konfirmasi_password') }}</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-control"
                placeholder="{{ __('auth.ulangi_sandi_baru') }}"
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-primary login-submit-btn">
            <i class="fa-solid fa-check"></i> {{ __('auth.reset_password') }}
          </button>
        </form>

        <div class="login-footer-note">
          <a href="{{ route('login') }}" class="login-public-link" style="display: inline-flex;">
            <i class="fa-solid fa-arrow-left"></i> {{ __('auth.kembali_ke_login') }}
          </a>
        </div>
      </div>

    </div>
  </div>

</body>
</html>
