<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Support\SiteContext;

class PublicPengumumanController extends Controller
{
    public function index()
    {
        $list = Pengumuman::where('mosque_id', SiteContext::mosqueId())
            ->where('status', 'Aktif')
            ->orderByDesc('tanggal')
            ->get();

        return view('pages.public.pengumuman', ['list' => $list]);
    }
}