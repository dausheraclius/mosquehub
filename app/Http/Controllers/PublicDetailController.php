<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Pengumuman;
use App\Support\SiteContext;

class PublicDetailController extends Controller
{
    public function kegiatan(int $id)
    {
        $kegiatan = Kegiatan::forMosque(SiteContext::mosqueId())
            ->findOrFail($id);

        return view('pages.public.kegiatan-detail', ['kegiatan' => $kegiatan]);
    }

    public function pengumuman(int $id)
    {
        $pengumuman = Pengumuman::forMosque(SiteContext::mosqueId())
            ->findOrFail($id);

        return view('pages.public.pengumuman-detail', ['pengumuman' => $pengumuman]);
    }
}
