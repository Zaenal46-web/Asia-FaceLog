<?php

namespace App\Http\Controllers\Integrasi;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Models\FingerspotPushLog;
use Illuminate\Http\Request;

class PushLogController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = trim((string) $request->get('tanggal', now()->timezone('Asia/Jakarta')->toDateString()));
        $deviceId = $request->get('device_id');
        $pin = trim((string) $request->get('pin', ''));
        $action = trim((string) $request->get('action', ''));
        $status = trim((string) $request->get('status', ''));

        $query = FingerspotPushLog::query()
            ->with('device')
            ->when($tanggal !== '', fn ($q) => $q->whereDate('created_at', $tanggal))
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($pin !== '', fn ($q) => $q->where('pin', 'like', "%{$pin}%"))
            ->when($action !== '', fn ($q) => $q->where('action', $action))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->latest();

        $items = $query->paginate(15)->withQueryString();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $baseQuery = FingerspotPushLog::query()
            ->when($tanggal !== '', fn ($q) => $q->whereDate('created_at', $tanggal));

        $totalLog = (clone $baseQuery)->count();
        $totalPending = (clone $baseQuery)->where('status', 'pending')->count();
        $totalSuccess = (clone $baseQuery)->whereIn('status', ['success', 'done', 'processed'])->count();
        $totalFailed = (clone $baseQuery)->whereIn('status', ['failed', 'error'])->count();

        return view('integrasi.push-log.index', compact(
            'items',
            'tanggal',
            'deviceId',
            'pin',
            'action',
            'status',
            'deviceOptions',
            'totalLog',
            'totalPending',
            'totalSuccess',
            'totalFailed'
        ));
    }

    public function create()
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.push-log.create', compact('deviceOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        FingerspotPushLog::create($validated);

        return redirect()
            ->route('integrasi.push-log.index')
            ->with('success', 'Push log berhasil ditambahkan.');
    }

    public function show(FingerspotPushLog $pushLog)
    {
        $pushLog->load('device');

        return view('integrasi.push-log.show', compact('pushLog'));
    }

    public function edit(FingerspotPushLog $pushLog)
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.push-log.edit', compact('pushLog', 'deviceOptions'));
    }

    public function update(Request $request, FingerspotPushLog $pushLog)
    {
        $validated = $this->validateForm($request);

        $pushLog->update($validated);

        return redirect()
            ->route('integrasi.push-log.index')
            ->with('success', 'Push log berhasil diperbarui.');
    }

    public function destroy(FingerspotPushLog $pushLog)
    {
        $pushLog->delete();

        return redirect()
            ->route('integrasi.push-log.index')
            ->with('success', 'Push log berhasil dihapus.');
    }

    protected function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
            'pin' => ['nullable', 'string', 'max:255'],
            'action' => ['required', 'string', 'max:255'],
            'payload_json' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:255'],
            'response_message' => ['nullable', 'string'],
            'processed_at' => ['nullable', 'date'],
        ]);

        return [
            'device_id' => $validated['device_id'] ?? null,
            'pin' => $this->nullableTrim($validated['pin'] ?? null),
            'action' => trim($validated['action']),
            'payload_json' => $this->nullableTrim($validated['payload_json'] ?? null),
            'status' => trim($validated['status']),
            'response_message' => $this->nullableTrim($validated['response_message'] ?? null),
            'processed_at' => $validated['processed_at'] ?? null,
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