@extends('layouts.public')

@section('title', $kegiatan->nama . ' - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>{{ $kegiatan->nama }}</h1>
    <p>{{ $kegiatan->tanggal->translatedFormat('l, d F Y') }}{{ $kegiatan->jam_mulai ? ' • ' . substr($kegiatan->jam_mulai, 0, 5) . ($kegiatan->jam_selesai ? ' – ' . substr($kegiatan->jam_selesai, 0, 5) : '') : '' }}</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-detail-card">
      @if ($kegiatan->kategori)
        <span class="lp-badge">{{ $kegiatan->kategori }}</span>
      @endif
      <div class="lp-detail-meta">
        @if ($kegiatan->lokasi)
          <span><i class="fa-solid fa-location-dot"></i> {{ $kegiatan->lokasi }}</span>
        @endif
        @if ($kegiatan->pemateri)
          <span><i class="fa-solid fa-microphone-lines"></i> Pemateri: {{ $kegiatan->pemateri }}</span>
        @endif
        @if ($kegiatan->pj)
          <span><i class="fa-solid fa-user"></i> Pj: {{ $kegiatan->pj }}</span>
        @endif
        @if ($kegiatan->peserta)
          <span><i class="fa-solid fa-users"></i> Peserta: {{ $kegiatan->peserta }}</span>
        @endif
      </div>
      <div class="lp-detail-body">{{ $kegiatan->deskripsi ?: 'Deskripsi kegiatan belum diisi.' }}</div>
      <a href="{{ route('public.jadwal') }}" class="btn-lp-view-details" style="display:inline-flex; text-decoration:none; margin-top:24px;">
        <i class="fa-solid fa-arrow-left"></i> &nbsp;Kembali ke Jadwal
      </a>
    </div>
  </div>
@endsection
