<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Mosque;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class ProfilMasjidController extends Controller
{
    use HasMosqueContext;
    public function index()
    {
        $mosque = Mosque::firstOrCreate(
            ['id' => $this->mosqueId],
            ['name' => 'Masjid Al-Firdaus', 'status' => 'aktif']
        );

        $pengurus = [
            'ketua' => Jabatan::forMosque()->where('nama', 'Ketua YMBPK')->with('jamaah')->first()?->jamaah?->nama,
            'sekretaris' => Jabatan::forMosque()->where('nama', 'Sekretaris YMBPK')->with('jamaah')->first()?->jamaah?->nama,
            'bendahara' => Jabatan::forMosque()->where('nama', 'Bendahara YMBPK')->with('jamaah')->first()?->jamaah?->nama,
        ];

        return view('pages.pengaturan.profil-masjid', ['mosque' => $mosque, 'pengurus' => $pengurus]);
    }

    public function update(Request $request)
    {
        $mosque = Mosque::findOrFail($this->mosqueId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'established_year' => 'nullable|string|max:4',
            'category' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'maps_link' => 'nullable|string|max:500',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'stempel' => 'nullable|file|max:2048',
            'kop_surat' => 'nullable|file|max:2048',
            'ttd' => 'nullable|file|max:2048',
        ]);

        $mosque->update(collect($validated)->except(['logo', 'stempel', 'kop_surat', 'ttd'])->toArray());

        foreach (['logo', 'stempel', 'kop_surat', 'ttd'] as $field) {
            if ($request->hasFile($field)) {
                $mosque->addMediaFromRequest($field)->toMediaCollection($field);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Hapus logo masjid → tampilan kembali ke ikon default.
     */
    public function destroyLogo()
    {
        $mosque = Mosque::findOrFail($this->mosqueId);
        $mosque->clearMediaCollection('logo');

        return response()->json(['success' => true]);
    }
}
