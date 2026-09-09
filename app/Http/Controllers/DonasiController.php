<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonasiResource;
use App\Models\Donasi;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    use HasMosqueContext;

    private const REQUIRED_STRING_255 = 'required|string|max:255';

    public function storeDonasi(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['mosque_id'] = $this->mosqueId;

        $donasi = Donasi::create($validated);

        return DonasiResource::make($donasi);
    }

    public function updateDonasi(Request $request, $id)
    {
        $donasi = Donasi::forMosque()->findOrFail($id);

        $validated = $request->validate($this->rules());

        $donasi->update($validated);

        return DonasiResource::make($donasi);
    }

    public function destroyDonasi($id)
    {
        $donasi = Donasi::forMosque()->findOrFail($id);
        $donasi->delete();

        return response()->json(['message' => 'Donasi dihapus']);
    }

    private function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'tanggal_akhir' => 'nullable|date',
            'donatur' => self::REQUIRED_STRING_255,
            'kategori' => "required|in:Zakat,Zakat Fitrah,Zakat Maal,Infaq,Infaq Jumat,Infaq Harian,Sodaqoh,Sodaqoh Dhuafa,Sodaqoh Anak Yatim,Sodaqoh Bencana,Wakaf,Wakaf Uang,Wakaf Tanah,Wakaf Bangunan,Wakaf Al-Qur'an,Donasi,Donasi Bencana,Donasi Pendidikan,Donasi Kesehatan,Donasi Umum",
            'jenis' => self::REQUIRED_STRING_255,
            'tipe' => 'required|in:Uang,Barang',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'metode' => 'nullable|string|max:100',
            'petugas' => self::REQUIRED_STRING_255,
            'status' => 'required|in:Berhasil,Pending',
        ];
    }
}
