@extends('layouts.public')

@section('title', 'Struktur Pengurus - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Struktur Pengurus</h1>
    <p>Susunan kepengurusan {{ $siteMosque->name }} yang menjalankan layanan masjid.</p>
  </div>

  <div class="lp-public-section">
    @if ($items->isEmpty())
      <p class="lp-keuangan-empty">Struktur pengurus belum diisi.</p>
    @else
      <div class="lp-pengurus-grid">
        @foreach ($items as $item)
          <div class="lp-pengurus-card">
            <div class="lp-pengurus-avatar">
              <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
              <div class="lp-pengurus-jabatan">{{ $item['nama'] }}</div>
              <div class="lp-pengurus-nama">{{ $item['pengurus'] ?: '—' }}</div>
              @if ($item['parent'])
                <div class="lp-pengurus-parent">di bawah {{ $item['parent'] }}</div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
