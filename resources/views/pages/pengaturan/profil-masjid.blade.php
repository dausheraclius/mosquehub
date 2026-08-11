@extends('layouts.app')

@section('title', 'MosqueHub - Profil Masjid')

@push('styles-before-components')
  <link rel="stylesheet" href="{{ asset('assets/css/profil-masjid.css') }}">
@endpush

@section('content')

  <x-content-header title="Profil Masjid" subtitle="Ketua YMBPK · 20 Juli 2026" />

  <!-- BANNER PROFIL -->
  <div class="card profile-banner">
    <div class="profile-banner-logo" id="bannerLogo">
      <i class="fa-solid fa-mosque"></i>
    </div>
    <div class="profile-banner-info">
      <div class="profile-banner-name">
        YMBPK Baiturrahim
        <span class="status-pill">Aktif</span>
      </div>
      <div class="profile-banner-line">Masjid Jami' Pusat Kegiatan Umat di Bandung</div>
      <div class="profile-banner-line">Jl. Merdeka No. 123, Bandung, Jawa Barat 40111</div>
      <div class="profile-banner-contact">
        <span><i class="fa-solid fa-phone"></i> +62 812 3456 7890</span>
        <span><i class="fa-solid fa-envelope"></i> info@baiturrahim.id</span>
        <span><i class="fa-solid fa-globe"></i> www.baiturrahim.id</span>
      </div>
    </div>
    <div class="profile-banner-actions">
      <button class="btn btn-outline" id="btnEditProfile"><i class="fa-solid fa-pen"></i> Edit Profil</button>
    </div>
  </div>

  <form id="profilForm" onsubmit="return false">
    <div class="profile-grid">
      <!-- KOLOM KIRI -->
      <div class="profile-col">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Informasi Dasar</h2></div>
          <div class="form-row">
            <div class="form-field">
              <label for="mosqueName">Nama Masjid</label>
              <input type="text" id="mosqueName" value="YMBPK Baiturrahim" disabled />
            </div>
            <div class="form-field">
              <label for="shortName">Nama Singkat</label>
              <input type="text" id="shortName" placeholder="Nama Singkat" disabled />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="establishedYear">Tahun Berdiri</label>
              <input type="text" id="establishedYear" value="2005" disabled />
            </div>
            <div class="form-field">
              <label for="mosqueCategory">Kategori Masjid</label>
              <select id="mosqueCategory" disabled>
                <option>Masjid Jami'</option>
                <option>Masjid Raya</option>
                <option>Musholla</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="phoneNumber">Nomor Telepon</label>
              <input type="text" id="phoneNumber" value="+62 812 3456 7890" disabled />
            </div>
            <div class="form-field">
              <label for="email">Email</label>
              <input type="email" id="email" value="info@baiturrahim.id" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="website">Website</label>
              <input type="text" id="website" value="www.baiturrahim.id" disabled />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">Lokasi</h2></div>
          <div class="form-row">
            <div class="form-field">
              <label for="province">Provinsi</label>
              <input type="text" id="province" placeholder="Provinsi" disabled />
            </div>
            <div class="form-field">
              <label for="city">Kota</label>
              <input type="text" id="city" value="Bandung" disabled />
            </div>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label for="district">Kecamatan</label>
              <input type="text" id="district" placeholder="Kecamatan" disabled />
            </div>
            <div class="form-field">
              <label for="kelurahan">Kelurahan</label>
              <input type="text" id="kelurahan" placeholder="Kelurahan" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="postalCode">Kode Pos</label>
              <input type="text" id="postalCode" value="40111" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="completeAddress">Alamat Lengkap</label>
              <input type="text" id="completeAddress" value="Jl. Merdeka No. 123, Bandung, Jawa Barat" disabled />
            </div>
          </div>
          <div class="form-row single">
            <div class="form-field">
              <label for="mapsLink">Link Google Maps</label>
              <input type="text" id="mapsLink" placeholder="Link Google Maps" disabled />
            </div>
          </div>
          <div class="map-placeholder"><i class="fa-solid fa-location-dot"></i></div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">Media Sosial</h2></div>
          <div class="social-row">
            <div class="social-icon ig"><i class="fa-brands fa-instagram"></i></div>
            <input type="text" id="igLink" placeholder="Instagram/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon fb"><i class="fa-brands fa-facebook-f"></i></div>
            <input type="text" id="fbLink" placeholder="Facebook/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon yt"><i class="fa-brands fa-youtube"></i></div>
            <input type="text" id="ytLink" placeholder="YouTube/" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon tt"><i class="fa-brands fa-tiktok"></i></div>
            <input type="text" id="ttLink" placeholder="TikTok/Tautan" disabled />
          </div>
          <div class="social-row">
            <div class="social-icon wa"><i class="fa-brands fa-whatsapp"></i></div>
            <input type="text" id="waLink" placeholder="WhatsApp/" disabled />
          </div>
        </div>
      </div>

      <!-- KOLOM KANAN -->
      <div class="profile-col">
        <div class="card">
          <div class="card-header"><h2 class="card-title">Logo Masjid</h2></div>
          <button
            type="button"
            class="btn btn-outline"
            id="logoUploadBtn"
            style="width: 100%; justify-content: center"
            onclick="document.getElementById('logoInput').click()"
          >
            <i class="fa-solid fa-upload"></i> Unggah Logo
          </button>
          <input type="file" id="logoInput" accept="image/*" hidden />
          <div class="logo-upload-box" id="logoBox">
            <i class="fa-solid fa-mosque"></i>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">Identitas Resmi</h2></div>
          <div class="identity-grid">
            <div class="identity-box" data-upload>
              Stempel Resmi
              <input type="file" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
            <div class="identity-box" data-upload>
              Pratinjau Kepala Surat
              <input type="file" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
            <div class="identity-box" data-upload>
              Tempat Tanda Tangan
              <input type="file" accept="image/*,.pdf,.doc,.docx" hidden />
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2 class="card-title">Informasi Pengurus</h2></div>
          <div class="mgmt-field">
            <label>Ketua YMBPK</label>
            <div class="mgmt-select-wrap highlight">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <select class="mgmt-select-input" id="mgmtChairman">
                <option value="1" selected>Ust. Daus Morgan</option>
                <option value="2">M. Reza</option>
                <option value="3">Fatimah</option>
                <option value="4">S. Abdullah</option>
              </select>
            </div>
          </div>
          <div class="mgmt-field">
            <label>Sekretaris</label>
            <div class="mgmt-select-wrap">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <select class="mgmt-select-input" id="mgmtSecretary">
                <option value="1">Ust. Daus Morgan</option>
                <option value="2" selected>M. Reza</option>
                <option value="3">Fatimah</option>
                <option value="4">S. Abdullah</option>
              </select>
            </div>
          </div>
          <div class="mgmt-field">
            <label>Bendahara</label>
            <div class="mgmt-select-wrap">
              <div class="mgmt-select-avatar"><i class="fa-solid fa-user"></i></div>
              <select class="mgmt-select-input" id="mgmtTreasurer">
                <option value="1">Ust. Daus Morgan</option>
                <option value="2">M. Reza</option>
                <option value="3" selected>Fatimah</option>
                <option value="4">S. Abdullah</option>
              </select>
            </div>
          </div>
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
      <button type="button" class="btn btn-text" id="btnCancel">Batal</button>
      <button type="button" class="btn btn-outline" id="btnReset">Atur Ulang</button>
      <button type="button" class="btn btn-primary" id="btnSaveChanges">
        <i class="fa-solid fa-check"></i> Simpan Perubahan
      </button>
    </div>
  </form>

@endsection

@section('modals')
  <div class="toast" id="appToast"></div>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profil-masjid.js') }}"></script>
@endpush