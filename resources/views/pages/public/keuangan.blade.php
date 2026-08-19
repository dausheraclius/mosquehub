@extends('layouts.public')

@section('title', 'Keuangan & Transparansi - MosqueHub')

@section('content')
  <div class="lp-page-header">
    <h1>Keuangan &amp; Transparansi</h1>
    <p>Laporan kas dan infaq {{ $siteMosque->name }} yang diperbarui secara berkala.</p>
  </div>

  <div class="lp-public-section">
    <div class="lp-transparency-grid" style="margin-bottom: 34px;">
      <div class="lp-transparency-card">
        <div class="lp-transparency-icon"><i class="fa-solid fa-piggy-bank"></i></div>
        <div class="lp-transparency-value">{{ $formatRupiah($saldo) }}</div>
        <div class="lp-transparency-label">Saldo Kas Masjid</div>
      </div>
      <div class="lp-transparency-card">
        <div class="lp-transparency-icon"><i class="fa-solid fa-arrow-down-wide-short"></i></div>
        <div class="lp-transparency-value" style="color: var(--color-teal);">{{ $formatRupiah($pemasukan) }}</div>
        <div class="lp-transparency-label">Total Pemasukan</div>
      </div>
      <div class="lp-transparency-card">
        <div class="lp-transparency-icon"><i class="fa-solid fa-arrow-up-wide-short"></i></div>
        <div class="lp-transparency-value" style="color: var(--color-danger, #dc2626);">{{ $formatRupiah($pengeluaran) }}</div>
        <div class="lp-transparency-label">Total Pengeluaran</div>
      </div>
      <div class="lp-transparency-card">
        <div class="lp-transparency-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <div class="lp-transparency-value">{{ $formatRupiah($totalDonasi) }}</div>
        <div class="lp-transparency-label">Total Infaq &amp; Donasi</div>
      </div>
    </div>

    <div class="lp-keuangan-block">
      <h2 class="lp-section-title">Rekap Kas 6 Bulan Terakhir</h2>
      <div class="lp-table-wrap">
        <table class="lp-table">
          <thead>
            <tr>
              <th>Bulan</th>
              <th class="lp-ta-right">Pemasukan</th>
              <th class="lp-ta-right">Pengeluaran</th>
              <th class="lp-ta-right">Saldo Bulan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($perBulan as $b)
              <tr>
                <td>{{ $b['label'] }}</td>
                <td class="lp-ta-right lp-txt-teal">{{ $formatRupiah($b['masuk']) }}</td>
                <td class="lp-ta-right lp-txt-red">{{ $formatRupiah($b['keluar']) }}</td>
                <td class="lp-ta-right">{{ $formatRupiah($b['saldo']) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="lp-keuangan-block">
      <h2 class="lp-section-title">Infaq &amp; Donasi per Kategori</h2>
      @if ($perKategori->isEmpty())
        <p class="lp-keuangan-empty">Belum ada donasi tercatat.</p>
      @else
        <div class="lp-kategori-grid">
          @foreach ($perKategori as $k)
            <div class="lp-kategori-card">
              <div class="lp-kategori-top">
                <span class="lp-kategori-label">{{ $k['label'] }}</span>
                <span class="lp-kategori-count">{{ $k['jumlah'] }}×</span>
              </div>
              <div class="lp-kategori-total">{{ $formatRupiah($k['total']) }}</div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    <div class="lp-keuangan-block">
      <h2 class="lp-section-title">Transaksi Kas Terbaru</h2>
      @if ($transaksiTerbaru->isEmpty())
        <p class="lp-keuangan-empty">Belum ada transaksi kas.</p>
      @else
        <div class="lp-table-wrap">
          <table class="lp-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="lp-ta-right">Nominal</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($transaksiTerbaru as $t)
                <tr>
                  <td>{{ $t['tanggal'] }}</td>
                  <td>{{ $t['keterangan'] }}</td>
                  <td class="lp-ta-right {{ $t['tipe'] === 'Pengeluaran' ? 'lp-txt-red' : 'lp-txt-teal' }}">
                    {{ $t['tipe'] === 'Pengeluaran' ? '− ' . $formatRupiah($t['nominal']) : '+' . $formatRupiah($t['nominal']) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if ($transaksiTerbaru->hasPages())
          <nav class="lp-pagination" aria-label="Halaman transaksi kas">
            @if ($transaksiTerbaru->onFirstPage())
              <span class="lp-page-btn" aria-disabled="true">&laquo; Sebelumnya</span>
            @else
              <a href="{{ $transaksiTerbaru->previousPageUrl() }}" class="lp-page-btn" rel="prev">&laquo; Sebelumnya</a>
            @endif

            @foreach ($transaksiTerbaru->getUrlRange(1, $transaksiTerbaru->lastPage()) as $page => $url)
              @if ($page === $transaksiTerbaru->currentPage())
                <span class="lp-page-btn active" aria-current="page">{{ $page }}</span>
              @else
                <a href="{{ $url }}" class="lp-page-btn">{{ $page }}</a>
              @endif
            @endforeach

            @if ($transaksiTerbaru->hasMorePages())
              <a href="{{ $transaksiTerbaru->nextPageUrl() }}" class="lp-page-btn" rel="next">Berikutnya &raquo;</a>
            @else
              <span class="lp-page-btn" aria-disabled="true">Berikutnya &raquo;</span>
            @endif
          </nav>
        @endif
      @endif
    </div>
  </div>
@endsection
