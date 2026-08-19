@extends('layouts.super-admin')

@section('title', 'Dashboard - Super Admin')

@section('content')
  <h1 class="page-title" style="margin-bottom:20px;">Dashboard Super Admin</h1>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Total Masjid</span></div>
      <span class="stat-value">{{ $totalMasjid }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Masjid Aktif</span></div>
      <span class="stat-value">{{ $masjidAktif }}</span>
    </div>
    <div class="stat-card">
      <div class="stat-card-top"><span class="stat-label">Total User (semua masjid)</span></div>
      <span class="stat-value">{{ $totalUser }}</span>
    </div>
  </div>

  <div class="card table-card" style="margin-top:20px;">
    <div class="card-header"><h2 class="card-title">Masjid Terbaru Daftar</h2></div>
    <table class="data-table">
      <thead><tr><th>Nama</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach ($masjidTerbaru as $m)
          <tr>
            <td>{{ $m->name }}</td>
            <td><span class="status-badge {{ $m->status === 'aktif' ? 'status-aktif' : '' }}">{{ ucfirst($m->status) }}</span></td>
            <td>{{ $m->created_at->translatedFormat('d M Y') }}</td>
            <td><a href="{{ route('super.mosques.show', $m) }}" class="btn-sm btn-detail">Detail</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection