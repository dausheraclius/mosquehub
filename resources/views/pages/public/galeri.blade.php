@extends('layouts.public')

@section('title', 'Galeri - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Galeri</h1>
    <p>Dokumentasi kegiatan dan acara {{ $siteMosque->name }}.</p>
  </div>

  <div class="lp-public-section">
    @if ($albums->isEmpty())
      <p style="text-align:center; color:var(--text-muted); padding:24px;">Belum ada dokumentasi kegiatan.</p>
    @else
      <div class="lp-galeri-grid">
        @foreach ($albums as $album)
          <div class="lp-galeri-card">
            <div class="lp-galeri-cover">
              @if ($album['cover'])
                <img src="{{ $album['cover'] }}" alt="{{ $album['nama'] }}" loading="lazy" />
              @else
                <div class="lp-galeri-cover-placeholder">
                  <i class="fa-solid fa-images"></i>
                  <span>{{ $album['photoCount'] }} foto</span>
                </div>
              @endif
            </div>
            <div class="lp-galeri-body">
              <div class="lp-galeri-title">{{ $album['nama'] }}</div>
              <div class="lp-galeri-meta">
                <span><i class="fa-regular fa-calendar"></i> {{ $album['tanggal'] }}</span>
                @if ($album['photoCount'] > 0)
                  <span><i class="fa-regular fa-image"></i> {{ $album['photoCount'] }} foto</span>
                @endif
              </div>
              @if ($album['deskripsi'])
                <div class="lp-galeri-desc">{{ \Illuminate\Support\Str::limit($album['deskripsi'], 90) }}</div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection