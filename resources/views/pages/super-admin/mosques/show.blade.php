@extends('layouts.super-admin')

@section('title', $mosque->name . ' - Super Admin')

@section('content')
  <a href="{{ route('super.mosques') }}" style="font-size:13px;color:var(--text-muted);">← Kembali ke Kelola Masjid</a>
  <h1 class="page-title" style="margin:12px 0 20px;">{{ $mosque->name }}</h1>

  <div class="card table-card">
    <div class="card-header"><h2 class="card-title">Pengguna di Masjid Ini</h2></div>
    <table class="data-table">
      <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->role }}</td>
            <td><span class="status-badge {{ $u->status === 'aktif' ? 'status-aktif' : '' }}">{{ ucfirst($u->status) }}</span></td>
            <td>
              <form action="{{ route('super.users.impersonate', $u) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-sm btn-detail">Login Sebagai</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Belum ada user.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection