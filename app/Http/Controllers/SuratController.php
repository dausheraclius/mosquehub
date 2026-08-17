<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Support\Concerns\HasMosqueContext;
use App\Support\MediaHelper;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $suratList = Surat::where('mosque_id', $this->mosqueId)
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($s) => $this->toArray($s));

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

        return response()->json($this->toArray($surat));
    }

    public function update(Request $request, Surat $surat)
    {
        $validated = $request->validate($this->rules());

        $surat->update($validated);

        if ($request->hasFile('file')) {
            $surat->addMedia($request->file('file'))->toMediaCollection('lampiran');
        }

        return response()->json($this->toArray($surat));
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

    private function toArray(Surat $surat): array
    {
        return [
            'id' => $surat->id,
            'nomor' => $surat->nomor,
            'subjek' => $surat->subjek,
            'jenis' => $surat->jenis,
            'status' => $surat->status,
            'tanggal' => $surat->tanggal->format('Y-m-d'),
            'kepada' => $surat->kepada,
            'isi' => $surat->isi,
            'fileUrl' => MediaHelper::relativeUrl($surat->getFirstMediaUrl('lampiran') ?: null),
            'fileName' => $surat->getFirstMedia('lampiran')?->file_name,
        ];
    }
}