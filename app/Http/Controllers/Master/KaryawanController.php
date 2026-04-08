<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Models\KategoriKaryawan;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $kategoriId = $request->get('kategori_karyawan_id');
        $deviceId = $request->get('device_id');
        $status = trim((string) $request->get('status', ''));
        $kelengkapan = trim((string) $request->get('kelengkapan', ''));

        $query = Karyawan::query()
            ->with(['kategoriKaryawan.parent', 'device'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('pin_fingerspot', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%")
                        ->orWhere('status_kerja', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, fn ($q) => $q->where('kategori_karyawan_id', $kategoriId))
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                }

                if ($status === 'inactive') {
                    $q->where('is_active', false);
                }

                if ($status === 'with_pin') {
                    $q->whereNotNull('pin_fingerspot')->where('pin_fingerspot', '!=', '');
                }

                if ($status === 'without_pin') {
                    $q->where(function ($sub) {
                        $sub->whereNull('pin_fingerspot')->orWhere('pin_fingerspot', '');
                    });
                }
            })
            ->when($kelengkapan !== '', function ($q) use ($kelengkapan) {
                if ($kelengkapan === 'lengkap') {
                    $q->whereNotNull('kategori_karyawan_id')
                        ->where(function ($sub) {
                            $sub->whereNotNull('jabatan')->where('jabatan', '!=', '');
                        })
                        ->whereNotNull('tanggal_masuk')
                        ->where(function ($sub) {
                            $sub->whereNotNull('status_kerja')->where('status_kerja', '!=', '');
                        });
                }

                if ($kelengkapan === 'belum_lengkap') {
                    $q->where(function ($sub) {
                        $sub->whereNull('kategori_karyawan_id')
                            ->orWhereNull('tanggal_masuk')
                            ->orWhereNull('jabatan')
                            ->orWhere('jabatan', '')
                            ->orWhereNull('status_kerja')
                            ->orWhere('status_kerja', '');
                    });
                }

                if ($kelengkapan === 'belum_ada_kategori') {
                    $q->whereNull('kategori_karyawan_id');
                }

                if ($kelengkapan === 'belum_ada_jabatan') {
                    $q->where(function ($sub) {
                        $sub->whereNull('jabatan')->orWhere('jabatan', '');
                    });
                }

                if ($kelengkapan === 'belum_ada_tanggal_masuk') {
                    $q->whereNull('tanggal_masuk');
                }

                if ($kelengkapan === 'belum_ada_status_kerja') {
                    $q->where(function ($sub) {
                        $sub->whereNull('status_kerja')->orWhere('status_kerja', '');
                    });
                }
            })
            ->latest();

        $items = $query->paginate(12)->withQueryString();

        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $totalKaryawan = Karyawan::count();
        $totalActive = Karyawan::where('is_active', true)->count();
        $totalWithPin = Karyawan::whereNotNull('pin_fingerspot')->where('pin_fingerspot', '!=', '')->count();
        $totalWithoutPin = Karyawan::where(function ($q) {
            $q->whereNull('pin_fingerspot')->orWhere('pin_fingerspot', '');
        })->count();

        $totalLengkap = Karyawan::query()
            ->whereNotNull('kategori_karyawan_id')
            ->where(function ($q) {
                $q->whereNotNull('jabatan')->where('jabatan', '!=', '');
            })
            ->whereNotNull('tanggal_masuk')
            ->where(function ($q) {
                $q->whereNotNull('status_kerja')->where('status_kerja', '!=', '');
            })
            ->count();

        $totalBelumLengkap = Karyawan::query()
            ->where(function ($q) {
                $q->whereNull('kategori_karyawan_id')
                    ->orWhereNull('tanggal_masuk')
                    ->orWhereNull('jabatan')
                    ->orWhere('jabatan', '')
                    ->orWhereNull('status_kerja')
                    ->orWhere('status_kerja', '');
            })
            ->count();

        return view('master.karyawan.index', compact(
            'items',
            'search',
            'kategoriId',
            'deviceId',
            'status',
            'kelengkapan',
            'kategoriOptions',
            'deviceOptions',
            'totalKaryawan',
            'totalActive',
            'totalWithPin',
            'totalWithoutPin',
            'totalLengkap',
            'totalBelumLengkap'
        ));
    }

    public function create()
    {
        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('master.karyawan.create', compact('kategoriOptions', 'deviceOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        Karyawan::create($validated);

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        $kategoriOptions = KategoriKaryawan::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('master.karyawan.edit', compact('karyawan', 'kategoriOptions', 'deviceOptions'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $this->validateForm($request, $karyawan->id);

        $karyawan->update($validated);

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        if ($karyawan->absensiHarians()->exists()) {
            return back()->with('error', 'Karyawan ini sudah memiliki data absensi harian.');
        }

        $karyawan->delete();

        return redirect()
            ->route('master.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    protected function validateForm(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'pin_fingerspot' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('karyawans', 'pin_fingerspot')->ignore($ignoreId),
            ],
            'kategori_karyawan_id' => ['nullable', 'exists:kategori_karyawans,id'],
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'tanggal_masuk' => ['nullable', 'date'],
            'status_kerja' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'nama' => trim($validated['nama']),
            'pin_fingerspot' => $this->nullableTrim($validated['pin_fingerspot'] ?? null),
            'kategori_karyawan_id' => $validated['kategori_karyawan_id'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            'jabatan' => $this->nullableTrim($validated['jabatan'] ?? null),
            'tanggal_masuk' => $validated['tanggal_masuk'] ?? null,
            'status_kerja' => $this->nullableTrim($validated['status_kerja'] ?? null),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    protected function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}