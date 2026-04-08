<?php

namespace App\Http\Controllers\Integrasi;

use App\Http\Controllers\Controller;
use App\Models\FingerspotAttlog;
use App\Models\FingerspotDevice;
use Illuminate\Http\Request;

class RawLogController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = trim((string) $request->get('tanggal', now()->timezone('Asia/Jakarta')->toDateString()));
        $pin = trim((string) $request->get('pin', ''));
        $deviceId = $request->get('device_id');
        $verifyMode = trim((string) $request->get('verify_mode', ''));
        $statusScan = trim((string) $request->get('status_scan', ''));

        $query = FingerspotAttlog::query()
            ->with('device')
            ->when($tanggal !== '', fn ($q) => $q->whereDate('scan_time', $tanggal))
            ->when($pin !== '', fn ($q) => $q->where('pin', 'like', "%{$pin}%"))
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($verifyMode !== '', fn ($q) => $q->where('verify_mode', $verifyMode))
            ->when($statusScan !== '', fn ($q) => $q->where('status_scan', $statusScan))
            ->latest('scan_time');

        $items = $query->paginate(15)->withQueryString();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $baseQuery = FingerspotAttlog::query()
            ->when($tanggal !== '', fn ($q) => $q->whereDate('scan_time', $tanggal));

        $totalLog = (clone $baseQuery)->count();
        $totalDeviceTerdeteksi = (clone $baseQuery)->whereNotNull('device_id')->distinct('device_id')->count('device_id');
        $totalPinTerdeteksi = (clone $baseQuery)->distinct('pin')->count('pin');
        $totalDenganFoto = (clone $baseQuery)->whereNotNull('photo_url')->where('photo_url', '!=', '')->count();

        return view('integrasi.raw-log.index', compact(
            'items',
            'tanggal',
            'pin',
            'deviceId',
            'verifyMode',
            'statusScan',
            'deviceOptions',
            'totalLog',
            'totalDeviceTerdeteksi',
            'totalPinTerdeteksi',
            'totalDenganFoto'
        ));
    }

    public function create()
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.raw-log.create', compact('deviceOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        FingerspotAttlog::create($validated);

        return redirect()
            ->route('integrasi.raw-log.index')
            ->with('success', 'Raw log berhasil ditambahkan.');
    }

    public function show(FingerspotAttlog $rawLog)
    {
        $rawLog->load('device');

        return view('integrasi.raw-log.show', compact('rawLog'));
    }

    public function edit(FingerspotAttlog $rawLog)
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.raw-log.edit', compact('rawLog', 'deviceOptions'));
    }

    public function update(Request $request, FingerspotAttlog $rawLog)
    {
        $validated = $this->validateForm($request);

        $rawLog->update($validated);

        return redirect()
            ->route('integrasi.raw-log.index')
            ->with('success', 'Raw log berhasil diperbarui.');
    }

    public function destroy(FingerspotAttlog $rawLog)
    {
        $rawLog->delete();

        return redirect()
            ->route('integrasi.raw-log.index')
            ->with('success', 'Raw log berhasil dihapus.');
    }

    protected function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
            'pin' => ['required', 'string', 'max:255'],
            'device_sn' => ['nullable', 'string', 'max:255'],
            'scan_time' => ['required', 'date'],
            'verify_mode' => ['nullable', 'string', 'max:255'],
            'status_scan' => ['nullable', 'string', 'max:255'],
            'photo_url' => ['nullable', 'url'],
            'raw' => ['nullable', 'string'],
        ]);

        return [
            'device_id' => $validated['device_id'] ?? null,
            'pin' => trim($validated['pin']),
            'device_sn' => $this->nullableTrim($validated['device_sn'] ?? null),
            'scan_time' => $validated['scan_time'],
            'verify_mode' => $this->nullableTrim($validated['verify_mode'] ?? null),
            'status_scan' => $this->nullableTrim($validated['status_scan'] ?? null),
            'photo_url' => $this->nullableTrim($validated['photo_url'] ?? null),
            'raw' => $this->nullableTrim($validated['raw'] ?? null),
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