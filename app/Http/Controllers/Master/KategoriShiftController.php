<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KategoriKaryawan;
use App\Models\KategoriShift;
use App\Models\ShiftMaster;
use Illuminate\Http\Request;

class KategoriShiftController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $kategoriId = $request->get('kategori_karyawan_id');
        $shiftId = $request->get('shift_master_id');
        $status = trim((string) $request->get('status', ''));

        $query = KategoriShift::query()
            ->with(['kategoriKaryawan.parent', 'shiftMaster'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_rule', 'like', "%{$search}%")
                        ->orWhereHas('kategoriKaryawan', function ($qq) use ($search) {
                            $qq->where('nama', 'like', "%{$search}%")
                               ->orWhere('kode', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shiftMaster', function ($qq) use ($search) {
                            $qq->where('nama', 'like', "%{$search}%")
                               ->orWhere('kode', 'like', "%{$search}%");
                        });
                });
            })
            ->when($kategoriId, fn ($q) => $q->where('kategori_karyawan_id', $kategoriId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId))
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('kategori_karyawan_id')
            ->orderBy('prioritas')
            ->orderByDesc('is_default');

        $items = $query->paginate(12)->withQueryString();

        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $shiftOptions = ShiftMaster::query()
            ->orderBy('jam_masuk')
            ->orderBy('nama')
            ->get();

        $totalRule = KategoriShift::count();
        $totalActive = KategoriShift::where('is_active', true)->count();
        $totalDefault = KategoriShift::where('is_default', true)->count();
        $totalIstirahatAktif = KategoriShift::where('istirahat_aktif', true)->count();

        return view('master.kategori-shift.index', compact(
            'items',
            'search',
            'kategoriId',
            'shiftId',
            'status',
            'kategoriOptions',
            'shiftOptions',
            'totalRule',
            'totalActive',
            'totalDefault',
            'totalIstirahatAktif'
        ));
    }

    public function create()
    {
        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $shiftOptions = ShiftMaster::query()
            ->orderBy('jam_masuk')
            ->orderBy('nama')
            ->get();

        return view('master.kategori-shift.create', compact('kategoriOptions', 'shiftOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        $kategoriShift = KategoriShift::create($validated);

        if ($kategoriShift->is_default) {
            $this->clearOtherDefaults($kategoriShift);
        }

        return redirect()
            ->route('master.kategori-shift.index')
            ->with('success', 'Rule shift per kategori berhasil ditambahkan.');
    }

    public function edit(KategoriShift $kategoriShift)
    {
        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $shiftOptions = ShiftMaster::query()
            ->orderBy('jam_masuk')
            ->orderBy('nama')
            ->get();

        return view('master.kategori-shift.edit', compact('kategoriShift', 'kategoriOptions', 'shiftOptions'));
    }

    public function update(Request $request, KategoriShift $kategoriShift)
    {
        $validated = $this->validateForm($request);

        $kategoriShift->update($validated);

        if ($kategoriShift->is_default) {
            $this->clearOtherDefaults($kategoriShift);
        }

        return redirect()
            ->route('master.kategori-shift.index')
            ->with('success', 'Rule shift per kategori berhasil diperbarui.');
    }

    public function destroy(KategoriShift $kategoriShift)
    {
        if ($kategoriShift->absensiHarians()->exists()) {
            return back()->with('error', 'Rule ini sudah dipakai pada absensi harian.');
        }

        $kategoriShift->delete();

        return redirect()
            ->route('master.kategori-shift.index')
            ->with('success', 'Rule shift per kategori berhasil dihapus.');
    }

    protected function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'kategori_karyawan_id' => ['required', 'exists:kategori_karyawans,id'],
            'shift_master_id' => ['required', 'exists:shift_masters,id'],
            'nama_rule' => ['nullable', 'string', 'max:255'],
            'prioritas' => ['required', 'integer', 'min:1'],
            'is_default' => ['nullable', 'boolean'],
            'lintas_hari' => ['nullable', 'boolean'],

            'window_masuk_mulai_menit' => ['required', 'integer'],
            'window_masuk_selesai_menit' => ['required', 'integer'],
            'window_pulang_mulai_menit' => ['required', 'integer'],
            'window_pulang_selesai_menit' => ['required', 'integer'],

            'toleransi_telat_menit' => ['required', 'integer', 'min:0'],
            'toleransi_pulang_cepat_menit' => ['required', 'integer', 'min:0'],
            'toleransi_lembur_menit' => ['required', 'integer', 'min:0'],

            'istirahat_aktif' => ['nullable', 'boolean'],
            'istirahat_otomatis_potong' => ['nullable', 'boolean'],
            'menit_istirahat_default' => ['required', 'integer', 'min:0'],

            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'kategori_karyawan_id' => (int) $validated['kategori_karyawan_id'],
            'shift_master_id' => (int) $validated['shift_master_id'],
            'nama_rule' => isset($validated['nama_rule']) && trim($validated['nama_rule']) !== '' ? trim($validated['nama_rule']) : null,
            'prioritas' => (int) $validated['prioritas'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'lintas_hari' => (bool) ($validated['lintas_hari'] ?? false),

            'window_masuk_mulai_menit' => (int) $validated['window_masuk_mulai_menit'],
            'window_masuk_selesai_menit' => (int) $validated['window_masuk_selesai_menit'],
            'window_pulang_mulai_menit' => (int) $validated['window_pulang_mulai_menit'],
            'window_pulang_selesai_menit' => (int) $validated['window_pulang_selesai_menit'],

            'toleransi_telat_menit' => (int) $validated['toleransi_telat_menit'],
            'toleransi_pulang_cepat_menit' => (int) $validated['toleransi_pulang_cepat_menit'],
            'toleransi_lembur_menit' => (int) $validated['toleransi_lembur_menit'],

            'istirahat_aktif' => (bool) ($validated['istirahat_aktif'] ?? false),
            'istirahat_otomatis_potong' => (bool) ($validated['istirahat_otomatis_potong'] ?? false),
            'menit_istirahat_default' => (int) $validated['menit_istirahat_default'],

            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    protected function clearOtherDefaults(KategoriShift $kategoriShift): void
    {
        KategoriShift::query()
            ->where('kategori_karyawan_id', $kategoriShift->kategori_karyawan_id)
            ->where('id', '!=', $kategoriShift->id)
            ->update(['is_default' => false]);
    }
}