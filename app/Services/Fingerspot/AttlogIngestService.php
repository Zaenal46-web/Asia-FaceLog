<?php

namespace App\Services\Fingerspot;

use App\Models\FingerspotAttlog;
use App\Models\FingerspotDevice;

class AttlogIngestService
{
    public function ingestRow(
        FingerspotDevice $device,
        array $row,
        string $sourceChannel,
        ?string $vendorTransId = null,
        ?string $syncBatch = null,
        ?\DateTimeInterface $receivedAt = null
    ): array {
        $pin = trim((string) ($row['pin'] ?? ''));
        $scan = $row['scan_date'] ?? $row['scan'] ?? $row['scan_time'] ?? null;
        $scan = is_string($scan) ? trim($scan) : $scan;

        if ($pin === '' || empty($scan)) {
            return [
                'status' => 'invalid',
                'message' => 'PIN atau scan_time kosong',
                'row' => $row,
            ];
        }

        $exists = FingerspotAttlog::query()
            ->where('device_id', $device->id)
            ->where('pin', $pin)
            ->where('scan_time', $scan)
            ->exists();

        if ($exists) {
            return [
                'status' => 'duplicate',
                'message' => 'Data sudah ada',
                'row' => [
                    'device_id' => $device->id,
                    'pin' => $pin,
                    'scan_time' => $scan,
                ],
            ];
        }

        $attlog = FingerspotAttlog::create([
            'device_id' => $device->id,
            'pin' => $pin,
            'device_sn' => $row['device_sn'] ?? $device->serial_number ?? null,
            'scan_time' => $scan,
            'verify_mode' => isset($row['verify']) ? (string) $row['verify'] : ($row['verify_mode'] ?? null),
            'status_scan' => isset($row['status_scan']) ? (string) $row['status_scan'] : ($row['status'] ?? null),
            'photo_url' => $row['photo_url'] ?? null,
            'source_channel' => $sourceChannel,
            'received_at' => $receivedAt ?? now(),
            'vendor_trans_id' => $vendorTransId,
            'sync_batch' => $syncBatch,
            'raw' => $row,
        ]);

        return [
            'status' => 'inserted',
            'message' => 'Berhasil disimpan',
            'attlog_id' => $attlog->id,
        ];
    }

    public function ingestMany(
        FingerspotDevice $device,
        array $rows,
        string $sourceChannel,
        ?string $vendorTransId = null,
        ?string $syncBatch = null,
        ?\DateTimeInterface $receivedAt = null
    ): array {
        $inserted = 0;
        $duplicate = 0;
        $invalid = 0;
        $invalidSamples = [];
        $duplicateSamples = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $invalid++;
                if (count($invalidSamples) < 5) {
                    $invalidSamples[] = [
                        'reason' => 'row_not_array',
                        'row' => $row,
                    ];
                }
                continue;
            }

            $result = $this->ingestRow(
                device: $device,
                row: $row,
                sourceChannel: $sourceChannel,
                vendorTransId: $vendorTransId,
                syncBatch: $syncBatch,
                receivedAt: $receivedAt,
            );

            if (($result['status'] ?? null) === 'inserted') {
                $inserted++;
                continue;
            }

            if (($result['status'] ?? null) === 'duplicate') {
                $duplicate++;
                if (count($duplicateSamples) < 5) {
                    $duplicateSamples[] = $result['row'] ?? [];
                }
                continue;
            }

            $invalid++;
            if (count($invalidSamples) < 5) {
                $invalidSamples[] = $result;
            }
        }

        return [
            'inserted' => $inserted,
            'duplicate' => $duplicate,
            'invalid' => $invalid,
            'invalid_samples' => $invalidSamples,
            'duplicate_samples' => $duplicateSamples,
        ];
    }
}