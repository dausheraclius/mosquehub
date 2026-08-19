<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\Jabatan;
use App\Models\Kegiatan;
use App\Models\KegiatanRelawan;
use App\Models\Pengumuman;
use App\Models\Surat;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

use App\Support\Concerns\HasMosqueContext;

class ExportDataController extends Controller
{
    use HasMosqueContext;
    // ============================================================
    // EKSPOR PER MODUL
    // ============================================================

    public function pengumuman()
    {
        $rows = Pengumuman::forMosque()->orderByDesc('tanggal')->get()
            ->map(fn ($p) => [$p->judul, $p->kategori ?? '-', $p->status, $p->tanggal?->format('d/m/Y') ?? '-', $p->isi ?? '']);

        return $this->streamExcel('pengumuman', ['Judul', 'Kategori', 'Status', 'Tanggal', 'Isi'], $rows);
    }

    public function surat()
    {
        $rows = Surat::forMosque()->orderByDesc('tanggal')->get()
            ->map(fn ($s) => [$s->nomor, $s->subjek, $s->jenis ?? '-', $s->status, $s->tanggal?->format('d/m/Y') ?? '-', $s->kepada ?? '-', $s->isi ?? '']);

        return $this->streamExcel('surat', ['Nomor', 'Subjek', 'Jenis', 'Status', 'Tanggal', 'Kepada', 'Isi'], $rows);
    }

    public function inventaris()
    {
        $rows = Inventaris::forMosque()->orderBy('nama')->get()
            ->map(fn ($i) => [$i->nama, $i->kode ?? '-', $i->kategori ?? '-', $i->lokasi ?? '-', $i->kondisi, $i->qty, $i->sumber ?? '-', $i->tgl_beli?->format('d/m/Y') ?? '-', $i->harga ?? 0, $i->catatan ?? '']);

        return $this->streamExcel('inventaris', ['Nama', 'Kode', 'Kategori', 'Lokasi', 'Kondisi', 'Qty', 'Sumber', 'Tgl Beli', 'Harga', 'Catatan'], $rows);
    }

    public function agenda()
    {
        $rows = Kegiatan::forMosque()->orderByDesc('tanggal')->get()
            ->map(fn ($k) => [
                $k->tanggal?->format('d/m/Y') ?? '-',
                $k->nama,
                $k->kategori ?? '-',
                $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '-',
                $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '-',
                $k->lokasi ?? '-',
                $k->pemateri ?? '-',
                $k->pj ?? '-',
                $k->peserta ?? '-',
                $k->status ?? '-',
            ]);

        return $this->streamExcel('agenda', ['Tanggal', 'Nama', 'Kategori', 'Jam Mulai', 'Jam Selesai', 'Lokasi', 'Pemateri', 'PJ', 'Peserta', 'Status'], $rows);
    }

    public function jadwal()
    {
        $rows = Kegiatan::forMosque()
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn ($k) => [
                $k->nama,
                $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '-',
                $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '-',
                $k->kategori ?? '-',
                $k->lokasi ?? '-',
                $k->status ?? '-',
            ]);

        return $this->streamExcel('jadwal-kegiatan-' . now()->format('Y-m-d'), ['Nama', 'Jam Mulai', 'Jam Selesai', 'Kategori', 'Lokasi', 'Status'], $rows);
    }

    public function kepengurusan()
    {
        $rows = Jabatan::forMosque()
            ->with('jamaah')
            ->orderBy('urutan')
            ->get()
            ->map(fn ($j) => [$j->nama, $j->jamaah?->nama ?? 'Kosong', $j->jamaah_id ? 'Terisi' : 'Belum terisi']);

        return $this->streamExcel('kepengurusan', ['Jabatan', 'Pengurus', 'Status'], $rows);
    }

    public function relawan()
    {
        $rows = KegiatanRelawan::whereHas('kegiatan', fn ($q) => $q->forMosque())
            ->with('kegiatan')
            ->latest()
            ->get()
            ->map(fn ($r) => [$r->nama, $r->telepon ?? '-', $r->kegiatan?->nama ?? '-', $r->kegiatan?->tanggal?->format('d/m/Y') ?? '-']);

        return $this->streamExcel('relawan', ['Nama', 'Telepon', 'Kegiatan', 'Tanggal'], $rows);
    }

    public function jadwalPetugas()
    {
        $rows = \App\Models\JadwalPetugasSholat::forMosque()
            ->orderBy('tanggal')
            ->get()
            ->map(fn ($j) => [
                $j->tanggal?->format('d/m/Y') ?? '-',
                $j->sholat,
                $j->khatib ?? '-',
                $j->imam ?? '-',
                $j->muadzin ?? '-',
                $j->keterangan ?? '-',
            ]);

        return $this->streamExcel('jadwal-petugas-sholat', ['Tanggal', 'Sholat', 'Khatib', 'Imam', 'Muadzin', 'Keterangan'], $rows);
    }

    // ============================================================
    // PEMBANTU CSV
    // ============================================================

    /**
     * Kirim data sebagai file Excel (.xlsx) dengan header berwarna teal.
     */
    private function streamExcel(string $label, array $headers, iterable $rows)
    {
        $fileName = Str::slug($label) . '-' . now()->format('Y-m-d') . '.xlsx';

        $headerStyle = (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('0F7670')
            ->setCellAlignment(CellAlignment::CENTER);

        return response()->stream(function () use ($headers, $rows, $headerStyle) {
            $options = new Options();
            $options->setTempFolder(sys_get_temp_dir());

            $writer = new Writer($options);
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($headers, $headerStyle));
            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues(array_values($row)));
            }
            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
