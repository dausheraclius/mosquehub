@extends('layouts.public')

@section('title', 'Jadwal Petugas Sholat - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Jadwal Petugas Sholat</h1>
    <p>Jadwal khatib, imam, dan muadzin {{ $siteMosque->name }}.</p>
  </div>

  <div class="lp-public-section">
    @if ($items->isEmpty())
      <p class="lp-keuangan-empty">Belum ada jadwal petugas sholat.</p>
    @else
      <div class="lp-petugas-list">
        @foreach ($items as $day)
          <div class="lp-petugas-day">
            <div class="lp-petugas-day-title">{{ $day['label'] }}</div>
            @foreach ($day['rows'] as $row)
              <div class="lp-petugas-row">
                <div class="lp-petugas-sholat">{{ $row['sholat'] }}</div>
                <div class="lp-petugas-detail">
                  @if ($row['khatib'])
                    <span><i class="fa-solid fa-microphone-lines"></i> Khatib: {{ $row['khatib'] }}</span>
                  @endif
                  @if ($row['imam'])
                    <span><i class="fa-solid fa-person-praying"></i> Imam: {{ $row['imam'] }}</span>
                  @endif
                  @if ($row['muadzin'])
                    <span><i class="fa-solid fa-bullhorn"></i> Muadzin: {{ $row['muadzin'] }}</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
