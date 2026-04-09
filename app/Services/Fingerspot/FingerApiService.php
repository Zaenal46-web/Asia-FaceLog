<?php

namespace App\Services\Fingerspot;

use App\Models\FingerspotDevice;
use App\Models\FingerspotPushLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FingerApiService
{
    public function getUserinfo(FingerspotDevice $device, string $pin): array
    {
        return $this->sendCommand(
            device: $device,
            action: 'get_userinfo',
            payload: [
                'pin' => trim($pin),
            ],
            pin: trim($pin),
        );
    }

    public function setUserinfo(FingerspotDevice $device, array $userData): array
    {
        $pin = trim((string) ($userData['pin'] ?? ''));

        return $this->sendCommand(
            device: $device,
            action: 'set_userinfo',
            payload: array_filter([
            'pin' => $pin,
            'name' => $this->nullableTrim($userData['name'] ?? $userData['nama'] ?? null),
            'privilege' => $this->nullableTrim($userData['privilege'] ?? null),
            'password' => $this->nullableTrim($userData['password'] ?? null),
            'rfid' => $this->nullableTrim($userData['rfid'] ?? null),
            'face' => isset($userData['face']) ? (int) $userData['face'] : (isset($userData['face_template_count']) ? (int) $userData['face_template_count'] : null),
            'finger' => isset($userData['finger']) ? (int) $userData['finger'] : (isset($userData['finger_template_count']) ? (int) $userData['finger_template_count'] : null),
            'vein' => isset($userData['vein']) ? (int) $userData['vein'] : (isset($userData['vein_template_count']) ? (int) $userData['vein_template_count'] : null),
            'template' => $this->nullableTrim($userData['template'] ?? null),
        ], fn ($v) => $v !== null && $v !== ''),
            pin: $pin,
        );
    }

    public function deleteUserinfo(FingerspotDevice $device, string $pin): array
    {
        return $this->sendCommand(
            device: $device,
            action: 'delete_userinfo',
            payload: [
                'pin' => trim($pin),
            ],
            pin: trim($pin),
        );
    }

    public function getAllPin(FingerspotDevice $device): array
    {
        return $this->sendCommand(
            device: $device,
            action: 'get_all_pin',
            payload: [],
            pin: null,
        );
    }

    public function getAttlog(FingerspotDevice $device, string $startDate, string $endDate): array
    {
        return $this->sendCommand(
            device: $device,
            action: 'get_attlog',
            payload: [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            pin: null,
        );
    }

    public function setTime(FingerspotDevice $device, ?string $timezone = null): array
    {
        return $this->sendCommand(
            device: $device,
            action: 'set_time',
            payload: [
                'timezone' => $timezone ?: ($device->timezone ?: config('app.timezone')),
                'time' => now()->timezone($device->timezone ?: config('app.timezone'))->format('Y-m-d H:i:s'),
            ],
            pin: null,
        );
    }

    protected function sendCommand(FingerspotDevice $device, string $action, array $payload, ?string $pin): array
    {
        $token = config('fingerspot.token');
        $baseUrl = config('fingerspot.base_url');
        $endpoint = config("fingerspot.endpoints.{$action}");
        $timeout = (int) config('fingerspot.timeout', 30);

        if (! $token) {
            return [
                'ok' => false,
                'message' => 'FINGERSPOT_API_TOKEN belum diisi di .env',
                'response' => null,
            ];
        }

        if (! $baseUrl || ! $endpoint) {
            return [
                'ok' => false,
                'message' => "Config endpoint Fingerspot untuk action {$action} belum lengkap",
                'response' => null,
            ];
        }

        $cloudId = $this->resolveCloudId($device);

        if (! $cloudId) {
            return [
                'ok' => false,
                'message' => 'Device belum punya cloud_id atau serial_number',
                'response' => null,
            ];
        }

        $requestPayload = array_merge([
            'trans_id' => (string) Str::uuid(),
            'cloud_id' => $cloudId,
        ], $payload);

        $pushLog = FingerspotPushLog::create([
            'device_id' => $device->id,
            'pin' => $pin,
            'action' => $action,
            'payload_json' => $this->toJson([
                'endpoint' => $endpoint,
                'request' => $requestPayload,
            ]),
            'status' => 'pending',
            'response_message' => null,
            'processed_at' => null,
        ]);

        try {
            $response = Http::timeout($timeout)
                ->acceptJson()
                ->withToken($token)
                ->post($baseUrl . $endpoint, $requestPayload);

            $responseBody = $response->json();

            if (! is_array($responseBody)) {
                $responseBody = [
                    'raw_body' => $response->body(),
                ];
            }

            if ($response->successful()) {
                $pushLog->update([
                    'payload_json' => $this->toJson([
                        'endpoint' => $endpoint,
                        'request' => $requestPayload,
                        'response' => $responseBody,
                    ]),
                    'status' => 'success',
                    'response_message' => $this->extractMessage($responseBody, 'Request ke Fingerspot berhasil'),
                    'processed_at' => now(),
                ]);

                return [
                    'ok' => true,
                    'message' => $this->extractMessage($responseBody, 'Request ke Fingerspot berhasil'),
                    'response' => $responseBody,
                    'push_log_id' => $pushLog->id,
                ];
            }

            $pushLog->update([
                'payload_json' => $this->toJson([
                    'endpoint' => $endpoint,
                    'request' => $requestPayload,
                    'response' => $responseBody,
                    'http_status' => $response->status(),
                ]),
                'status' => 'failed',
                'response_message' => $this->extractMessage($responseBody, 'Request ke Fingerspot gagal'),
                'processed_at' => now(),
            ]);

            return [
                'ok' => false,
                'message' => $this->extractMessage($responseBody, 'Request ke Fingerspot gagal'),
                'response' => $responseBody,
                'push_log_id' => $pushLog->id,
            ];
        } catch (\Throwable $e) {
            $pushLog->update([
                'status' => 'error',
                'response_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);

            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'response' => null,
                'push_log_id' => $pushLog->id,
            ];
        }
    }

    protected function resolveCloudId(FingerspotDevice $device): ?string
    {
        return $this->nullableTrim($device->cloud_id ?: $device->serial_number);
    }

    protected function extractMessage(array $responseBody, string $default): string
    {
        return (string) (
            $responseBody['message']
            ?? $responseBody['msg']
            ?? $responseBody['status']
            ?? $default
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

    protected function toJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}