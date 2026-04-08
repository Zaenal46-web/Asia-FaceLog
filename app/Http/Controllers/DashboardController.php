<?php

namespace App\Http\Controllers;

use App\Models\AbsensiHarian;
use App\Models\FingerspotAttlog;
use App\Models\FingerspotDevice;
use App\Models\Karyawan;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->timezone('Asia/Jakarta')->toDateString();

        $stats = [
            'total_karyawan' => Karyawan::where('is_active', true)->count(),
            'total_device_aktif' => FingerspotDevice::where('is_active', true)->count(),
            'scan_hari_ini' => FingerspotAttlog::whereDate('scan_time', $today)->count(),
            'absensi_diproses' => AbsensiHarian::whereDate('tanggal', $today)->count(),
        ];

        $latestRawLogs = FingerspotAttlog::query()
            ->with('device')
            ->latest('scan_time')
            ->limit(8)
            ->get();

        $latestKaryawans = Karyawan::query()
            ->with(['kategoriKaryawan', 'device'])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.index', compact('stats', 'latestRawLogs', 'latestKaryawans'));
    }
}