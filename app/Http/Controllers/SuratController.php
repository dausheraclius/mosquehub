<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuratResource;
use App\Models\Surat;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $suratList = Surat::forMosque()
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($s) => SuratResource::make($s)->resolve());

        return view('pages.surat', ['suratList' => $suratList]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['mosque_id'] = $this->mosqueId;

        $surat = Surat::create($validated);

        if ($request->hasFile('file')) {
            $surat->addMedia($request->file('file'))->toMediaCollection('lampiran');
        }

        return response()->json(SuratResource::make($surat));
    }

    public function update(Request $request, Surat $surat)
    {
        $validated = $request->validate($this->rules());

        $surat->update($validated);

        if ($request->hasFile('file')) {
            $surat->addMedia($request->file('file'))->toMediaCollection('lampiran');
        }

        return response()->json(SuratResource::make($surat));
    }

    public function destroy(Surat $surat)
    {
        $surat->delete();

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'nomor' => 'required|string|max:255',
            'subjek' => 'required|string|max:255',
            'jenis' => 'required|in:Surat Undangan,Sertifikat,Surat Keterangan,Surat Tugas',
            'status' => 'required|in:Draft,Terkirim',
            'tanggal' => 'required|date',
            'kepada' => 'nullable|string|max:255',
            'isi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ];
    }
}