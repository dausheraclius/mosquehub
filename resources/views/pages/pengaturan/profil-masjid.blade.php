@extends('layouts.app')

@section('title', 'MosqueHub - Profil Masjid')

@push('styles-before-components')
  @vite('resources/assets/css/profil-masjid.css')
@endpush

@section('content')

  <x-page-header crumb="{{ __('menu.pengaturan') }}" active="{{ __('menu.profil_masjid') }}" title="{{ __('pages.profil_masjid_title') }}" subtitle="{{ auth()->user()->role ?? 'Ketua YMBPK' }} · {{ now()->translatedFormat('d F Y') }}" />

  <!-- BANNER PROFIL -->
  <div class="card profile-banner">
    <div class="profile-banner-logo" id="bannerLogo">
      @if ($mosque->getFirstMediaUrl('logo'))
        <img src="{{ $mosque->getFirstMediaUrl('logo') }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;border-radius:10px;">
      @else
        <i class="fa-solid fa-mosque"></i>
      @endif
    </div>
    <div class="profile-banner-info">
      <div class="profile-banner-name">
        {{ $mosque->name }}
        <span class="status-pill">{{ ucfirst($mosque->status) }}</span>
      </div>
      <div class="profile-banner-line">{{ $mosque->short_name ?: '-' }}</div>
      <div class="profile-banner-line">{{ $mosque->address ?: 'Alamat belum diisi' }}</div>
      <div class="profile-banner-contact">
        <span><i class="fa-solid fa-phone"></i> {{ $mosque->phone ?: '-' }}</span>
        <span><i class="fa-solid fa-envelope"></i> {{ $mosque->email ?: '-' }}</span>
        <span><i class="fa-solid fa-globe"></i> {{ $mosque->website ?: '-' }}</span>
      </div>
    </div>
    <div class="profile-banner-actions">
      <button type="button" class="btn btn-outline" id="btnEditProfile"><i class="fa-solid fa-pen"></i> {{ __('general.edit') }} {{ __('menu.profil_masjid') }}</button>
    </div>
  </div>

  <form id="profilForm" onsubmit="return false" enctype="multipart/form-data">
    <div class="profile-grid">
      <!-- KOLOM KIRI -->
      <div class="profile-col">
        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.informasi_dasar') }}</h2></div>
          <div class="form-row">
            <div class="form-field">
              <label for="mosqueName">Nama Masjid</label>
              <input type="text" id="mosqueName" name="name" value="{{ $mosque->name }}" disabled />
            </div>
            <div class="form-field">
              <label for="shortName">Nama Singkat</label>
              <input type="text" id="shortName" name="short_name" value="{{ $mosque->short_name }}" disabled />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="establishedYear">Tahun Berdiri</label>
              <input type="text" id="establishedYear" name="established_year" value="{{ $mosque->established_year }}" disabled />
            </div>
            <div class="form-field">
              <label for="mosqueCategory">Kategori Masjid</label>
              <select id="mosqueCategory" name="category" disabled>
                <option {{ $mosque->category === "Masjid Jami'" ? 'selected' : '' }}>Masjid Jami'</option>
                <option {{ $mosque->category === 'Masjid Raya' ? 'selected' : '' }}>Masjid Raya</option>
                <option {{ $mosque->category === 'Musholla' ? 'selected' : '' }}>Musholla</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="phoneNumber">Nomor Telepon</label>
              <input type="text" id="phoneNumber" name="phone" value="{{ $mosque->phone }}" disabled />
            </div>
            <div class="form-field">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="{{ $mosque->email }}" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" value="{{ $mosque->website }}" disabled />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.lokasi') }}</h2></div>
          <div class="form-row">
            <div class="form-field">
              <label for="province">Provinsi</label>
              <input type="text" id="province" name="province" value="{{ $mosque->province }}" disabled />
            </div>
            <div class="form-field">
              <label for="city">Kota</label>
              <input type="text" id="city" name="city" value="{{ $mosque->city }}" disabled />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="district">Kecamatan</label>
              <input type="text" id="district" name="district" value="{{ $mosque->district }}" disabled />
            </div>
            <div class="form-field">
              <label for="kelurahan">Kelurahan</label>
              <input type="text" id="kelurahan" name="kelurahan" value="{{ $mosque->kelurahan }}" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="postalCode">Kode Pos</label>
              <input type="text" id="postalCode" name="postal_code" value="{{ $mosque->postal_code }}" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="completeAddress">Alamat Lengkap</label>
              <input type="text" id="completeAddress" name="address" value="{{ $mosque->address }}" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="mapsLink">Link Google Maps</label>
              <input type="text" id="mapsLink" name="maps_link" value="{{ $mosque->maps_link }}" disabled />
            </div>
          </div>
          @if ($mosque->maps_link)
            <a class="map-placeholder map-link" href="{{ $mosque->maps_link }}" target="_blank" rel="noopener noreferrer">
              <i class="fa-solid fa-location-dot"></i>
              <span>Buka lokasi di Google Maps</span>
            </a>
          @else
            <div class="map-placeholder"><i class="fa-solid fa-location-dot"></i><span>Link Google Maps belum diisi</span></div>
          @endif
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.media_sosial') }}</h2></div>
          <div class="social-row">
            <div class="social-icon ig">
              <svg width="14" height="16" viewBox="0 0 448 512" fill="none" stroke="currentColor" stroke-width="44" stroke-linecap="round" aria-hidden="true">
                <rect x="56" y="56" width="336" height="400" rx="96" />
                <circle cx="224" cy="256" r="76" />
                <circle cx="324" cy="156" r="14" fill="currentColor" stroke="none" />
              </svg>
            </div>
            <input type="text" id="igLink" name="instagram" value="{{ $mosque->instagram }}" placeholder="Instagram/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon fb"><i class="fa-brands fa-facebook-f"></i></div>
            <input type="text" id="fbLink" name="facebook" value="{{ $mosque->facebook }}" placeholder="Facebook/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon yt"><i class="fa-brands fa-youtube"></i></div>
            <input type="text" id="ytLink" name="youtube" value="{{ $mosque->youtube }}" placeholder="YouTube/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon tt"><i class="fa-brands fa-tiktok"></i></div>
            <input type="text" id="ttLink" name="tiktok" value="{{ $mosque->tiktok }}" placeholder="TikTok/Tautan" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon wa"><i class="fa-brands fa-whatsapp"></i></div>
            <input type="text" id="waLink" name="whatsapp" value="{{ $mosque->whatsapp }}" placeholder="WhatsApp/" disabled />
          </div>
        </div>
      </div>

      <!-- KOLOM KANAN -->
      <div class="profile-col">
        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.logo_masjid') }}</h2></div>
          <button
            type="button"
            class="btn btn-outline"
            id="logoUploadBtn"
            style="width: 100%; justify-content: center"
            onclick="document.getElementById('logoInput').click()"
          >
            <i class="fa-solid fa-upload"></i> {{ __('general.unggah_logo') }}
          </button>
          <input type="file" id="logoInput" name="logo" accept="image/*" hidden />
          <div class="logo-upload-box" id="logoBox">
            @if ($mosque->getFirstMediaUrl('logo'))
              <img src="{{ $mosque->getFirstMediaUrl('logo') }}" alt="Logo Masjid">
            @else
              <i class="fa-solid fa-mosque"></i>
            @endif
          </div>
          @if ($mosque->getFirstMediaUrl('logo'))
            <button type="button" class="btn btn-text" id="btnHapusLogo" style="margin-top: 8px; width: 100%; justify-content: center; color: var(--color-danger, #dc2626);">
              <i class="fa-solid fa-trash"></i> Hapus Logo
            </button>
          @endif
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.identitas_resmi') }}</h2></div>
          <div class="identity-grid">
            <div class="identity-box" data-upload>
              @if ($mosque->getFirstMediaUrl('stempel'))
                <img src="{{ $mosque->getFirstMediaUrl('stempel') }}" style="width:100%;height:100%;object-fit:contain">
              @else
                Stempel Resmi
              @endif
              <input type="file" name="stempel" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
            <div class="identity-box" data-upload>
              @if ($mosque->getFirstMediaUrl('kop_surat'))
                <img src="{{ $mosque->getFirstMediaUrl('kop_surat') }}" style="width:100%;height:100%;object-fit:contain">
              @else
                Pratinjau Kepala Surat
              @endif
              <input type="file" name="kop_surat" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
            <div class="identity-box" data-upload>
              @if ($mosque->getFirstMediaUrl('ttd'))
                <img src="{{ $mosque->getFirstMediaUrl('ttd') }}" style="width:100%;height:100%;object-fit:contain">
              @else
                Tempat Tanda Tangan
              @endif
              <input type="file" name="ttd" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">{{ __('general.informasi_pengurus') }}</h2></div>
          <div class="mgmt-field">
            <label>Ketua YMBPK</label>
            <div class="mgmt-select-wrap highlight">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <span style="padding: 8px 4px; font-size: 13px;">{{ $pengurus['ketua'] ?? 'Belum ditentukan' }}</span>
            </div>
          </div>
          <div class="mgmt-field">
            <label>Sekretaris</label>
            <div class="mgmt-select-wrap">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <span style="padding: 8px 4px; font-size: 13px;">{{ $pengurus['sekretaris'] ?? 'Belum ditentukan' }}</span>
            </div>
          </div>
          <div class="mgmt-field">
            <label>Bendahara</label>
            <div class="mgmt-select-wrap">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <span style="padding: 8px 4px; font-size: 13px;">{{ $pengurus['bendahara'] ?? 'Belum ditentukan' }}</span>
            </div>
          </div>
          <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 8px;">
            Data ini ngikutin otomatis dari halaman Kepengurusan.
          </p>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Kartu Penggunaan Sistem</h2>
            <i class="fa-regular fa-circle-question"></i>
          </div>
          <p class="usage-note">Digunakan di Seluruh Sistem</p>
          <div class="usage-columns">
            <div class="usage-item"><i class="fa-solid fa-gauge"></i> Dashboard</div>
            <div class="usage-item"><i class="fa-regular fa-calendar"></i> Agenda</div>
            <div class="usage-item"><i class="fa-solid fa-file-lines"></i> Surat Resmi</div>
            <div class="usage-item"><i class="fa-solid fa-chart-line"></i> Laporan</div>
            <div class="usage-item"><i class="fa-solid fa-right-to-bracket"></i> Halaman Login</div>
            <div class="usage-item"><i class="fa-solid fa-file-pdf"></i> Ekspor PDF</div>
          </div>
        </div>
      </div>
    </div>

    <div class="profile-actions-bar">
      <button type="button" class="btn btn-text" id="btnCancel">{{ __('general.batal') }}</button>
      <button type="button" class="btn btn-outline" id="btnReset">{{ __('general.atur_ulang') }}</button>
      <button type="button" class="btn btn-primary" id="btnSaveChanges">
        <i class="fa-solid fa-check"></i> {{ __('general.simpan_perubahan') }}
      </button>
    </div>
  </form>

@endsection

@section('modals')
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profil-masjid.js') }}"></script>
@endpush
