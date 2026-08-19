<?php

namespace App\Http\Controllers;

use App\Models\Mosque;
use App\Services\LaporanBuilder;
use App\Services\LaporanExportService;
use App\Support\Concerns\HasMosqueContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    use HasMosqueContext;

    public function index(Request $request, LaporanBuilder $laporan)
    {
        $mosqueId = $this->mosqueId;
        $window = $laporan->buildWindow($request);

        $laporanData = array_map(
            fn ($l) => $laporan->buildReport($l, $mosqueId, $window),
            range(1, 9)
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

    public function printReport(int $id, Request $request, LaporanBuilder $laporan)
    {
        $mosqueId = $this->mosqueId;
        $report = $laporan->buildReport($id, $mosqueId, $laporan->buildWindow($request));
        abort_unless($report, 404);

        return view('pages.laporan-print', [
            'reports' => [$report],
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => true,
        ]);
    }

    public function exportPdf(int $id, Request $request, LaporanBuilder $laporan)
    {
        $mosqueId = $this->mosqueId;
        $report = $laporan->buildReport($id, $mosqueId, $laporan->buildWindow($request));
        abort_unless($report, 404);

        $pdf = Pdf::loadView('pages.laporan-print', [
            'reports' => [$report],
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => false,
        ])->setPaper('a4');

        return $pdf->download('laporan-'.Str::slug($report['judul']).'.pdf');
    }

    public function exportExcel(int $id, Request $request, LaporanBuilder $laporan, LaporanExportService $export)
    {
        $mosqueId = $this->mosqueId;
        $report = $laporan->buildReport($id, $mosqueId, $laporan->buildWindow($request));
        abort_unless($report, 404);

        return $export->streamXlsx(
            [$report],
            'laporan-'.Str::slug($report['judul']).'.xlsx'
        );
    }

    public function exportExcelAll(Request $request, LaporanBuilder $laporan, LaporanExportService $export)
    {
        return $export->streamXlsx(
            $laporan->allReports($this->mosqueId, $laporan->buildWindow($request)),
            'laporan-semua.xlsx'
        );
    }

    public function exportPdfAll(Request $request, LaporanBuilder $laporan)
    {
        $mosqueId = $this->mosqueId;

        $pdf = Pdf::loadView('pages.laporan-print', [
            'reports' => $laporan->allReports($mosqueId, $laporan->buildWindow($request)),
            'mosque' => Mosque::find($mosqueId),
            'autoPrint' => false,
        ])->setPaper('a4');

        return $pdf->download('laporan-semua.pdf');
    }
}
