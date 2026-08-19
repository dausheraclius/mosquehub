<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Support\SiteContext;

class PublicPengurusController extends Controller
{
    public function index()
    {
        $mosqueId = SiteContext::mosqueId();

        $jabatans = Jabatan::forMosque($mosqueId)
            ->with('jamaah')
            ->orderBy('urutan')
            ->get();

        $items = $jabatans->map(fn ($j) => [
            'nama' => $j->nama,
            'parent' => $j->parent_id ? optional($jabatans->firstWhere('id', $j->parent_id))->nama : null,
            'pengurus' => $j->jamaah?->nama ?: null,
        ]);

        $grouped = $items->groupBy('parent')->values();

        return view('pages.public.pengurus', [
            'items' => $items,
            'grouped' => $grouped,
        ]);
    }
}
