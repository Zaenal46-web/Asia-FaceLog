<?php

namespace App\Http\Controllers;

use App\Models\AbsensiHarian;
use App\Models\FingerspotDevice;
use App\Models\ShiftMaster;
use App\Services\Attendance\ProsesAbsensiCoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->timezone('Asia/Jakarta')->toDateString());
        $deviceId = $request->get('device_id');
        $shiftId = $request->get('shift_id');
        $search = trim((string) $request->get('search', ''));

        $query = AbsensiHarian::query()
            ->with(['karyawan.kategoriKaryawan', 'device', 'shiftMaster'])
            ->whereHas('karyawan', function ($q) {
                $q->whereNotNull('kategori_karyawan_id');
            })
            ->whereDate('tanggal', $tanggal)
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->whereNotNull('kategori_karyawan_id')
                        ->where(function ($qq) use ($search) {
                            $qq->where('nama', 'like', "%{$search}%")
                               ->orWhere('pin_fingerspot', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('jam_masuk')
            ->orderBy('karyawan_id');

        $items = $query->paginate(20)->withQueryString();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $shiftOptions = ShiftMaster::query()
            ->orderBy('nama')
            ->get();

        $summaryQuery = AbsensiHarian::query()
            ->whereHas('karyawan', function ($q) {
                $q->whereNotNull('kategori_karyawan_id');
            })
            ->whereDate('tanggal', $tanggal)
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId));

        $total = (clone $summaryQuery)->count();
        $totalMasuk = (clone $summaryQuery)->whereNotNull('jam_masuk')->count();
        $totalPulang = (clone $summaryQuery)->whereNotNull('jam_pulang')->count();
        $totalTelat = (clone $summaryQuery)->where('status_telat', true)->count();
        $totalBelumPulang = (clone $summaryQuery)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->count();

        return view('absensi.index', compact(
            'items',
            'tanggal',
            'deviceId',
            'shiftId',
            'search',
            'deviceOptions',
            'shiftOptions',
            'total',
            'totalMasuk',
            'totalPulang',
            'totalTelat',
            'totalBelumPulang'
        ));
    }

    public function proses(Request $request, ProsesAbsensiCoreService $service)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
        ]);

        $tanggal = Carbon::parse($validated['tanggal'])->toDateString();
        $deviceId = $validated['device_id'] ?? null;

        $result = $service->prosesTanggal($tanggal, $deviceId);

        return redirect()
            ->route('absensi.index', [
                'tanggal' => $tanggal,
                'device_id' => $deviceId,
            ])
            ->with('success', 'Proses absensi selesai. '
                . 'Processed: ' . $result['processed']
                . ', Upserted: ' . $result['upserted']
                . ', Skip Manual: ' . $result['skipped_manual']
                . ', No Rule: ' . $result['no_rule']
                . ', No Scan: ' . $result['no_scan']
                . ', Unmatched: ' . $result['unmatched']);
    }
}