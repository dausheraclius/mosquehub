@extends('layouts.public')

@section('title', 'Kontak - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Kontak</h1>
    <p>Hubungi {{ $siteMosque->name }} untuk informasi lebih lanjut.</p>
  </div>

  <div class="lp-public-section">
    <div class="kontak-grid">
      <!-- INFO KONTAK -->
      <div>
        <h2 class="kontak-subtitle">Informasi Kontak</h2>

        <div class="kontak-info-card">
          <div class="kontak-info-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div>
            <div class="kontak-info-label">Alamat</div>
            <div class="kontak-info-value">{{ $siteMosque->address ?: $siteMosque->city ?: $siteMosque->district ?: 'Alamat belum diisi' }}</div>
          </div>
        </div>

        <div class="kontak-info-card">
          <div class="kontak-info-icon"><i class="fa-solid fa-phone"></i></div>
          <div>
            <div class="kontak-info-label">Telepon / WhatsApp</div>
            <div class="kontak-info-value">{{ $siteMosque->phone ?: $siteMosque->whatsapp ?: 'Belum tersedia' }}</div>
          </div>
        </div>

        <div class="kontak-info-card">
          <div class="kontak-info-icon"><i class="fa-solid fa-envelope"></i></div>
          <div>
            <div class="kontak-info-label">Email</div>
            <div class="kontak-info-value">{{ $siteMosque->email ?: 'Belum tersedia' }}</div>
          </div>
        </div>

        <div class="kontak-info-card">
          <div class="kontak-info-icon"><i class="fa-regular fa-clock"></i></div>
          <div>
            <div class="kontak-info-label">Layanan</div>
            <div class="kontak-info-value">{{ $siteMosque->category ?: 'Masjid' }} &middot; Melayani jamaah setiap hari</div>
          </div>
        </div>

        @php
          $sosmed = [
              'ig' => ['label' => 'Instagram', 'url' => $siteMosque->instagram ?? null],
              'fb' => ['label' => 'Facebook', 'url' => $siteMosque->facebook ?? null],
              'yt' => ['label' => 'YouTube', 'url' => $siteMosque->youtube ?? null],
              'tt' => ['label' => 'TikTok', 'url' => $siteMosque->tiktok ?? null],
              'wa' => ['label' => 'WhatsApp', 'url' => $siteMosque->whatsapp ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $siteMosque->whatsapp) : null],
          ];
          $sosmed = array_filter($sosmed, fn ($s) => $s['url']);
        @endphp

        @if (count($sosmed))
          <h2 class="kontak-subtitle" style="margin-top:28px;">Media Sosial</h2>
          <div class="kontak-sosial-row">
            @foreach ($sosmed as $key => $s)
              <a href="{{ $s['url'] }}" class="kontak-sosial-link {{ $key }}" target="_blank" rel="noopener" title="{{ $s['label'] }}">
                <i class="fa-brands fa-{{ $key === 'ig' ? 'instagram' : ($key === 'wa' ? 'whatsapp' : $key) }}"></i>
              </a>
            @endforeach
          </div>
        @endif
      </div>

      <!-- FORM PESAN -->
      <div>
        <h2 class="kontak-subtitle">Kirim Pesan</h2>

        @if (session('success'))
          <div class="kontak-alert kontak-alert-success">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="kontak-alert kontak-alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i> Periksa kembali isian form Anda.
          </div>
        @endif

        <form method="POST" action="{{ route('public.kontak.store') }}" class="kontak-form" novalidate>
          @csrf
          <div class="kontak-form-row">
            <div class="kontak-form-field">
              <label for="nama">Nama <span style="color:var(--color-teal);">*</span></label>
              <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Nama lengkap Anda" required maxlength="120" />
            </div>
            <div class="kontak-form-field">
              <label for="email">Email <span style="color:var(--color-teal);">*</span></label>
              <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="alamat@email.com" required maxlength="120" />
            </div>
          </div>
          <div class="kontak-form-row">
            <div class="kontak-form-field">
              <label for="telepon">Telepon / WhatsApp</label>
              <input type="text" id="telepon" name="telepon" value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx" maxlength="30" />
            </div>
            <div class="kontak-form-field">
              <label for="subjek">Subjek</label>
              <input type="text" id="subjek" name="subjek" value="{{ old('subjek') }}" placeholder="Topik pesan" maxlength="150" />
            </div>
          </div>
          <div class="kontak-form-field">
            <label for="pesan">Pesan <span style="color:var(--color-teal);">*</span></label>
            <textarea id="pesan" name="pesan" placeholder="Tulis pesan Anda di sini..." required maxlength="2000">{{ old('pesan') }}</textarea>
          </div>
          <button type="submit" class="btn-lp-primary">
            <i class="fa-solid fa-paper-plane"></i> Kirim Pesan
          </button>
        </form>
      </div>
    </div>

    @if ($siteMosque->maps_link)
      <a href="{{ $siteMosque->maps_link }}" target="_blank" rel="noopener" class="kontak-map" style="text-decoration:none;">
        <i class="fa-solid fa-map"></i> Buka lokasi di Google Maps
      </a>
    @endif
  </div>
@endsection