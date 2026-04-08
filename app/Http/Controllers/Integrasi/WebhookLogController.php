<?php

namespace App\Http\Controllers\Integrasi;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Models\FingerspotWebhookLog;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = trim((string) $request->get('tanggal', now()->timezone('Asia/Jakarta')->toDateString()));
        $deviceId = $request->get('device_id');
        $eventType = trim((string) $request->get('event_type', ''));
        $status = trim((string) $request->get('status', ''));

        $query = FingerspotWebhookLog::query()
            ->with('device')
            ->when($tanggal !== '', fn ($q) => $q->whereDate('received_at', $tanggal))
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($eventType !== '', fn ($q) => $q->where('event_type', $eventType))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('received_at')
            ->latest();

        $items = $query->paginate(15)->withQueryString();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $baseQuery = FingerspotWebhookLog::query()
            ->when($tanggal !== '', fn ($q) => $q->whereDate('received_at', $tanggal));

        $totalLog = (clone $baseQuery)->count();
        $totalDeviceTerdeteksi = (clone $baseQuery)->whereNotNull('device_id')->distinct('device_id')->count('device_id');
        $totalReceived = (clone $baseQuery)->where('status', 'received')->count();
        $totalError = (clone $baseQuery)->where('status', 'error')->count();

        return view('integrasi.webhook-log.index', compact(
            'items',
            'tanggal',
            'deviceId',
            'eventType',
            'status',
            'deviceOptions',
            'totalLog',
            'totalDeviceTerdeteksi',
            'totalReceived',
            'totalError'
        ));
    }

    public function create()
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.webhook-log.create', compact('deviceOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        FingerspotWebhookLog::create($validated);

        return redirect()
            ->route('integrasi.webhook-log.index')
            ->with('success', 'Webhook log berhasil ditambahkan.');
    }

    public function show(FingerspotWebhookLog $webhookLog)
    {
        $webhookLog->load('device');

        return view('integrasi.webhook-log.show', compact('webhookLog'));
    }

    public function edit(FingerspotWebhookLog $webhookLog)
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.webhook-log.edit', compact('webhookLog', 'deviceOptions'));
    }

    public function update(Request $request, FingerspotWebhookLog $webhookLog)
    {
        $validated = $this->validateForm($request);

        $webhookLog->update($validated);

        return redirect()
            ->route('integrasi.webhook-log.index')
            ->with('success', 'Webhook log berhasil diperbarui.');
    }

    public function destroy(FingerspotWebhookLog $webhookLog)
    {
        $webhookLog->delete();

        return redirect()
            ->route('integrasi.webhook-log.index')
            ->with('success', 'Webhook log berhasil dihapus.');
    }

    protected function validateForm(Request $request): array
    {
        $validated = $request->validate([
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
            'event_type' => ['nullable', 'string', 'max:255'],
            'payload_json' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'received_at' => ['nullable', 'date'],
        ]);

        return [
            'device_id' => $validated['device_id'] ?? null,
            'event_type' => $this->nullableTrim($validated['event_type'] ?? null),
            'payload_json' => $this->nullableTrim($validated['payload_json'] ?? null),
            'status' => trim($validated['status']),
            'message' => $this->nullableTrim($validated['message'] ?? null),
            'received_at' => $validated['received_at'] ?? null,
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