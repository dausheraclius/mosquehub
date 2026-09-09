<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventarisResource;
use App\Models\Inventaris;
use App\Support\Concerns\HasMosqueContext;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    use HasMosqueContext;

    private const REQUIRED_STRING_255 = 'required|string|max:255';
    private const NULLABLE_STRING_255 = 'nullable|string|max:255';

    public function index()
    {
        $inventarisList = Inventaris::forMosque()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => InventarisResource::make($i)->resolve());

        return view('pages.inventaris', ['inventarisList' => $inventarisList]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['mosque_id'] = $this->mosqueId;
        $validated['qty'] = $validated['qty'] ?? '1';

        $total = Inventaris::forMosque()->count();
        $validated['kode'] = 'MOSQ-NEW-' . str_pad($total + 1, 3, '0', STR_PAD_LEFT);

        $item = Inventaris::create($validated);

        if ($request->hasFile('gambar')) {
            $item->addMedia($request->file('gambar'))->toMediaCollection('gambar');
        }

        return response()->json(InventarisResource::make($item));
    }

    public function update(Request $request, Inventaris $inventari)
    {
        $validated = $request->validate($this->rules());

        $inventari->update($validated);

        if ($request->hasFile('gambar')) {
            $inventari->clearMediaCollection('gambar');
            $inventari->addMedia($request->file('gambar'))->toMediaCollection('gambar');
        }

        return response()->json(InventarisResource::make($inventari));
    }

    public function destroy(Inventaris $inventari)
    {
        $inventari->delete();

        return response()->json(['success' => true]);
    }

    private function rules(): array
    {
        return [
            'nama' => self::REQUIRED_STRING_255,
            'kategori' => self::NULLABLE_STRING_255,
            'lokasi' => self::NULLABLE_STRING_255,
            'kondisi' => 'required|in:Baik,Perlu Perbaikan,Rusak,Nonaktif',
            'qty' => 'nullable|string|max:50',
            'sumber' => 'required|in:Beli,Waqaf',
            'tgl_beli' => 'nullable|date',
            'harga' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'gambar' => 'nullable|image|max:5120',
        ];
    }
}