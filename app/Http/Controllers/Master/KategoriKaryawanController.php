<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KategoriKaryawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $query = KategoriKaryawan::query()
            ->with(['parent', 'children'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->orderBy('urutan')
            ->orderBy('nama');

        $items = $query->paginate(12)->withQueryString();

        $totalKategori = KategoriKaryawan::count();
        $totalParent = KategoriKaryawan::whereNull('parent_id')->count();
        $totalChild = KategoriKaryawan::whereNotNull('parent_id')->count();
        $totalActive = KategoriKaryawan::where('is_active', true)->count();

        return view('master.kategori-karyawan.index', compact(
            'items',
            'search',
            'totalKategori',
            'totalParent',
            'totalChild',
            'totalActive'
        ));
    }

    public function create()
    {
        $parents = KategoriKaryawan::query()
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('master.kategori-karyawan.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:kategori_karyawans,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:255', 'unique:kategori_karyawans,kode'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        KategoriKaryawan::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.kategori-karyawan.index')
            ->with('success', 'Kategori karyawan berhasil ditambahkan.');
    }

    public function edit(KategoriKaryawan $kategoriKaryawan)
    {
        $parents = KategoriKaryawan::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $kategoriKaryawan->id)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        return view('master.kategori-karyawan.edit', compact('kategoriKaryawan', 'parents'));
    }

    public function update(Request $request, KategoriKaryawan $kategoriKaryawan)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:kategori_karyawans,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_karyawans', 'kode')->ignore($kategoriKaryawan->id),
            ],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($validated['parent_id'] ?? null) == $kategoriKaryawan->id) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'Parent kategori tidak boleh dirinya sendiri.']);
        }

        $kategoriKaryawan->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'urutan' => $validated['urutan'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.kategori-karyawan.index')
            ->with('success', 'Kategori karyawan berhasil diperbarui.');
    }

    public function destroy(KategoriKaryawan $kategoriKaryawan)
    {
        if ($kategoriKaryawan->children()->exists()) {
            return back()->with('error', 'Kategori ini masih punya subkategori. Hapus atau pindahkan subkategori dulu.');
        }

        if ($kategoriKaryawan->karyawans()->exists()) {
            return back()->with('error', 'Kategori ini masih dipakai data karyawan.');
        }

        $kategoriKaryawan->delete();

        return redirect()
            ->route('master.kategori-karyawan.index')
            ->with('success', 'Kategori karyawan berhasil dihapus.');
    }
}