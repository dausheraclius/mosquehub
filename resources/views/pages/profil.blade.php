@extends('layouts.app')

@section('title', 'MosqueHub - Profil Saya')

@push('styles-before-components')
  @vite('resources/assets/css/umum.css')
@endpush

@section('content')

  <x-page-header crumb="Akun" active="Profil Saya" title="Profil Saya" subtitle="{{ auth()->user()->role ?? 'Ketua YMBPK' }} · {{ now()->translatedFormat('d F Y') }}" />

  @if (session('status'))
    <div class="login-alert" style="background: var(--color-green-light); color: var(--color-green); margin-bottom: 16px;">
      <i class="fa-solid fa-circle-check"></i>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <div class="card settings-card">
    <div class="settings-layout">
      <!-- MINI NAV KIRI -->
      <nav class="settings-nav" id="settingsNav">
        <button class="settings-nav-item active" data-target="panelData">
          <i class="fa-solid fa-user"></i>
          <span>Data Akun</span>
        </button>
        <button class="settings-nav-item" data-target="panelKeamanan">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Keamanan</span>
        </button>
      </nav>

      <!-- PANEL KANAN -->
      <div class="settings-content">
        <!-- PANEL: DATA AKUN -->
        <section class="settings-panel active" id="panelData">
          <h2 class="settings-panel-title">Data Akun</h2>
          <p class="settings-panel-desc">Nama, email, dan nomor HP yang dipakai untuk login dan identitas di sistem.</p>

          <div class="form-row">
            <div class="form-field">
              <label for="profilName">Nama Lengkap</label>
              <input type="text" id="profilName" value="{{ $user->name }}" />
            </div>
            <div class="form-field">
              <label for="profilPhone">No. HP</label>
              <input type="text" id="profilPhone" value="{{ $user->phone ?: '' }}" placeholder="cth: 0812xxxxxxx" />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="profilEmail">Email</label>
              <input type="email" id="profilEmail" value="{{ $user->email }}" />
            </div>
          </div>

          <button class="btn btn-primary" id="btnSaveProfil">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </section>

        <!-- PANEL: KEAMANAN -->
        <section class="settings-panel" id="panelKeamanan">
          <h2 class="settings-panel-title">Verifikasi Email</h2>
          <p class="settings-panel-desc">Pastikan email Anda terverifikasi supaya bisa menerima link reset kata sandi.</p>

          <div class="toggle-row" style="margin-bottom: 24px">
            <div>
              <div class="toggle-title">Status Email</div>
              <div class="toggle-sub">
                @if ($emailVerified)
                  Email <strong>{{ $user->email }}</strong> sudah terverifikasi.
                @else
                  Email <strong>{{ $user->email }}</strong> belum terverifikasi.
                @endif
              </div>
            </div>
            @if ($emailVerified)
              <span class="status-badge status-aktif"><i class="fa-solid fa-circle-check"></i> Terverifikasi</span>
            @else
              <button class="btn btn-outline" id="btnResendVerification">
                <i class="fa-solid fa-envelope-circle-check"></i> Kirim Ulang Email Verifikasi
              </button>
            @endif
          </div>

          <h2 class="settings-panel-title" style="margin-top: 8px">Ganti Kata Sandi</h2>
          <p class="settings-panel-desc">Gunakan kombinasi huruf, angka, dan simbol supaya akun lebih aman.</p>

          <div class="form-row single">
            <div class="form-field">
              <label for="profilOldPassword">Password Lama</label>
              <input type="password" id="profilOldPassword" placeholder="Masukkan password lama" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="profilNewPassword">Password Baru</label>
              <input type="password" id="profilNewPassword" placeholder="Minimal 8 karakter" />
            </div>
            <div class="form-field">
              <label for="profilConfirmPassword">Konfirmasi Password Baru</label>
              <input type="password" id="profilConfirmPassword" placeholder="Ulangi password baru" />
            </div>
          </div>
          <button class="btn btn-outline" id="btnChangePassword">
            <i class="fa-solid fa-key"></i> Ganti Password
          </button>
        </section>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profil.js') }}"></script>
@endpush
