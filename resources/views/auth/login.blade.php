<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Masuk - MosqueHub</title>

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
              <div class="login-brand-sub">Sistem Informasi Masjid</div>
            </div>
          </div>

          <div class="login-brand-mid">
            <h1>Kelola masjid lebih rapi &amp; transparan.</h1>
            <p>Satu sistem buat data jemaah, keuangan, kegiatan, sampai laporan masjid Anda.</p>
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

      {{-- Panel kanan: form login --}}
      <div class="login-form-panel">
        <div class="login-form-header">
          <h2>Selamat Datang</h2>
          <p>Masuk ke akun Anda untuk lanjut ke dashboard.</p>
        </div>

        @if ($errors->any())
          <div class="login-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="login-form">
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

          <div class="form-group">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="login-input-wrap">
              <i class="fa-solid fa-lock login-input-icon"></i>
              <input
                type="password"
                name="password"
                id="password"
                class="form-control @error('password') input-error @enderror"
                placeholder="Masukkan kata sandi"
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
              Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="login-forgot">Lupa password?</a>
          </div>

          <button type="submit" class="btn btn-primary login-submit-btn">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk
          </button>
        </form>

        <div class="login-footer-note">
          Butuh bantuan akses? Hubungi pengurus aplikasi masjid Anda.
        </div>

        <a href="{{ route('public.beranda') }}" class="login-public-link" target="_blank" rel="noopener">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat website masjid
        </a>
      </div>

    </div>
  </div>

  <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>