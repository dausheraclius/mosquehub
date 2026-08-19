@extends('layouts.super-admin')

@section('title', 'Semua User - Super Admin')

@section('content')
  <h1 class="page-title" style="margin-bottom:20px;">Semua User (Lintas Masjid)</h1>

  <form method="GET" style="margin-bottom:16px;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/email..." class="form-input" style="max-width:280px;">
  </form>

  <div class="card table-card">
    <table class="data-table">
      <thead><tr><th>Nama</th><th>Email</th><th>Masjid</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        @foreach ($users as $u)
          <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->mosque->name ?? '-' }}</td>
            <td>{{ $u->role }}</td>
            <td><span class="status-badge {{ $u->status === 'aktif' ? 'status-aktif' : '' }}">{{ ucfirst($u->status) }}</span></td>
            <td>
              <form action="{{ route('super.users.impersonate', $u) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-sm btn-detail">Login Sebagai</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div style="padding:16px;">{{ $users->links() }}</div>
  </div>
@endsection