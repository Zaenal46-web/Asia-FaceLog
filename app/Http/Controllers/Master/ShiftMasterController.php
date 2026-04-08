<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ShiftMaster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftMasterController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $lintasHari = trim((string) $request->get('lintas_hari', ''));

        $query = ShiftMaster::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($lintasHari !== '', function ($q) use ($lintasHari) {
                if ($lintasHari === 'yes') {
                    $q->where('lintas_hari', true);
                }

                if ($lintasHari === 'no') {
                    $q->where('lintas_hari', false);
                }
            })
            ->orderBy('jam_masuk')
            ->orderBy('nama');

        $items = $query->paginate(12)->withQueryString();

        $totalShift = ShiftMaster::count();
        $totalActive = ShiftMaster::where('is_active', true)->count();
        $totalLintasHari = ShiftMaster::where('lintas_hari', true)->count();
        $totalSabtuAktif = ShiftMaster::where('sabtu_aktif', true)->count();

        return view('master.shift-master.index', compact(
            'items',
            'search',
            'status',
            'lintasHari',
            'totalShift',
            'totalActive',
            'totalLintasHari',
            'totalSabtuAktif'
        ));
    }

    public function create()
    {
        return view('master.shift-master.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:255', 'unique:shift_masters,kode'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'lintas_hari' => ['nullable', 'boolean'],
            'sabtu_aktif' => ['nullable', 'boolean'],
            'minggu_aktif' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ShiftMaster::create([
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'jam_masuk' => $validated['jam_masuk'],
            'jam_pulang' => $validated['jam_pulang'],
            'lintas_hari' => (bool) ($validated['lintas_hari'] ?? false),
            'sabtu_aktif' => (bool) ($validated['sabtu_aktif'] ?? false),
            'minggu_aktif' => (bool) ($validated['minggu_aktif'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.shift-master.index')
            ->with('success', 'Shift master berhasil ditambahkan.');
    }

    public function edit(ShiftMaster $shiftMaster)
    {
        return view('master.shift-master.edit', compact('shiftMaster'));
    }

    public function update(Request $request, ShiftMaster $shiftMaster)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shift_masters', 'kode')->ignore($shiftMaster->id),
            ],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'lintas_hari' => ['nullable', 'boolean'],
            'sabtu_aktif' => ['nullable', 'boolean'],
            'minggu_aktif' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $shiftMaster->update([
            'nama' => trim($validated['nama']),
            'kode' => strtoupper(trim($validated['kode'])),
            'jam_masuk' => $validated['jam_masuk'],
            'jam_pulang' => $validated['jam_pulang'],
            'lintas_hari' => (bool) ($validated['lintas_hari'] ?? false),
            'sabtu_aktif' => (bool) ($validated['sabtu_aktif'] ?? false),
            'minggu_aktif' => (bool) ($validated['minggu_aktif'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.shift-master.index')
            ->with('success', 'Shift master berhasil diperbarui.');
    }

    public function destroy(ShiftMaster $shiftMaster)
    {
        if ($shiftMaster->kategoriShifts()->exists()) {
            return back()->with('error', 'Shift ini masih dipakai pada shift per kategori.');
        }

        if ($shiftMaster->absensiHarians()->exists()) {
            return back()->with('error', 'Shift ini masih dipakai pada data absensi harian.');
        }

        $shiftMaster->delete();

        return redirect()
            ->route('master.shift-master.index')
            ->with('success', 'Shift master berhasil dihapus.');
    }
}