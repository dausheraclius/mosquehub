@extends('layouts.app')

@section('title', 'MosqueHub - Log Aktivitas')

@push('styles-after-components')
  @vite('resources/assets/css/activity-log.css')
@endpush

@section('content')
  <x-page-header crumb="Pengawasan" active="Log Aktivitas" title="Log Aktivitas" subtitle="Riwayat perubahan dan ekspor data oleh pengurus." />

  <section class="activity-card">
    <div class="activity-card-header">
      <div>
        <h2>Riwayat administrasi</h2>
        <p>Hanya Ketua YMBPK yang dapat melihat riwayat ini.</p>
      </div>
      <span class="activity-count">{{ $activities->total() }} aktivitas</span>
    </div>

    @if ($activities->isEmpty())
      <div class="activity-empty"><i class="fa-solid fa-clipboard-list"></i> Belum ada aktivitas tercatat.</div>
    @else
      <div class="activity-table-wrap">
        <table class="activity-table">
          <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Modul</th></tr></thead>
          <tbody>
            @foreach ($activities as $activity)
              @php($route = $activity->properties['route'] ?? '-')
              <tr>
                <td>{{ $activity->created_at->translatedFormat('d M Y, H:i') }}</td>
                <td><strong>{{ $activity->causer?->name ?? 'Sistem' }}</strong><small>{{ $activity->causer?->email ?? '-' }}</small></td>
                <td>{{ $activity->description }}</td>
                <td><code>{{ $route }}</code></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="activity-pagination">{{ $activities->links() }}</div>
    @endif
  </section>
@endsection
