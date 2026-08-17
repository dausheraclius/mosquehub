<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Support\Concerns\HasMosqueContext;

class JadwalKegiatanController extends Controller
{
    use HasMosqueContext;
    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $filters = $this->filters($request);

        $query = Kegiatan::where('mosque_id', $this->mosqueId)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        if ($filters['search'] !== null) {
            $keyword = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', $keyword)
                    ->orWhere('kategori', 'like', $keyword)
                    ->orWhere('lokasi', 'like', $keyword)
                    ->orWhere('pemateri', 'like', $keyword)
                    ->orWhere('pj', 'like', $keyword);
            });
        }
        if ($filters['tanggal'] !== null) {
            $query->whereDate('tanggal', $filters['tanggal']);
        }
        if ($filters['kategori'] !== null) {
            $query->where('kategori', $filters['kategori']);
        }
        if ($filters['lokasi'] !== null) {
            $query->where('lokasi', $filters['lokasi']);
        }
        if ($filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        // Maksimal 10 agenda per halaman.
        $kegiatans = $query->paginate(self::PER_PAGE)->withQueryString();

        // Data pendukung (statistik & sidebar) — dihitung tanpa memuat semua baris.
        $allQuery = Kegiatan::where('mosque_id', $this->mosqueId);

        $stats = [
            'total' => (clone $allQuery)->count(),
            'berlangsung' => (clone $allQuery)->where('status', 'Berlangsung')->count(),
            'akanDatang' => (clone $allQuery)->where('status', 'Akan Datang')->count(),
            'selesai' => (clone $allQuery)->where('status', 'Selesai')->count(),
        ];

        $kegiatanBerikutnya = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereDate('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->first();

        $besok = now()->addDay()->toDateString();
        $jadwalBesok = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereDate('tanggal', $besok)
            ->orderBy('jam_mulai')
            ->take(3)
            ->get();

        $mingguIni = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Opsi dropdown Kategori & Lokasi — diambil dari seluruh data
        // (bukan hanya halaman yang sedang aktif) biar opsinya stabil.
        $kategoriOptions = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereNotNull('kategori')->where('kategori', '!=', '')
            ->distinct()->orderBy('kategori')->pluck('kategori');
        $lokasiOptions = Kegiatan::where('mosque_id', $this->mosqueId)
            ->whereNotNull('lokasi')->where('lokasi', '!=', '')
            ->distinct()->orderBy('lokasi')->pluck('lokasi');

        $timelineData = [
            'agendaList' => $kegiatans->map(fn ($k) => $this->toArray($k)),
            'isEmpty' => $kegiatans->isEmpty(),
            'paginator' => $kegiatans,
            'pageWindow' => $this->pageWindow($kegiatans),
            'hasActiveFilter' => collect($filters)->contains(fn ($v) => $v !== null),
        ];

        // Permintaan AJAX (filter / ganti halaman) — cukup kirim potongan timeline.
        if ($request->boolean('partial')) {
            return view('pages.kegiatan.partials.jadwal-timeline', $timelineData);
        }

        return view('pages.kegiatan.jadwal', array_merge($timelineData, [
            'riwayatSelesai' => (clone $allQuery)->where('status', 'Selesai')->orderBy('tanggal')->get(),
            'stats' => $stats,
            'kegiatanBerikutnya' => $kegiatanBerikutnya,
            'jadwalBesok' => $jadwalBesok,
            'mingguIni' => $mingguIni,
            'kategoriOptions' => $kategoriOptions,
            'lokasiOptions' => $lokasiOptions,
            'filters' => $filters,
        ]));
    }

    private function filters(Request $request): array
    {
        return [
            'search' => $request->filled('search') ? trim($request->input('search')) : null,
            'tanggal' => $request->filled('tanggal') ? $request->input('tanggal') : null,
            'kategori' => $request->filled('kategori') ? $request->input('kategori') : null,
            'lokasi' => $request->filled('lokasi') ? $request->input('lokasi') : null,
            'status' => $request->filled('status') ? $this->statusFromSlug($request->input('status')) : null,
        ];
    }

    private function statusFromSlug(string $slug): ?string
    {
        return match ($slug) {
            'berlangsung' => 'Berlangsung',
            'akan-datang' => 'Akan Datang',
            'selesai' => 'Selesai',
            default => null,
        };
    }

    /**
     * Nomor halaman yang ditampilkan: 1 … (seputar halaman aktif) … halaman terakhir.
     * Nilai '...' dipakai sebagai penanda lompatan.
     */
    private function pageWindow($paginator): array
    {
        $last = $paginator->lastPage();
        $current = $paginator->currentPage();
        if ($last <= 7) {
            return range(1, $last);
        }

        $pages = [1];
        $start = max(2, $current - 2);
        $end = min($last - 1, $current + 2);

        if ($start > 2) {
            $pages[] = '...';
        }
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        if ($end < $last - 1) {
            $pages[] = '...';
        }
        $pages[] = $last;

        return $pages;
    }

    private function toArray(Kegiatan $k): array
    {
        return [
            'id' => $k->id,
            'tanggal' => $k->tanggal->format('Y-m-d'),
            'hari' => $this->hariIndonesia($k->tanggal->format('l')),
            'tanggalLabel' => $this->tanggalLabel($k->tanggal),
            'nama' => $k->nama,
            'lokasi' => $k->lokasi,
            'lokasiSlug' => $k->lokasi ? Str::slug($k->lokasi) : '',
            'pemateri' => $k->pemateri ?: $k->pj,
            'jamMulai' => $k->jam_mulai ? substr($k->jam_mulai, 0, 5) : '-',
            'jamSelesai' => $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '-',
            'jamMulaiDot' => $k->jam_mulai ? str_replace(':', '.', substr($k->jam_mulai, 0, 5)) : '-',
            'kategoriSlug' => Str::slug($k->kategori),
            'kategori' => $k->kategori,
            'statusSlug' => Str::slug($k->status),
            'status' => $k->status,
            'deskripsi' => $k->deskripsi,
            'peserta' => $k->peserta,
        ];
    }

    private function hariIndonesia(string $day): string
    {
        return [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ][$day] ?? $day;
    }

    private function tanggalLabel($date): string
    {
        $bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        return $date->format('d') . ' ' . $bulan[$date->format('m')] . ' ' . $date->format('Y');
    }
}
