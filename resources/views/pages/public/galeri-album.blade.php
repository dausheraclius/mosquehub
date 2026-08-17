@extends('layouts.public')

@section('title', $album->nama . ' - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>{{ $album->nama }}</h1>
    <p>{{ $album->tanggal->translatedFormat('l, d F Y') }}</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-album-head">
      <a href="{{ route('public.galeri') }}" class="lp-album-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Galeri
      </a>
      @if ($album->deskripsi)
        <p class="lp-album-desc">{{ $album->deskripsi }}</p>
      @endif
    </div>

    @if ($photos->isEmpty())
      <p class="lp-keuangan-empty">Belum ada foto pada album ini.</p>
    @else
      <div class="lp-album-grid" id="albumGrid">
        @foreach ($photos as $photo)
          <button
            type="button"
            class="lp-album-item"
            data-index="{{ $loop->index }}"
            aria-label="Perbesar foto {{ $loop->index + 1 }}"
          >
            <img src="{{ $photo['url'] }}" alt="{{ $album->nama }}" loading="lazy" />
          </button>
        @endforeach
      </div>
    @endif
  </div>

  <div class="lp-lightbox" id="lightbox" hidden>
    <button type="button" class="lp-lightbox-close" id="lightboxClose" aria-label="Tutup">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <button type="button" class="lp-lightbox-nav lp-lightbox-prev" id="lightboxPrev" aria-label="Sebelumnya">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <div class="lp-lightbox-body">
      <img src="" alt="" id="lightboxImg" />
      <div class="lp-lightbox-counter" id="lightboxCounter"></div>
    </div>
    <button type="button" class="lp-lightbox-nav lp-lightbox-next" id="lightboxNext" aria-label="Berikutnya">
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  </div>
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const urls = @json($photos->pluck('url'))
      const items = Array.from(document.querySelectorAll('.lp-album-item'))
      const lightbox = document.getElementById('lightbox')
      const img = document.getElementById('lightboxImg')
      const counter = document.getElementById('lightboxCounter')
      let index = 0

      if (urls.length === 0) return

      function openLightbox(i) {
        index = i
        img.src = urls[index]
        img.alt = '{{ $album->nama }} ' + (index + 1)
        counter.textContent = (index + 1) + ' / ' + urls.length
        lightbox.hidden = false
        document.body.style.overflow = 'hidden'
      }

      function closeLightbox() {
        lightbox.hidden = true
        document.body.style.overflow = ''
        img.src = ''
      }

      function nav(delta) {
        const next = (index + delta + urls.length) % urls.length
        openLightbox(next)
      }

      items.forEach((btn) =>
        btn.addEventListener('click', () => openLightbox(Number(btn.dataset.index)))
      )

      document.getElementById('lightboxClose').addEventListener('click', closeLightbox)
      document.getElementById('lightboxPrev').addEventListener('click', () => nav(-1))
      document.getElementById('lightboxNext').addEventListener('click', () => nav(1))
      lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox()
      })

      document.addEventListener('keydown', (e) => {
        if (lightbox.hidden) return
        if (e.key === 'Escape') closeLightbox()
        if (e.key === 'ArrowLeft') nav(-1)
        if (e.key === 'ArrowRight') nav(1)
      })
    })
  </script>
@endsection
