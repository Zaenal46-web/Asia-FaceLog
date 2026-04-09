<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Services\Fingerspot\FingerApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FingerDeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));

        $query = FingerspotDevice::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('cloud_id', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
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
            ->latest();

        $items = $query->paginate(12)->withQueryString();

        $totalDevice = FingerspotDevice::count();
        $totalActive = FingerspotDevice::where('is_active', true)->count();
        $totalInactive = FingerspotDevice::where('is_active', false)->count();
        $totalOnlineHint = FingerspotDevice::whereNotNull('last_seen_at')->count();

        return view('master.device.index', compact(
            'items',
            'search',
            'status',
            'totalDevice',
            'totalActive',
            'totalInactive',
            'totalOnlineHint'
        ));
    }

    public function create()
    {
        $timezones = $this->timezones();

        return view('master.device.create', compact('timezones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:fingerspot_devices,serial_number'],
            'cloud_id' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'ip'],
            'last_seen_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        FingerspotDevice::create([
            'nama' => trim($validated['nama']),
            'serial_number' => $this->nullableTrim($validated['serial_number'] ?? null),
            'cloud_id' => $this->nullableTrim($validated['cloud_id'] ?? null),
            'lokasi' => $this->nullableTrim($validated['lokasi'] ?? null),
            'timezone' => $validated['timezone'],
            'ip_address' => $this->nullableTrim($validated['ip_address'] ?? null),
            'last_seen_at' => $validated['last_seen_at'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.device.index')
            ->with('success', 'Device mesin berhasil ditambahkan.');
    }

    public function edit(FingerspotDevice $device)
    {
        $timezones = $this->timezones();

        return view('master.device.edit', compact('device', 'timezones'));
    }

    public function update(Request $request, FingerspotDevice $device)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('fingerspot_devices', 'serial_number')->ignore($device->id),
            ],
            'cloud_id' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:100'],
            'ip_address' => ['nullable', 'ip'],
            'last_seen_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $device->update([
            'nama' => trim($validated['nama']),
            'serial_number' => $this->nullableTrim($validated['serial_number'] ?? null),
            'cloud_id' => $this->nullableTrim($validated['cloud_id'] ?? null),
            'lokasi' => $this->nullableTrim($validated['lokasi'] ?? null),
            'timezone' => $validated['timezone'],
            'ip_address' => $this->nullableTrim($validated['ip_address'] ?? null),
            'last_seen_at' => $validated['last_seen_at'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master.device.index')
            ->with('success', 'Device mesin berhasil diperbarui.');
    }

    public function destroy(FingerspotDevice $device)
    {
        if ($device->karyawans()->exists()) {
            return back()->with('error', 'Device ini masih terhubung dengan data karyawan.');
        }

        if ($device->fingerspotUsers()->exists()) {
            return back()->with('error', 'Device ini masih punya data user mesin.');
        }

        if ($device->attlogs()->exists()) {
            return back()->with('error', 'Device ini masih punya raw log. Hapus data terkait dulu jika ingin menghapus device.');
        }

        $device->delete();

        return redirect()
            ->route('master.device.index')
            ->with('success', 'Device mesin berhasil dihapus.');
    }

    public function getAllPinFromApi(FingerspotDevice $device, FingerApiService $api)
    {
        $result = $api->getAllPin($device);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function setTimeToDevice(FingerspotDevice $device, FingerApiService $api)
    {
        $result = $api->setTime($device, $device->timezone);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function getAttlogFromApi(Request $request, FingerspotDevice $device, FingerApiService $api)
{
    $validated = $request->validate([
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
    ]);

    $startDate = $validated['start_date'];
    $endDate = $validated['end_date'];

    $diffDays = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate));

    if ($diffDays > 1) {
        return back()->with('error', 'Range Get Attlog maksimal 2 hari per request.');
    }

    $result = $api->getAttlog($device, $startDate, $endDate);

    if (!($result['ok'] ?? false)) {
        return back()->with('error', $result['message'] ?? 'Get Attlog gagal.');
    }

    $response = $result['response'] ?? [];

    \Log::info('GetAttlog API Response', [
        'device_id' => $device->id,
        'device_name' => $device->nama ?? null,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'response' => $response,
    ]);

    if (is_string($response)) {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response = $decoded;
        }
    }

    $rows = [];

    if (is_array($response)) {
        if (isset($response['data']) && is_array($response['data'])) {
            $rows = $response['data'];
        } elseif (isset($response['attlog']) && is_array($response['attlog'])) {
            $rows = $response['attlog'];
        } elseif (isset($response['rows']) && is_array($response['rows'])) {
            $rows = $response['rows'];
        } elseif (isset($response[0]) && is_array($response[0])) {
            $rows = $response;
        }
    }

    if (empty($rows)) {
        return back()->with('error', 'Get Attlog berhasil dipanggil, tetapi data scan tidak ditemukan pada response API. Cek laravel.log.');
    }

    $inserted = 0;
    $skipped = 0;

    foreach ($rows as $row) {
        $pin = (string) ($row['pin'] ?? '');
        $scan = $row['scan_date'] ?? $row['scan'] ?? $row['scan_time'] ?? null;

        if ($pin === '' || !$scan) {
            $skipped++;
            continue;
        }

        $exists = \App\Models\FingerspotAttlog::query()
            ->where('device_id', $device->id)
            ->where('pin', $pin)
            ->where('scan_time', $scan)
            ->exists();

        if ($exists) {
            $skipped++;
            continue;
        }

        \App\Models\FingerspotAttlog::create([
            'device_id' => $device->id,
            'pin' => $pin,
            'device_sn' => $row['device_sn'] ?? $device->serial_number ?? null,
            'scan_time' => $scan,
            'verify_mode' => $row['verify'] ?? $row['verify_mode'] ?? null,
            'status_scan' => $row['status_scan'] ?? $row['status'] ?? null,
            'photo_url' => $row['photo_url'] ?? null,
            'raw' => $row,
        ]);

        $inserted++;
    }

    return back()->with(
        'success',
        "Get Attlog selesai. Data baru: {$inserted}. Dilewati: {$skipped}."
    );
}

    protected function nullableTrim(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    return $value === '' ? null : $value;
}

    protected function timezones(): array
    {
        return [
            'Asia/Jakarta',
            'Asia/Makassar',
            'Asia/Jayapura',
        ];
    }
}