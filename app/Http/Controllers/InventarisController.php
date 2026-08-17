<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Support\Concerns\HasMosqueContext;
use App\Support\MediaHelper;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    use HasMosqueContext;

    public function index()
    {
        $inventarisList = Inventaris::where('mosque_id', $this->mosqueId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => $this->toArray($i));

        return view('pages.inventaris', ['inventarisList' => $inventarisList]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['mosque_id'] = $this->mosqueId;
        $validated['qty'] = $validated['qty'] ?? '1';

        $total = Inventaris::where('mosque_id', $this->mosqueId)->count();
        $validated['kode'] = 'MOSQ-NEW-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);

        $item = Inventaris::create($validated);

        if ($request->hasFile('gambar')) {
            $item->addMedia($request->file('gambar'))->toMediaCollection('gambar');
        }

        return response()->json($this->toArray($item));
    }

    public function update(Request $request, Inventaris $inventari)
    {
        $validated = $request->validate($this->rules());

        $inventari->update($validated);

        if ($request->hasFile('gambar')) {
            $inventari->clearMediaCollection('gambar');
            $inventari->addMedia($request->file('gambar'))->toMediaCollection('gambar');
        }

        return response()->json($this->toArray($inventari));
    }

    public function destroy(Inventaris $inventari)
    {
        $inventari->delete();

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'kondisi' => 'required|in:Baik,Perlu Perbaikan,Rusak,Nonaktif',
            'qty' => 'nullable|string|max:50',
            'sumber' => 'required|in:Beli,Waqaf',
            'tgl_beli' => 'nullable|date',
            'harga' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'gambar' => 'nullable|image|max:5120',
        ];
    }

    private function toArray(Inventaris $item): array
    {
        return [
            'id' => $item->id,
            'nama' => $item->nama,
            'kategori' => $item->kategori,
            'lokasi' => $item->lokasi,
            'kondisi' => $item->kondisi,
            'qty' => $item->qty,
            'sumber' => $item->sumber,
            'kode' => $item->kode,
            'tglBeli' => $item->tgl_beli?->format('d M Y'),
            'tglBeliIso' => $item->tgl_beli?->format('Y-m-d'),
            'harga' => $item->harga,
            'catatan' => $item->catatan,
            'gambar' => MediaHelper::relativeUrl($item->getFirstMediaUrl('gambar') ?: null),
        ];
    }
}