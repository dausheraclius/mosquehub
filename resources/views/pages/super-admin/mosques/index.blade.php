@extends('layouts.super-admin')

@section('title', 'Kelola Masjid - Super Admin')

@section('content')
  <h1 class="page-title" style="margin-bottom:20px;">Kelola Masjid</h1>

  <div class="card table-card">
    <table class="data-table">
      <thead><tr><th>Nama Masjid</th><th>Jumlah User</th><th>Status</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach ($mosques as $m)
          <tr>
            <td>{{ $m->name }}</td>
            <td>{{ $m->users_count }}</td>
            <td><span class="status-badge {{ $m->status === 'aktif' ? 'status-aktif' : '' }}">{{ ucfirst($m->status) }}</span></td>
            <td>{{ $m->created_at->translatedFormat('d M Y') }}</td>
            <td>
              <a href="{{ route('super.mosques.show', $m) }}" class="btn-sm btn-detail">Detail</a>
              <form action="{{ route('super.mosques.switch', $m) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-sm btn-edit">Lihat Sebagai</button>
              </form>
              <form action="{{ route('super.mosques.status', $m) }}" method="PATCH" style="display:inline;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $m->status === 'aktif' ? 'nonaktif' : 'aktif' }}">
                <button type="submit" class="btn-sm btn-hapus">{{ $m->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection