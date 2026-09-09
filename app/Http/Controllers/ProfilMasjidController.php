<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Mosque;
use Illuminate\Http\Request;

use App\Support\Concerns\HasMosqueContext;

class ProfilMasjidController extends Controller
{
    use HasMosqueContext;

    private const NULLABLE_STRING_255 = 'nullable|string|max:255';
    private const NULLABLE_STRING_100 = 'nullable|string|max:100';
    private const NULLABLE_FILE_2048 = 'nullable|file|max:2048';
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
            'short_name' => self::NULLABLE_STRING_100,
            'established_year' => 'nullable|string|max:4',
            'category' => self::NULLABLE_STRING_100,
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => self::NULLABLE_STRING_255,
            'province' => self::NULLABLE_STRING_100,
            'city' => self::NULLABLE_STRING_100,
            'district' => self::NULLABLE_STRING_100,
            'kelurahan' => self::NULLABLE_STRING_100,
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'maps_link' => 'nullable|string|max:500',
            'instagram' => self::NULLABLE_STRING_255,
            'facebook' => self::NULLABLE_STRING_255,
            'youtube' => self::NULLABLE_STRING_255,
            'tiktok' => self::NULLABLE_STRING_255,
            'whatsapp' => self::NULLABLE_STRING_255,
            'logo' => 'nullable|image|max:2048',
            'stempel' => self::NULLABLE_FILE_2048,
            'kop_surat' => self::NULLABLE_FILE_2048,
            'ttd' => self::NULLABLE_FILE_2048,
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
