<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Services\Fingerspot\AttlogIngestService;
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

    public function getAttlogFromApi(
        Request $request,
        FingerspotDevice $device,
        FingerApiService $api,
        AttlogIngestService $ingestService
    ) {
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

        if (! ($result['ok'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Get Attlog gagal.');
        }

        $response = $result['response'] ?? [];

        \Log::info('GetAttlog raw response', [
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

        if (! is_array($response)) {
            return back()->with('error', 'Response Get Attlog bukan array yang valid. Cek laravel.log.');
        }

        $rows = $this->extractAttlogRows($response);

        if (empty($rows)) {
            \Log::warning('GetAttlog rows empty after extract', [
                'device_id' => $device->id,
                'response' => $response,
            ]);

            return back()->with('error', 'Get Attlog berhasil dipanggil, tetapi data scan tidak ditemukan pada response API. Cek laravel.log.');
        }

        $syncBatch = 'get_attlog:' . $device->id . ':' . now()->format('YmdHis');

        $summary = $ingestService->ingestMany(
            device: $device,
            rows: $rows,
            sourceChannel: 'get_attlog',
            vendorTransId: $response['trans_id'] ?? null,
            syncBatch: $syncBatch,
            receivedAt: now(),
        );

        \Log::info('GetAttlog summary', [
            'device_id' => $device->id,
            'device_name' => $device->nama ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_rows_extracted' => count($rows),
            'inserted' => $summary['inserted'],
            'duplicate' => $summary['duplicate'],
            'invalid' => $summary['invalid'],
            'invalid_samples' => $summary['invalid_samples'],
            'duplicate_samples' => $summary['duplicate_samples'],
            'vendor_trans_id' => $response['trans_id'] ?? null,
            'sync_batch' => $syncBatch,
        ]);

        return back()->with(
            'success',
            "Get Attlog selesai. Total response: " . count($rows)
            . ". Data baru: {$summary['inserted']}. Duplicate: {$summary['duplicate']}. Invalid: {$summary['invalid']}."
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

    protected function extractAttlogRows(array $response): array
    {
        $candidates = [
            $response['data'] ?? null,
            $response['attlog'] ?? null,
            $response['rows'] ?? null,
            $response['response']['data'] ?? null,
            $response['response']['attlog'] ?? null,
            $response['payload']['data'] ?? null,
            $response['payload']['attlog'] ?? null,
            $response['data']['data'] ?? null,
            $response['data']['attlog'] ?? null,
            $response['data']['rows'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($this->isListOfAttlogs($candidate)) {
                return $candidate;
            }
        }

        $found = $this->findAttlogListRecursive($response);

        return $found ?? [];
    }

    protected function isListOfAttlogs($value): bool
    {
        if (! is_array($value) || empty($value)) {
            return false;
        }

        if (! array_is_list($value)) {
            return false;
        }

        $first = $value[0] ?? null;

        if (! is_array($first)) {
            return false;
        }

        return array_key_exists('pin', $first)
            || array_key_exists('scan_date', $first)
            || array_key_exists('scan', $first)
            || array_key_exists('scan_time', $first);
    }

    protected function findAttlogListRecursive($value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        if ($this->isListOfAttlogs($value)) {
            return $value;
        }

        foreach ($value as $child) {
            if (is_array($child)) {
                $found = $this->findAttlogListRecursive($child);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}