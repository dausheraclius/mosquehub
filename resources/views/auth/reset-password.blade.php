<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reset Kata Sandi - MosqueHub</title>

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
              <div class="login-brand-sub">Sistem Informasi Masjid</div>
            </div>
          </div>

          <div class="login-brand-mid">
            <h1>Buat kata sandi baru</h1>
            <p>Isi kata sandi baru untuk akun Anda.</p>
          </div>
        </div>

        <div class="login-brand-footer">
          &copy; {{ date('Y') }} MosqueHub. Semua hak dilindungi.
        </div>
      </div>

      {{-- Panel kanan: form reset --}}
      <div class="login-form-panel">
        <div class="login-form-header">
          <h2>Kata Sandi Baru</h2>
          <p>Minimal 8 karakter. Pastikan tidak sama dengan kata sandi lama Anda.</p>
        </div>

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
            <label for="email" class="form-label">Email</label>
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
            <label for="password" class="form-label">Kata Sandi Baru</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password"
                id="password"
                class="form-control @error('password') input-error @enderror"
                placeholder="Minimal 8 karakter"
                required
                autofocus
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirmation" class="form-label">Ulangi Kata Sandi</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-control"
                placeholder="Ketik ulang kata sandi"
                required
              >
            </div>
          </div>

          <button type="submit" class="btn btn-primary login-submit-btn">
            <i class="fa-solid fa-check"></i> Simpan Kata Sandi
          </button>
        </form>

        <div class="login-footer-note">
          <a href="{{ route('login') }}" class="login-public-link" style="display: inline-flex;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke halaman masuk
          </a>
        </div>
      </div>

    </div>
  </div>

</body>
</html>
