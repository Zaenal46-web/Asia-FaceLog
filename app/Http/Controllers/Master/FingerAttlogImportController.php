<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Services\Fingerspot\AttlogIngestService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FingerAttlogImportController extends Controller
{
    public function create()
    {
        $devices = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        return view('integrasi.sinkronisasi-log.index', compact('devices'));
    }

    public function store(Request $request, AttlogIngestService $ingestService)
{
    $validated = $request->validate([
        'device_id' => ['required', 'exists:fingerspot_devices,id'],
        'file' => ['required', 'file', 'max:10240'],
    ]);

    $device = FingerspotDevice::findOrFail($validated['device_id']);
    $file = $request->file('file');

    $ext = strtolower($file->getClientOriginalExtension());

    if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
        return back()->withErrors([
            'file' => 'File harus berformat .xlsx, .xls, atau .csv',
        ])->withInput();
    }

    \Log::info('Manual attlog import upload info', [
        'original_name' => $file->getClientOriginalName(),
        'original_extension' => $file->getClientOriginalExtension(),
        'client_mime' => $file->getClientMimeType(),
        'server_mime' => $file->getMimeType(),
        'size' => $file->getSize(),
    ]);

    $spreadsheet = IOFactory::load($file->getRealPath());
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, false, false, false);

    if (empty($rows)) {
        return back()->with('error', 'File Excel kosong atau tidak terbaca.');
    }

    $headerIndex = $this->findHeaderRowIndex($rows);

    if ($headerIndex === null) {
        return back()->with('error', 'Header Excel tidak dikenali. Pastikan ada kolom No., Status, dan Waktu.');
    }

    $headers = $this->normalizeHeaderRow($rows[$headerIndex]);
    $dataRows = array_slice($rows, $headerIndex + 1);

    $mappedRows = [];
    $skippedNonSuccess = 0;
    $skippedEmpty = 0;

    foreach ($dataRows as $row) {
        $assoc = $this->rowToAssoc($headers, $row);

        $pin = trim((string) ($assoc['no.'] ?? $assoc['no'] ?? ''));
        $status = trim((string) ($assoc['status'] ?? ''));
        $scanTimeRaw = $assoc['waktu'] ?? '';
        $scanTime = $this->normalizeExcelDateTime($scanTimeRaw);

        if ($pin === '' && $scanTime === '' && $status === '') {
            $skippedEmpty++;
            continue;
        }

        if ($pin === '' || $scanTime === '') {
            $skippedEmpty++;
            continue;
        }

        if (! $this->isSuccessStatus($status)) {
            $skippedNonSuccess++;
            continue;
        }

        $mappedRows[] = [
            'pin' => $pin,
            'scan_date' => $scanTime,
            'verify' => null,
            'status_scan' => 0,
            'photo_url' => null,
            'device_sn' => $device->serial_number,
            'nama_pengguna' => trim((string) ($assoc['nama pengguna'] ?? '')),
            'status_excel' => $status,
            'status_kehadiran' => trim((string) ($assoc['status kehadiran'] ?? '')),
            'metode_buka' => trim((string) ($assoc['metode buka'] ?? '')),
            'masker_wajah' => trim((string) ($assoc['masker wajah'] ?? '')),
            'helm_pengaman' => trim((string) ($assoc['helm pengaman'] ?? '')),
            'imported_from' => 'machine_excel',
        ];
    }

    $syncBatch = 'manual_import:' . $device->id . ':' . now()->format('YmdHis');

    $summary = $ingestService->ingestMany(
        device: $device,
        rows: $mappedRows,
        sourceChannel: 'manual_import',
        vendorTransId: null,
        syncBatch: $syncBatch,
        receivedAt: now(),
    );

    \Log::info('Manual attlog import summary', [
        'device_id' => $device->id,
        'device_name' => $device->nama,
        'file_name' => $file->getClientOriginalName(),
        'mapped_rows' => count($mappedRows),
        'skipped_non_success' => $skippedNonSuccess,
        'skipped_empty' => $skippedEmpty,
        'inserted' => $summary['inserted'],
        'duplicate' => $summary['duplicate'],
        'invalid' => $summary['invalid'],
        'invalid_samples' => $summary['invalid_samples'],
        'duplicate_samples' => $summary['duplicate_samples'],
        'sync_batch' => $syncBatch,
    ]);

    return back()->with(
        'success',
        'Import attlog selesai. '
        . 'Berhasil dipetakan: ' . count($mappedRows)
        . '. Inserted: ' . $summary['inserted']
        . '. Duplicate: ' . $summary['duplicate']
        . '. Invalid: ' . $summary['invalid']
        . '. Dilewati non-berhasil: ' . $skippedNonSuccess
        . '. Dilewati kosong: ' . $skippedEmpty . '.'
    );
}

    protected function findHeaderRowIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $normalized = $this->normalizeHeaderRow($row);

            if (
                (in_array('no.', $normalized, true) || in_array('no', $normalized, true))
                && in_array('status', $normalized, true)
                && in_array('waktu', $normalized, true)
            ) {
                return $index;
            }
        }

        return null;
    }

    protected function normalizeHeaderRow(array $row): array
    {
        return array_map(fn ($value) => $this->normalizeHeaderCell($value), $row);
    }

    protected function normalizeHeaderCell($value): string
    {
        $value = trim((string) $value);
        $value = mb_strtolower($value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    protected function rowToAssoc(array $headers, array $row): array
    {
        $assoc = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $assoc[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $assoc;
    }

    protected function isSuccessStatus(?string $status): bool
    {
        $status = trim((string) $status);
        $status = mb_strtolower($status);

        return in_array($status, ['berhasil', 'success', 'sukses'], true);
    }

    protected function normalizeExcelDateTime($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                return '';
            }
        }

        $value = trim((string) $value);

        $formats = [
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
            'd/m/Y H:i:s',
            'm/d/Y H:i:s',
            'Y/m/d H:i:s',
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt !== false) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return '';
        }
    }
}