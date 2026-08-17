@extends('layouts.public')

@section('title', $pengumuman->judul . ' - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>{{ $pengumuman->judul }}</h1>
    <p>{{ $pengumuman->tanggal->translatedFormat('l, d F Y') }}</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-detail-card">
      <span class="lp-badge">{{ $pengumuman->kategori ?: 'Umum' }}</span>
      <div class="lp-detail-body" style="margin-top:18px;">{{ $pengumuman->isi }}</div>
      <a href="{{ route('public.pengumuman') }}" class="btn-lp-view-details" style="display:inline-flex; text-decoration:none; margin-top:24px;">
        <i class="fa-solid fa-arrow-left"></i> &nbsp;Kembali ke Pengumuman
      </a>
    </div>
  </div>
@endsection
