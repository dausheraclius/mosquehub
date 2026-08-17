<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Lupa Password - MosqueHub</title>

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
            <h1>Lupa kata sandi?</h1>
            <p>Tenang, kami bantu pulihkan akses akun Anda.</p>
          </div>

          <div class="login-brand-features">
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> Data jemaah &amp; kepengurusan terpusat
            </div>
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> Kas, infaq &amp; sodaqoh tercatat rapi
            </div>
            <div class="login-brand-feature">
              <i class="fa-solid fa-check"></i> Agenda &amp; galeri kegiatan masjid
            </div>
          </div>
        </div>

        <div class="login-brand-footer">
          &copy; {{ date('Y') }} MosqueHub. Semua hak dilindungi.
        </div>
      </div>

      {{-- Panel kanan: form lupa password --}}
      <div class="login-form-panel">
        <div class="login-form-header">
          <h2>Reset Kata Sandi</h2>
          <p>Masukkan email akun Anda. Kami akan kirimkan link untuk membuat kata sandi baru.</p>
        </div>

        @if (session('status'))
          <div class="login-alert" style="background: var(--color-green-light); color: var(--color-green);">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
          </div>
        @endif

        @if ($errors->any())
          <div class="login-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="login-form">
          @csrf

          <div class="form-group">
            <label for="email" class="form-label">Email</label>
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

          <button type="submit" class="btn btn-primary login-submit-btn">
            <i class="fa-solid fa-paper-plane"></i> Kirim Link Reset
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
