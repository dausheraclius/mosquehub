@extends('layouts.public')

@section('title', 'Tentang Masjid - MosqueHub')

@section('content')
  <style>
    .lp-about-grid {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 24px;
      align-items: start;
    }

    .lp-about-card {
      background: var(--color-white);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      padding: 26px;
    }

    .lp-about-card + .lp-about-card {
      margin-top: 24px;
    }

    .lp-about-head {
      font-size: 17px;
      font-weight: 400;
      color: var(--text-dark);
      margin-bottom: 14px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border-color);
    }

    .lp-about-desc {
      font-size: 13.5px;
      line-height: 1.75;
      color: var(--text-muted);
      margin: 0;
    }

    .lp-about-list {
      list-style: none;
      margin: 0;
    }

    .lp-about-list li {
      display: flex;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px dashed var(--border-color);
      font-size: 13px;
      color: var(--text-dark);
    }

    .lp-about-list li:last-child {
      border-bottom: none;
    }

    .lp-about-list li i {
      width: 16px;
      text-align: center;
      color: var(--color-teal);
      margin-top: 2px;
      flex-shrink: 0;
    }

    .lp-about-list li div span:first-child {
      font-size: 11.5px;
      color: var(--text-muted);
      display: block;
      margin-bottom: 2px;
    }

    .lp-about-sosmed {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 4px;
    }

    .lp-about-sosmed a {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: var(--text-dark);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 8px 14px;
      text-decoration: none;
      transition: background 0.15s, border-color 0.15s;
    }

    .lp-about-sosmed a:hover {
      background: var(--color-green-light);
      border-color: var(--color-teal);
    }

    .lp-about-sosmed a i {
      color: var(--color-teal);
    }

    @media (max-width: 768px) {
      .lp-about-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="lp-page-header">
    <h1>Tentang Masjid</h1>
    <p>{{ $mosque->name ?? 'Masjid kami' }} — sekilas profil, sejarah, dan informasi kontak.</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-about-grid">
      <div>
        <div class="lp-about-card">
          <h2 class="lp-about-head">Tentang {{ $mosque->name ?? 'Masjid Kami' }}</h2>
          <p class="lp-about-desc">
            {{ $mosque->name ?? 'Masjid ini' }}
            {{ $mosque->category ? ' merupakan ' . $mosque->category . ' ' : '' }}
            yang menjadi pusat ibadah dan kegiatan kemasyarakatan.
            {{ $mosque->established_year ? 'Berdiri sejak tahun ' . $mosque->established_year . '.' : '' }}
            Kami berkomitmen menghadirkan informasi yang transparan serta pelayanan terbaik
            bagi seluruh jamaah.
          </p>
        </div>

        <div class="lp-about-card">
          <h2 class="lp-about-head">Alamat &amp; Kontak</h2>
          <ul class="lp-about-list">
            <li>
              <i class="fa-solid fa-location-dot"></i>
              <div>
                <span>Alamat</span>
                {{ $mosque->address ?: $mosque->city ?: $mosque->district ?: 'Alamat belum diisi' }}
              </div>
            </li>
            <li>
              <i class="fa-solid fa-phone"></i>
              <div>
                <span>Telepon / WhatsApp</span>
                {{ $mosque->phone ?: $mosque->whatsapp ?: 'Belum tersedia' }}
              </div>
            </li>
            <li>
              <i class="fa-solid fa-envelope"></i>
              <div>
                <span>Email</span>
                {{ $mosque->email ?: 'Belum tersedia' }}
              </div>
            </li>
            <li>
              <i class="fa-regular fa-clock"></i>
              <div>
                <span>Layanan</span>
                {{ $mosque->category ?: 'Masjid' }} &middot; Melayani jamaah setiap hari
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <div class="lp-about-card">
          <h2 class="lp-about-head">Media Sosial</h2>
          @php
            $sosmed = [
                'Instagram' => $mosque->instagram ?? null,
                'Facebook' => $mosque->facebook ?? null,
                'YouTube' => $mosque->youtube ?? null,
                'TikTok' => $mosque->tiktok ?? null,
                'WhatsApp' => $mosque->whatsapp ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $mosque->whatsapp) : null,
            ];
            $sosmed = array_filter($sosmed);
          @endphp
          @if (count($sosmed))
            <div class="lp-about-sosmed">
              @foreach ($sosmed as $label => $url)
                <a href="{{ $url }}" target="_blank" rel="noopener">
                  <i class="fa-brands fa-{{ strtolower($label) }}"></i> {{ $label }}
                </a>
              @endforeach
            </div>
          @else
            <p class="lp-about-desc">Akun media sosial belum ditambahkan.</p>
          @endif
        </div>

        @if ($mosque->maps_link)
          <div class="lp-about-card">
            <h2 class="lp-about-head">Lokasi</h2>
            <a href="{{ $mosque->maps_link }}" target="_blank" rel="noopener"
              class="btn-lp-view-details" style="display:inline-flex; text-decoration:none;">
              <i class="fa-solid fa-map"></i> Buka di Google Maps
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection