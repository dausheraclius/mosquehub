<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Inventaris;
use App\Models\Jabatan;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\KegiatanRelawan;
use App\Models\Mosque;
use App\Models\Surat;
use App\Support\Concerns\HasMosqueContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

class LaporanController extends Controller
{
    use HasMosqueContext;

    public function index(Request $request)
    {
        $mosqueId = $this->mosqueId;
        $window = $this->buildWindow($request);

        $laporanData = array_map(
            fn ($l) => $this->injectLabels($l, $window),
            [
                $this->laporanJemaah($mosqueId, $window),
                $this->laporanKeuangan($mosqueId, $window),
                $this->laporanKasMasjid($mosqueId, $window),
                $this->laporanInfaqSodaqoh($mosqueId, $window),
                $this->laporanAgenda($mosqueId, $window),
                $this->laporanKepengurusan($mosqueId, $window),
                $this->laporanRelawan($mosqueId, $window),
                $this->laporanInventaris($mosqueId, $window),
                $this->laporanSurat($mosqueId, $window),
            ]
        );

        // Permintaan AJAX (filter periode/tahun) → kembalikan data JSON.
        if ($request->wantsJson()) {
            return response()->json($laporanData);
        }

        return view('pages.laporan', ['laporanData' => $laporanData]);
    }

    // ============================================================
    // EKSPOR: Cetak, PDF, Excel
    // ============================================================

    public function printReport(int $id, Request $request)
    {
        $mosqueId = $this->mosqueId;
        $report = $this->buildReport($id, $mosqueId, $this->buildWindow($request));
        abort_unless($report, 404);

        return view('pages.laporan-print', [
            'reports' => [$report],
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => true,
        ]);
    }

    public function exportPdf(int $id, Request $request)
    {
        $mosqueId = $this->mosqueId;
        $report = $this->buildReport($id, $mosqueId, $this->buildWindow($request));
        abort_unless($report, 404);

        $pdf = Pdf::loadView('pages.laporan-print', [
            'reports' => [$report],
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => false,
        ])->setPaper('a4');

        return $pdf->download('laporan-' . Str::slug($report['judul']) . '.pdf');
    }

    public function exportExcel(int $id, Request $request)
    {
        $mosqueId = $this->mosqueId;
        $report = $this->buildReport($id, $mosqueId, $this->buildWindow($request));
        abort_unless($report, 404);

        return $this->streamXlsx(
            [$report],
            'laporan-' . Str::slug($report['judul']) . '.xlsx'
        );
    }

    public function exportExcelAll(Request $request)
    {
        return $this->streamXlsx($this->allReports($this->buildWindow($request)), 'laporan-semua.xlsx');
    }

    public function exportPdfAll(Request $request)
    {
        $mosqueId = $this->mosqueId;

        $pdf = Pdf::loadView('pages.laporan-print', [
            'reports' => $this->allReports($this->buildWindow($request)),
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => false,
        ])->setPaper('a4');

        return $pdf->download('laporan-semua.pdf');
    }

    // ============================================================
    // FILTER PERIODE & TAHUN
    // ============================================================

    /**
     * Hitung rentang tanggal dari filter periode (Harian/Mingguan/Bulanan)
     * dan tahun. Kembalikan null bila tidak ada filter (semua data).
     *
     * @return array{from: Carbon, to: Carbon}|null
     */
    private function buildWindow(Request $request): ?array
    {
        $periode = $request->query('periode');
        $tahun = $request->query('tahun');

        $from = null;
        $to = null;

        if ($periode === 'Harian') {
            $from = now()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($periode === 'Mingguan') {
            $from = now()->startOfWeek();
            $to = now()->endOfWeek();
        } elseif ($periode === 'Bulanan') {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        }

        // Tahun harus angka 4 digit (mis. 2026); selain itu diabaikan demi
        // menghindari nilai ekstrem yang bisa membuat Carbon melempar exception.
        if ($tahun !== null && $tahun !== '' && ctype_digit($tahun) && strlen($tahun) === 4) {
            $yearFrom = Carbon::create((int) $tahun, 1, 1)->startOfYear();
            $yearTo = Carbon::create((int) $tahun, 12, 31)->endOfYear();

            if ($from === null) {
                $from = $yearFrom;
                $to = $yearTo;
            } else {
                // Periode dibatasi ke dalam tahun terpilih (intersection)
                $from = $from->lt($yearFrom) ? $yearFrom : $from;
                $to = $to->gt($yearTo) ? $yearTo : $to;
            }
        }

        return $from === null ? null : ['from' => $from, 'to' => $to];
    }

    /** Terapkan window tanggal ke query bila ada filter. */
    private function applyWindow($query, string $column, ?array $window)
    {
        if ($window === null) {
            return $query;
        }

        return $query->whereBetween($column, [$window['from'], $window['to']]);
    }

    private function periodLabel(?array $window): string
    {
        if ($window === null) {
            return 'Periode: Semua data hingga saat ini';
        }

        // Kombinasi periode (mis. Bulanan tahun ini) + tahun lampau bisa
        // menghasilkan from > to — artinya tidak ada data pada rentang itu.
        if ($window['from']->gt($window['to'])) {
            return 'Periode: Tidak ada data pada rentang tersebut';
        }

        return 'Periode: ' . $window['from']->translatedFormat('d M Y')
            . ' - ' . $window['to']->translatedFormat('d M Y');
    }

    /** Kumpulan semua laporan (id 1-9) untuk ekspor gabungan. */
    private function allReports(?array $window = null): array
    {
        $mosqueId = $this->mosqueId;

        return collect(range(1, 9))
            ->map(fn ($id) => $this->buildReport($id, $mosqueId, $window))
            ->filter()
            ->values()
            ->all();
    }

    /** Ambil data satu laporan berdasarkan id (1-9), lengkap dengan label. */
    private function buildReport(int $id, int $mosqueId, ?array $window = null): ?array
    {
        $report = match ($id) {
            1 => $this->laporanJemaah($mosqueId, $window),
            2 => $this->laporanKeuangan($mosqueId, $window),
            3 => $this->laporanKasMasjid($mosqueId, $window),
            4 => $this->laporanInfaqSodaqoh($mosqueId, $window),
            5 => $this->laporanAgenda($mosqueId, $window),
            6 => $this->laporanKepengurusan($mosqueId, $window),
            7 => $this->laporanRelawan($mosqueId, $window),
            8 => $this->laporanInventaris($mosqueId, $window),
            9 => $this->laporanSurat($mosqueId, $window),
            default => null,
        };

        return $report === null ? null : $this->injectLabels($report, $window);
    }

    private function injectLabels(array $report, ?array $window): array
    {
        $report['dibuat'] = 'Dibuat: ' . now()->translatedFormat('d M Y');
        $report['periode'] = $this->periodLabel($window);

        return $report;
    }

    /**
     * Tulis satu atau banyak laporan ke file .xlsx lalu kirim sebagai unduhan.
     * Setiap laporan menjadi satu sheet.
     */
    private function streamXlsx(array $reports, string $fileName)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'laporan_');
        $path = ($tmp === false ? sys_get_temp_dir() . '/laporan_' . uniqid() : $tmp) . '.xlsx';

        try {
            $writer = new Writer();
            $writer->openToFile($path);

            $titleStyle = (new Style())
                ->setFontBold()
                ->setFontSize(13)
                ->setFontColor('0F766E');

            $headerStyle = (new Style())
                ->setFontBold()
                ->setBackgroundColor('E6F4F2');

            $lastIndex = count($reports) - 1;

            foreach ($reports as $i => $report) {
                $sheet = $writer->getCurrentSheet();
                $sheet->setName($this->sheetName($report['judul']));

                // Kop laporan
                $writer->addRow(Row::fromValues([strtoupper($report['judul'])], $titleStyle));
                $writer->addRow(Row::fromValues([$report['updated']]));
                $writer->addRow(Row::fromValues([$report['dibuat']]));
                $writer->addRow(Row::fromValues([$report['periode']]));
                $writer->addRow(Row::fromValues([]));

                // Ringkasan
                foreach ($report['summary'] as $s) {
                    $writer->addRow(Row::fromValues([
                        $this->xlsxSafe($s['label']),
                        $this->xlsxSafe($s['value']),
                    ]));
                }
                $writer->addRow(Row::fromValues([]));

                // Header tabel
                $writer->addRow(Row::fromValues($report['columns'], $headerStyle));

                // Data
                $rows = $report['rows'];
                if (empty($rows)) {
                    $writer->addRow(Row::fromValues(['Belum ada data.']));
                }
                foreach ($rows as $row) {
                    $writer->addRow(Row::fromValues(array_map([$this, 'xlsxSafe'], $row)));
                }

                // Lebar kolom agar mudah dibaca di Excel
                $sheet->setColumnWidth(28, 1, 2, 3, 4, 5);

                if ($i < $lastIndex) {
                    $writer->addNewSheetAndMakeItCurrent();
                }
            }

            $writer->close();
        } catch (\Throwable $e) {
            // Gagal: bersihkan file xlsx yang mungkin parsial
            @unlink($path);
            throw $e;
        } finally {
            if ($tmp !== false) {
                @unlink($tmp);
            }
        }

        return response()->download($path, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function sheetName(string $judul): string
    {
        // Nama sheet maks 31 karakter & tidak boleh mengandung \ / ? * [ ] :
        $name = Str::slug($judul, '_');

        return Str::limit($name, 31, '');
    }

    /** Cegah formula injection: sel diawali =, +, -, @ diawali tanda kutip. */
    private function xlsxSafe($value)
    {
        if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    private function rupiah($angka): string
    {
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }

    private function laporanJemaah($mosqueId, ?array $window): array
    {
        $q = Jamaah::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'tanggal_bergabung', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('tanggal_bergabung')->limit(3)->get()
            ->map(fn ($j, $i) => [$i + 1, $j->nama, $j->status_jamaah, $j->tanggal_bergabung?->translatedFormat('d M Y') ?? '-'])
            ->toArray();

        return [
            'id' => 1,
            'judul' => 'Data Jemaah',
            'kategori' => 'Jemaah',
            'icon' => 'fa-users',
            'desc' => 'Informasi dan statistik jamaah secara lengkap.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Data',
            'count' => (clone $qW)->count(),
            'summary' => [
                ['label' => $filtered ? 'Jemaah Baru' : 'Total Jemaah', 'value' => (clone $qW)->count()],
                ['label' => $filtered ? 'Jemaah Aktif (Baru)' : 'Jemaah Aktif', 'value' => (clone $qW)->where('status_jamaah', 'Aktif')->count()],
            ],
            'columns' => ['No.', 'Nama', 'Status', 'Tanggal Bergabung'],
            'rows' => $rows,
        ];
    }

    private function laporanKeuangan($mosqueId, ?array $window): array
    {
        $kas = KasTransaction::where('mosque_id', $mosqueId);
        $donasi = Donasi::where('mosque_id', $mosqueId);
        $kasW = $this->applyWindow((clone $kas), 'tanggal', $window);
        $donasiW = $this->applyWindow((clone $donasi), 'tanggal', $window);
        $filtered = $window !== null;

        $rows = [];
        $end = $window !== null ? $window['to'] : now();
        $fromMonth = $window !== null ? $window['from']->copy()->startOfMonth() : null;
        for ($i = 2; $i >= 0; $i--) {
            $bulan = $end->copy()->subMonths($i)->startOfMonth();
            if ($fromMonth !== null && $bulan->lt($fromMonth)) {
                continue;
            }
            $pemasukan = (clone $kasW)->whereMonth('tanggal', $bulan->month)->whereYear('tanggal', $bulan->year)->sum('pemasukan');
            $pengeluaran = (clone $kasW)->whereMonth('tanggal', $bulan->month)->whereYear('tanggal', $bulan->year)->sum('pengeluaran');
            $rows[] = [$bulan->translatedFormat('M Y'), $this->rupiah($pemasukan), $this->rupiah($pengeluaran)];
        }

        return [
            'id' => 2,
            'judul' => 'Laporan Keuangan',
            'kategori' => 'Keuangan',
            'icon' => 'fa-chart-pie',
            'desc' => 'Rincian keuangan tahunan secara komprehensif.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Transaksi',
            'count' => (clone $kasW)->count() + (clone $donasiW)->count(),
            'summary' => [
                ['label' => $filtered ? 'Pemasukan (Periode)' : 'Total Pemasukan', 'value' => $this->rupiah((clone $kasW)->sum('pemasukan') + (clone $donasiW)->where('status', 'Berhasil')->sum('nominal'))],
                ['label' => $filtered ? 'Pengeluaran (Periode)' : 'Total Pengeluaran', 'value' => $this->rupiah((clone $kasW)->sum('pengeluaran'))],
            ],
            'columns' => ['Bulan', 'Pemasukan', 'Pengeluaran'],
            'rows' => $rows,
        ];
    }

    private function laporanKasMasjid($mosqueId, ?array $window): array
    {
        $q = KasTransaction::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'tanggal', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('tanggal')->limit(3)->get()
            ->map(fn ($k) => [
                $k->tanggal->format('d/m'),
                $k->jenis,
                $k->pemasukan > 0 ? '+' . $this->rupiah($k->pemasukan)
                    : ($k->pengeluaran > 0 ? '-' . $this->rupiah($k->pengeluaran) : $this->rupiah(0)),
            ])->toArray();

        return [
            'id' => 3,
            'judul' => 'Kas Masjid',
            'kategori' => 'Keuangan',
            'icon' => 'fa-wallet',
            'desc' => 'Arus kas harian dan bulanan secara detail.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Transaksi',
            'count' => (clone $qW)->count(),
            'summary' => $filtered ? [
                ['label' => 'Pemasukan (Periode)', 'value' => $this->rupiah((clone $qW)->sum('pemasukan'))],
                ['label' => 'Pengeluaran (Periode)', 'value' => $this->rupiah((clone $qW)->sum('pengeluaran'))],
            ] : [
                ['label' => 'Saldo Kas', 'value' => $this->rupiah((clone $qW)->sum('pemasukan') - (clone $qW)->sum('pengeluaran'))],
                ['label' => 'Transaksi Bulan Ini', 'value' => (clone $qW)->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count()],
            ],
            'columns' => ['Tanggal', 'Jenis', 'Nominal'],
            'rows' => $rows,
        ];
    }

    private function laporanInfaqSodaqoh($mosqueId, ?array $window): array
    {
        $q = Donasi::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'tanggal', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('tanggal')->limit(3)->get()
            ->map(fn ($d) => [$d->donatur, $d->jenis, $this->rupiah($d->nominal)])
            ->toArray();

        return [
            'id' => 4,
            'judul' => 'Infaq & Sodaqoh',
            'kategori' => 'Keuangan',
            'icon' => 'fa-hand-holding-heart',
            'desc' => 'Statistik penerimaan dan penggunaan secara detail.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Koleksi',
            'count' => (clone $qW)->count(),
            'summary' => $filtered ? [
                ['label' => 'Donasi (Periode)', 'value' => $this->rupiah((clone $qW)->where('status', 'Berhasil')->sum('nominal'))],
                ['label' => 'Donatur (Periode)', 'value' => (clone $qW)->distinct('donatur')->count('donatur')],
            ] : [
                ['label' => 'Total Donasi Bulan Ini', 'value' => $this->rupiah((clone $qW)->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->where('status', 'Berhasil')->sum('nominal'))],
                ['label' => 'Donatur Aktif', 'value' => (clone $qW)->distinct('donatur')->count('donatur')],
            ],
            'columns' => ['Donatur', 'Jenis', 'Nominal'],
            'rows' => $rows,
        ];
    }

    private function laporanAgenda($mosqueId, ?array $window): array
    {
        $q = Kegiatan::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'tanggal', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('tanggal')->limit(3)->get()
            ->map(fn ($k) => [$k->tanggal->translatedFormat('d M'), $k->nama, $k->status])
            ->toArray();

        return [
            'id' => 5,
            'judul' => 'Agenda',
            'kategori' => 'Kegiatan',
            'icon' => 'fa-calendar-days',
            'desc' => 'Ringkasan agenda dan kegiatan yang akan datang.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Acara',
            'count' => (clone $qW)->count(),
            'summary' => $filtered ? [
                ['label' => 'Agenda (Periode)', 'value' => (clone $qW)->count()],
                ['label' => 'Agenda Selesai (Periode)', 'value' => (clone $qW)->where('status', 'Selesai')->count()],
            ] : [
                ['label' => 'Agenda Bulan Ini', 'value' => (clone $qW)->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count()],
                ['label' => 'Agenda Selesai', 'value' => (clone $qW)->where('status', 'Selesai')->count()],
            ],
            'columns' => ['Tanggal', 'Nama Agenda', 'Status'],
            'rows' => $rows,
        ];
    }

    private function laporanKepengurusan($mosqueId, ?array $window): array
    {
        // Struktur kepengurusan bersifat "kondisi saat ini" — tidak ada kolom
        // tanggal, jadi filter periode/tahun tidak mengubah data ini.
        $q = Jabatan::where('mosque_id', $mosqueId);

        $rows = (clone $q)->with('jamaah')->orderBy('urutan')->limit(3)->get()
            ->map(fn ($j) => [$j->nama, $j->jamaah->nama ?? '-', $j->jamaah_id ? 'Terisi' : 'Kosong'])
            ->toArray();

        return [
            'id' => 6,
            'judul' => 'Kepengurusan',
            'kategori' => 'Kegiatan',
            'icon' => 'fa-sitemap',
            'desc' => 'Struktur pengurus dan status jabatan terkini.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Jabatan',
            'count' => (clone $q)->count(),
            'summary' => [
                ['label' => 'Jabatan Terisi', 'value' => (clone $q)->whereNotNull('jamaah_id')->count()],
                ['label' => 'Jabatan Kosong', 'value' => (clone $q)->whereNull('jamaah_id')->count()],
            ],
            'columns' => ['Jabatan', 'Nama', 'Status'],
            'rows' => $rows,
        ];
    }

    private function laporanRelawan($mosqueId, ?array $window): array
    {
        // kegiatan_relawan gak punya kolom mosque_id langsung, jadi kita saring lewat relasi kegiatan
        $q = KegiatanRelawan::whereHas('kegiatan', fn ($k) => $k->where('mosque_id', $mosqueId));
        $qW = $this->applyWindow((clone $q), 'kegiatan_relawan.created_at', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->with('kegiatan')->latest()->limit(3)->get()
            ->map(fn ($r) => [$r->nama, $r->kegiatan->nama ?? '-', $r->telepon ?? '-'])
            ->toArray();

        return [
            'id' => 7,
            'judul' => 'Laporan Relawan',
            'kategori' => 'Kegiatan',
            'icon' => 'fa-hands-helping',
            'desc' => 'Partisipasi relawan dalam kegiatan masjid.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Data',
            'count' => (clone $qW)->count(),
            'summary' => [
                ['label' => $filtered ? 'Relawan (Periode)' : 'Relawan Terdaftar', 'value' => (clone $qW)->distinct('nama')->count('nama') . ' Orang'],
                ['label' => $filtered ? 'Partisipasi (Periode)' : 'Total Partisipasi', 'value' => (clone $qW)->count() . ' Kali'],
            ],
            'columns' => ['Nama', 'Kegiatan', 'Telepon'],
            'rows' => $rows,
        ];
    }

    private function laporanInventaris($mosqueId, ?array $window): array
    {
        $q = Inventaris::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'created_at', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('created_at')->limit(3)->get()
            ->map(fn ($i) => [$i->nama, $i->kategori ?? '-', $i->kondisi])
            ->toArray();

        return [
            'id' => 8,
            'judul' => 'Inventaris',
            'kategori' => 'Aset',
            'icon' => 'fa-boxes-stacked',
            'desc' => 'Daftar aset dan perlengkapan masjid.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Item',
            'count' => (clone $qW)->count(),
            'summary' => $filtered ? [
                ['label' => 'Item (Periode)', 'value' => (clone $qW)->count()],
                ['label' => 'Kondisi Baik (Periode)', 'value' => (clone $qW)->where('kondisi', 'Baik')->count()],
            ] : [
                ['label' => 'Total Item', 'value' => (clone $qW)->count()],
                ['label' => 'Kondisi Baik', 'value' => (clone $qW)->where('kondisi', 'Baik')->count()],
            ],
            'columns' => ['Item', 'Kategori', 'Kondisi'],
            'rows' => $rows,
        ];
    }

    private function laporanSurat($mosqueId, ?array $window): array
    {
        $q = Surat::where('mosque_id', $mosqueId);
        $qW = $this->applyWindow((clone $q), 'tanggal', $window);
        $filtered = $window !== null;

        $rows = (clone $qW)->orderByDesc('tanggal')->limit(3)->get()
            ->map(fn ($s) => [$s->nomor, $s->subjek, $s->status])
            ->toArray();

        return [
            'id' => 9,
            'judul' => 'Surat Resmi',
            'kategori' => 'Aset',
            'icon' => 'fa-envelope-open-text',
            'desc' => 'Arsip surat masuk dan keluar.',
            'updated' => 'Diperbarui: ' . now()->translatedFormat('d M Y'),
            'countLabel' => 'Surat',
            'count' => (clone $qW)->count(),
            'summary' => $filtered ? [
                ['label' => 'Surat (Periode)', 'value' => (clone $qW)->count()],
                ['label' => 'Terkirim (Periode)', 'value' => (clone $qW)->where('status', 'Terkirim')->count()],
            ] : [
                ['label' => 'Terkirim', 'value' => (clone $qW)->where('status', 'Terkirim')->count()],
                ['label' => 'Draft', 'value' => (clone $qW)->where('status', 'Draft')->count()],
            ],
            'columns' => ['No. Surat', 'Subjek', 'Status'],
            'rows' => $rows,
        ];
    }
}
 