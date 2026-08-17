<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Masjid — MosqueHub</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
      font-size: 12px;
      color: #111827;
      margin: 0;
      padding: 24px;
      background: #fff;
    }

    /* --- Satu blok laporan; halaman baru otomatis antar laporan --- */
    .report-page { page-break-inside: auto; }
    .report-page.break { page-break-after: always; }

    /* --- Kop laporan --- */
    .kop {
      text-align: center;
      border-bottom: 2.5px solid #0f766e;
      padding-bottom: 10px;
      margin-bottom: 18px;
    }
    .kop h1 { font-size: 17px; margin: 0 0 3px; color: #0f172a; }
    .kop .sub { font-size: 11px; color: #4b5563; margin: 0; }

    /* --- Judul & meta --- */
    .report-title {
      font-size: 14px;
      font-weight: 700;
      color: #0f766e;
      text-transform: uppercase;
      letter-spacing: .3px;
      margin-bottom: 4px;
    }
    .report-meta { font-size: 10.5px; color: #6b7280; margin-bottom: 14px; }

    /* --- Kotak ringkasan --- */
    .summary-box {
      border: 1px solid #d1fae5;
      background: #f0fdf9;
      border-radius: 6px;
      padding: 10px 12px;
      margin-bottom: 14px;
      page-break-inside: avoid;
    }
    .summary-box .summary-title {
      font-size: 10.5px;
      font-weight: 700;
      color: #0f766e;
      text-transform: uppercase;
      letter-spacing: .3px;
      margin-bottom: 6px;
    }
    .summary-box table { width: 100%; border-collapse: collapse; }
    .summary-box td { padding: 3px 4px; font-size: 11.5px; }
    .summary-box td.label { color: #374151; width: 50%; }
    .summary-box td.value { font-weight: 700; color: #0f766e; text-align: right; }

    /* --- Tabel data --- */
    table.data { width: 100%; border-collapse: collapse; margin-top: 4px; page-break-inside: auto; }
    table.data th {
      background: #0f766e;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      text-align: left;
      padding: 7px 8px;
      border: 1px solid #0f766e;
    }
    table.data td {
      padding: 6px 8px;
      font-size: 11.5px;
      border: 1px solid #e5e7eb;
    }
    table.data tr:nth-child(even) td { background: #f9fafb; }
    table.data td.empty { text-align: center; color: #6b7280; padding: 16px; }

    .footer {
      margin-top: 18px;
      padding-top: 8px;
      border-top: 1px solid #e5e7eb;
      font-size: 10px;
      color: #6b7280;
      text-align: center;
    }

    /* --- Toolbar hanya tampil di layar (rute Cetak) --- */
    .toolbar {
      position: fixed;
      top: 12px;
      right: 12px;
      display: flex;
      gap: 8px;
      z-index: 10;
    }
    .toolbar button {
      border: 1px solid #0f766e;
      background: #0f766e;
      color: #fff;
      padding: 7px 14px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
    }
    .toolbar button.secondary { background: #fff; color: #0f766e; }

    @media print {
      body { padding: 0; }
      .toolbar { display: none !important; }
      .summary-box, table.data th {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>

  @if ($autoPrint ?? false)
    <div class="toolbar">
      <button onclick="window.print()">🖨 Cetak</button>
      <button class="secondary" onclick="window.close()">← Tutup</button>
    </div>
  @endif

  @foreach ($reports as $report)
    <section class="report-page @if(!$loop->last) break @endif">
      <div class="kop">
        <h1>{{ $mosque?->name ?? 'Masjid' }}</h1>
        <p class="sub">
          {{ $mosque?->address ?? '' }}@if(!empty($mosque?->city)), {{ $mosque->city }}@endif
        </p>
      </div>

      <div class="report-title">{{ $report['judul'] }}</div>
      <div class="report-meta">{{ $report['updated'] }} · {{ $report['dibuat'] }}</div>

      <div class="summary-box">
        <div class="summary-title">Ringkasan</div>
        <table>
          @foreach ($report['summary'] as $s)
            <tr>
              <td class="label">{{ $s['label'] }}</td>
              <td class="value">{{ $s['value'] }}</td>
            </tr>
          @endforeach
        </table>
      </div>

      <table class="data">
        <thead>
          <tr>
            @foreach ($report['columns'] as $col)
              <th>{{ $col }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse ($report['rows'] as $row)
            <tr>
              @foreach ($row as $cell)
                <td>{{ $cell }}</td>
              @endforeach
            </tr>
          @empty
            <tr>
              <td colspan="{{ count($report['columns']) }}" class="empty">Belum ada data untuk laporan ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </section>
  @endforeach

  <div class="footer">Dicetak {{ now()->translatedFormat('d M Y H:i') }} · Sistem Informasi Masjid — MosqueHub</div>

</body>
</html>
