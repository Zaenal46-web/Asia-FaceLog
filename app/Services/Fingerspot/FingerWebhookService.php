<?php

namespace App\Services\Fingerspot;

use App\Models\FingerspotDevice;
use App\Models\FingerspotUser;
use App\Models\FingerspotWebhookLog;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Log;

class FingerWebhookService
{
    public function __construct(
        protected AttlogIngestService $attlogIngestService
    ) {
    }

    public function handle(array $payload): void
    {
        $type = (string) ($payload['type'] ?? '');
        $cloudId = (string) ($payload['cloud_id'] ?? '');
        $data = $payload['data'] ?? [];

        $device = null;

        if ($cloudId !== '') {
            $device = FingerspotDevice::query()
                ->where('cloud_id', $cloudId)
                ->orWhere('serial_number', $cloudId)
                ->first();
        }

        $webhookLog = FingerspotWebhookLog::create([
            'device_id' => $device?->id,
            'event_type' => $type !== '' ? $type : null,
            'payload_json' => $this->toJson($payload),
            'status' => 'received',
            'message' => $device ? 'Webhook diterima' : 'Webhook diterima, device belum ditemukan',
            'received_at' => now(),
        ]);

        if (! $device) {
            Log::warning('Webhook cloud_id tidak terdaftar di fingerspot_devices', [
                'cloud_id' => $cloudId,
                'type' => $type,
                'payload' => $payload,
            ]);

            $webhookLog->update([
                'status' => 'error',
                'message' => 'Device tidak ditemukan berdasarkan cloud_id / serial_number',
            ]);

            return;
        }

        $device->update([
            'last_seen_at' => now(),
        ]);

        try {
            match ($type) {
                'attlog' => $this->handleAttlog($device, $data, $payload),
                'get_userinfo' => $this->handleGetUserinfo($device, $data, $payload),
                'get_userid_list' => $this->handleGetUseridList($device, $data, $payload),
                'set_userinfo' => $this->handleSetUserinfo($device, $data, $payload),
                'delete_userinfo' => $this->handleDeleteUserinfo($device, $data, $payload),
                'set_time' => $this->handleSetTime($device, $data, $payload),
                'register_online' => $this->handleRegisterOnline($device, $data, $payload),
                default => $this->handleUnknownType($device, $type, $payload),
            };

            $webhookLog->update([
                'device_id' => $device->id,
                'status' => 'processed',
                'message' => 'Webhook berhasil diproses',
            ]);
        } catch (\Throwable $e) {
            Log::error('FingerWebhookService process error', [
                'type' => $type,
                'cloud_id' => $cloudId,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            $webhookLog->update([
                'device_id' => $device->id,
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function handleAttlog(FingerspotDevice $device, array $data, array $payload): void
    {
        $pin = $this->stringOrNull($data['pin'] ?? null);
        $scan = $this->stringOrNull($data['scan'] ?? $data['scan_date'] ?? $data['scan_time'] ?? null);

        if (! $pin || ! $scan) {
            throw new \RuntimeException('Payload attlog tidak memiliki pin/scan yang valid');
        }

        $row = [
            'pin' => $pin,
            'scan_date' => $scan, // simpan apa adanya, jangan parse Carbon agar tidak lompat jam
            'device_sn' => $this->stringOrNull($payload['cloud_id'] ?? $data['device_sn'] ?? $device->serial_number),
            'verify' => $this->stringOrNull($data['verify'] ?? $data['verify_mode'] ?? null),
            'status_scan' => $this->stringOrNull($data['status_scan'] ?? $data['status'] ?? null),
            'photo_url' => $this->stringOrNull($data['photo_url'] ?? null),
        ];

        $result = $this->attlogIngestService->ingestRow(
            device: $device,
            row: $row,
            sourceChannel: 'webhook',
            vendorTransId: $this->stringOrNull($payload['trans_id'] ?? null),
            syncBatch: 'webhook:' . $device->id . ':' . now()->format('YmdHis'),
            receivedAt: now(),
        );

        Log::info('Webhook attlog processed', [
            'device_id' => $device->id,
            'cloud_id' => $payload['cloud_id'] ?? null,
            'trans_id' => $payload['trans_id'] ?? null,
            'result' => $result,
            'scan_time_from_payload' => $scan,
            'received_at' => now()->format('Y-m-d H:i:s'),
        ]);

        if (($result['status'] ?? null) === 'invalid') {
            throw new \RuntimeException($result['message'] ?? 'Attlog webhook invalid');
        }
    }

    protected function handleGetUserinfo(FingerspotDevice $device, array $data, array $payload): void
    {
        $pin = $this->stringOrNull($data['pin'] ?? null);
        if (! $pin) {
            throw new \RuntimeException('Payload get_userinfo tidak memiliki pin');
        }

        $user = FingerspotUser::updateOrCreate(
            [
                'device_id' => $device->id,
                'pin' => $pin,
            ],
            [
                'nama' => $this->stringOrNull($data['name'] ?? $data['nama'] ?? null),
                'privilege' => $this->stringOrNull($data['privilege'] ?? null),
                'password' => $this->stringOrNull($data['password'] ?? null),
                'rfid' => $this->stringOrNull($data['rfid'] ?? null),
                'face_template_count' => (int) ($data['face'] ?? $data['face_template_count'] ?? 0),
                'finger_template_count' => (int) ($data['finger'] ?? $data['finger_template_count'] ?? 0),
                'vein_template_count' => (int) ($data['vein'] ?? $data['vein_template_count'] ?? 0),
                'raw_json' => $this->toJson($payload),
                'synced_at' => now(),
            ]
        );

        $this->tryAutoMapKaryawanByPinOrNama($user, $device);
    }

    protected function handleGetUseridList(FingerspotDevice $device, array $data, array $payload): void
    {
        $pins = $data['pin_arr'] ?? $data['pins'] ?? null;

        if (! is_array($pins)) {
            throw new \RuntimeException('Payload get_userid_list tidak memiliki pin_arr array');
        }

        foreach ($pins as $pin) {
            $pin = $this->stringOrNull($pin);

            if (! $pin) {
                continue;
            }

            FingerspotUser::firstOrCreate(
                [
                    'device_id' => $device->id,
                    'pin' => $pin,
                ],
                [
                    'raw_json' => $this->toJson($payload),
                    'synced_at' => now(),
                ]
            );
        }
    }

    protected function handleSetUserinfo(FingerspotDevice $device, array $data, array $payload): void
    {
        $pin = $this->stringOrNull($data['pin'] ?? null);
        if (! $pin) {
            return;
        }

        FingerspotUser::updateOrCreate(
            [
                'device_id' => $device->id,
                'pin' => $pin,
            ],
            [
                'nama' => $this->stringOrNull($data['name'] ?? $data['nama'] ?? null),
                'privilege' => $this->stringOrNull($data['privilege'] ?? null),
                'password' => $this->stringOrNull($data['password'] ?? null),
                'rfid' => $this->stringOrNull($data['rfid'] ?? null),
                'face_template_count' => (int) ($data['face'] ?? $data['face_template_count'] ?? 0),
                'finger_template_count' => (int) ($data['finger'] ?? $data['finger_template_count'] ?? 0),
                'vein_template_count' => (int) ($data['vein'] ?? $data['vein_template_count'] ?? 0),
                'raw_json' => $this->toJson($payload),
                'synced_at' => now(),
            ]
        );
    }

    protected function handleDeleteUserinfo(FingerspotDevice $device, array $data, array $payload): void
    {
        $pin = $this->stringOrNull($data['pin'] ?? null);
        if (! $pin) {
            return;
        }

        $user = FingerspotUser::query()
            ->where('device_id', $device->id)
            ->where('pin', $pin)
            ->first();

        if ($user) {
            $user->delete();
        }
    }

    protected function handleSetTime(FingerspotDevice $device, array $data, array $payload): void
    {
        // cukup audit di webhook log
    }

    protected function handleRegisterOnline(FingerspotDevice $device, array $data, array $payload): void
    {
        // cukup audit di webhook log
    }

    protected function handleUnknownType(FingerspotDevice $device, string $type, array $payload): void
    {
        Log::info('Webhook type tidak dikenali, disimpan untuk audit', [
            'device_id' => $device->id,
            'type' => $type,
            'payload' => $payload,
        ]);
    }

    protected function tryAutoMapKaryawanByPinOrNama(FingerspotUser $user, FingerspotDevice $device): void
    {
        $karyawanByPin = Karyawan::query()
            ->where('pin_fingerspot', $user->pin)
            ->first();

        if ($karyawanByPin) {
            if (! $karyawanByPin->device_id) {
                $karyawanByPin->update([
                    'device_id' => $device->id,
                ]);
            }

            return;
        }

        if (! $user->nama) {
            return;
        }

        $normalizedUserNama = $this->normalizeNama($user->nama);

        $karyawanByNama = Karyawan::query()
            ->get()
            ->first(function (Karyawan $karyawan) use ($normalizedUserNama) {
                return $this->normalizeNama($karyawan->nama) === $normalizedUserNama;
            });

        if ($karyawanByNama && ! $karyawanByNama->pin_fingerspot) {
            $payload = [
                'pin_fingerspot' => $user->pin,
            ];

            if (! $karyawanByNama->device_id) {
                $payload['device_id'] = $device->id;
            }

            $karyawanByNama->update($payload);
        }
    }

    protected function normalizeNama(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);

        return $value ?? '';
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function toJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}