<?php

namespace App\Http\Controllers;

use App\Models\HolidayCalendar;
use Illuminate\Http\Request;

class HolidayCalendarController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $tahun = $request->get('tahun', now()->year);
        $status = trim((string) $request->get('status', ''));
        $tipe = trim((string) $request->get('tipe', ''));

        $items = HolidayCalendar::query()
            ->when($tahun, function ($q) use ($tahun) {
                $q->where(function ($sub) use ($tahun) {
                    $sub->whereYear('tanggal_mulai', $tahun)
                        ->orWhereYear('tanggal_selesai', $tahun);
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
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
            ->when($tipe !== '', fn ($q) => $q->where('tipe', $tipe))
            ->orderBy('tanggal_mulai')
            ->orderBy('tanggal_selesai')
            ->paginate(20)
            ->withQueryString();

        $total = HolidayCalendar::count();
        $totalActive = HolidayCalendar::where('is_active', true)->count();
        $tahunIni = HolidayCalendar::query()
            ->where(function ($q) {
                $q->whereYear('tanggal_mulai', now()->year)
                    ->orWhereYear('tanggal_selesai', now()->year);
            })
            ->count();

        return view('holiday.index', compact(
            'items',
            'search',
            'tahun',
            'status',
            'tipe',
            'total',
            'totalActive',
            'tahunIni'
        ));
    }

    public function create()
    {
        return view('holiday.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'max:50'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HolidayCalendar::create([
            'nama' => trim($validated['nama']),
            'tipe' => trim($validated['tipe']),
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan' => $validated['keterangan'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('holiday.index')
            ->with('success', 'Kalender libur berhasil ditambahkan.');
    }

    public function edit(HolidayCalendar $holiday)
    {
        return view('holiday.edit', compact('holiday'));
    }

    public function update(Request $request, HolidayCalendar $holiday)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'max:50'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $holiday->update([
            'nama' => trim($validated['nama']),
            'tipe' => trim($validated['tipe']),
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan' => $validated['keterangan'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('holiday.index')
            ->with('success', 'Kalender libur berhasil diperbarui.');
    }

    public function destroy(HolidayCalendar $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('holiday.index')
            ->with('success', 'Kalender libur berhasil dihapus.');
    }
}