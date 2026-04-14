<?php

namespace App\Services\Attendance;

use App\Models\AbsensiHarian;
use App\Models\FingerspotAttlog;
use App\Models\KategoriKaryawan;
use App\Models\KategoriShift;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProsesAbsensiCoreService
{
    public function prosesTanggal(string $tanggal, ?int $deviceId = null): array
    {
        $tanggal = Carbon::parse($tanggal)->toDateString();

        $karyawans = Karyawan::query()
        ->where('is_active', true)
        ->whereNotNull('pin_fingerspot')
        ->where('pin_fingerspot', '!=', '')
        ->whereNotNull('kategori_karyawan_id')
        ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
        ->get();

        $processed = 0;
        $upserted = 0;
        $skippedManual = 0;
        $noRule = 0;
        $noScan = 0;
        $unmatched = 0;

        DB::beginTransaction();

        try {
            foreach ($karyawans as $karyawan) {
                $existing = AbsensiHarian::query()
                    ->where('karyawan_id', $karyawan->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                if ($existing && $existing->is_manual) {
                    $skippedManual++;
                    continue;
                }

                $logs = $this->getRelevantLogs($karyawan, $tanggal, $deviceId);
                $kategoriShiftRules = $this->getKategoriShiftRules($karyawan, $tanggal);

                if ($kategoriShiftRules->isEmpty()) {
                    $noRule++;

                    $this->upsertAbsensi(
                        $karyawan,
                        $tanggal,
                        null,
                        $logs,
                        $existing,
                        'Tidak ada rule shift aktif pada hari ini.'
                    );

                    $processed++;
                    $upserted++;
                    continue;
                }

                if ($logs->isEmpty()) {
                    $noScan++;

                    $this->upsertAbsensi(
                        $karyawan,
                        $tanggal,
                        null,
                        collect(),
                        $existing,
                        'Tidak ada scan pada range yang relevan.'
                    );

                    $processed++;
                    $upserted++;
                    continue;
                }

                $bestMatch = $this->pickBestShiftMatch($tanggal, $logs, $kategoriShiftRules);

                if (! $bestMatch) {
                    $unmatched++;

                    $this->upsertAbsensi(
                        $karyawan,
                        $tanggal,
                        null,
                        $logs,
                        $existing,
                        'Tidak ditemukan rule shift yang cocok dari scan yang tersedia.'
                    );

                    $processed++;
                    $upserted++;
                    continue;
                }

                $this->upsertAbsensi(
                    $karyawan,
                    $tanggal,
                    $bestMatch,
                    $logs,
                    $existing
                );

                $processed++;
                $upserted++;
            }

            DB::commit();

            return [
                'tanggal' => $tanggal,
                'processed' => $processed,
                'upserted' => $upserted,
                'skipped_manual' => $skippedManual,
                'no_rule' => $noRule,
                'no_scan' => $noScan,
                'unmatched' => $unmatched,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getRelevantLogs(Karyawan $karyawan, string $tanggal, ?int $deviceId = null): Collection
    {
        $start = Carbon::parse($tanggal)->subDay()->startOfDay();
        $end = Carbon::parse($tanggal)->addDay()->endOfDay();

        return FingerspotAttlog::query()
            ->where('pin', (string) $karyawan->pin_fingerspot)
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when(! $deviceId && $karyawan->device_id, fn ($q) => $q->where('device_id', $karyawan->device_id))
            ->whereBetween('scan_time', [
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s'),
            ])
            ->orderBy('scan_time')
            ->get();
    }

    protected function getKategoriShiftRules(Karyawan $karyawan, string $tanggal): Collection
    {
        if (! $karyawan->kategori_karyawan_id) {
            return collect();
        }

        $kategoriIds = $this->getKategoriLineageIds($karyawan->kategori_karyawan_id);

        $rules = KategoriShift::query()
            ->with('shiftMaster')
            ->where('is_active', true)
            ->whereIn('kategori_karyawan_id', $kategoriIds)
            ->orderBy('prioritas')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return $rules->filter(function (KategoriShift $rule) use ($tanggal) {
            return $this->isRuleActiveOnDate($rule, $tanggal);
        })->values();
    }

    protected function isRuleActiveOnDate(KategoriShift $rule, string $tanggal): bool
    {
        $shift = $rule->shiftMaster;

        if (! $shift || ! $shift->is_active) {
            return false;
        }

        $date = Carbon::parse($tanggal);
        $day = $date->dayOfWeekIso; // 1=Senin ... 6=Sabtu, 7=Minggu

        if ($day === 6) {
            return (bool) $shift->sabtu_aktif;
        }

        if ($day === 7) {
            return (bool) $shift->minggu_aktif;
        }

        return true;
    }

    protected function getKategoriLineageIds(int $kategoriId): array
    {
        $ids = [];
        $current = KategoriKaryawan::find($kategoriId);

        while ($current) {
            $ids[] = $current->id;
            $current = $current->parent_id ? KategoriKaryawan::find($current->parent_id) : null;
        }

        return array_unique($ids);
    }

    protected function pickBestShiftMatch(string $tanggal, Collection $logs, Collection $rules): ?array
    {
        $candidates = [];

        foreach ($rules as $rule) {
            $shift = $rule->shiftMaster;

            if (! $shift || ! $shift->jam_masuk || ! $shift->jam_pulang) {
                continue;
            }

            $candidate = $this->buildShiftCandidate($tanggal, $logs, $rule);

            if ($candidate) {
                $candidates[] = $candidate;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            return [
                $b['score'],
                $b['matched_scan_count'],
                $a['rule']->prioritas ?? 999999,
                (int) ($b['rule']->is_default ?? false),
                $a['rule']->id,
            ] <=> [
                $a['score'],
                $a['matched_scan_count'],
                $b['rule']->prioritas ?? 999999,
                (int) ($a['rule']->is_default ?? false),
                $b['rule']->id,
            ];
        });

        return $candidates[0] ?? null;
    }

    protected function buildShiftCandidate(string $tanggal, Collection $logs, KategoriShift $rule): ?array
    {
        $shift = $rule->shiftMaster;

        $shiftStart = Carbon::parse($tanggal . ' ' . $shift->jam_masuk);
        $shiftEnd = Carbon::parse($tanggal . ' ' . $shift->jam_pulang);

        $lintasHari = (bool) ($rule->lintas_hari ?? $shift->lintas_hari ?? false);
        if ($lintasHari || $shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        // sesuai migration kategori_shifts
        $windowMasukMulai = (int) ($rule->window_masuk_mulai_menit ?? -120);
        $windowMasukSelesai = (int) ($rule->window_masuk_selesai_menit ?? 180);
        $windowPulangMulai = (int) ($rule->window_pulang_mulai_menit ?? -180);
        $windowPulangSelesai = (int) ($rule->window_pulang_selesai_menit ?? 240);

        $masukStart = $shiftStart->copy()->addMinutes($windowMasukMulai);
        $masukEnd = $shiftStart->copy()->addMinutes($windowMasukSelesai);
        $pulangStart = $shiftEnd->copy()->addMinutes($windowPulangMulai);
        $pulangEnd = $shiftEnd->copy()->addMinutes($windowPulangSelesai);

        $masukLogs = $logs->filter(function ($log) use ($masukStart, $masukEnd) {
            $scan = Carbon::parse($log->scan_time);
            return $scan->between($masukStart, $masukEnd);
        })->values();

        $pulangLogs = $logs->filter(function ($log) use ($pulangStart, $pulangEnd) {
            $scan = Carbon::parse($log->scan_time);
            return $scan->between($pulangStart, $pulangEnd);
        })->values();

        $allShiftLogs = $logs->filter(function ($log) use ($masukStart, $pulangEnd) {
            $scan = Carbon::parse($log->scan_time);
            return $scan->between($masukStart, $pulangEnd);
        })->values();

        if ($allShiftLogs->isEmpty()) {
            return null;
        }

        // hardening shift lintas hari:
        // wajib ada jejak scan di tanggal anchor supaya scan pagi next-day
        // tidak dicaplok jadi pulang hari sebelumnya
        $anchorDate = Carbon::parse($tanggal)->toDateString();

        if ($lintasHari) {
            $hasAnchorDateLog = $allShiftLogs->contains(function ($log) use ($anchorDate) {
                return Carbon::parse($log->scan_time)->toDateString() === $anchorDate;
            });

            if (! $hasAnchorDateLog) {
                return null;
            }
        }

        $jamMasuk = null;
        $jamPulang = null;
        $singleScanMode = null;

        // kasus scan tunggal
        if ($allShiftLogs->count() === 1) {
            $singleLog = $allShiftLogs->first();
            $singleScan = Carbon::parse($singleLog->scan_time);

            $isInMasukWindow = $singleScan->between($masukStart, $masukEnd);
            $isInPulangWindow = $singleScan->between($pulangStart, $pulangEnd);

            $diffToMasuk = abs($singleScan->diffInMinutes($shiftStart, false));
            $diffToPulang = abs($singleScan->diffInMinutes($shiftEnd, false));

            if ($isInMasukWindow && ! $isInPulangWindow) {
                $jamMasuk = $singleLog;
                $singleScanMode = 'masuk';
            } elseif (! $isInMasukWindow && $isInPulangWindow) {
                $jamPulang = $singleLog;
                $singleScanMode = 'pulang';
            } else {
                if ($diffToMasuk <= $diffToPulang) {
                    $jamMasuk = $singleLog;
                    $singleScanMode = 'masuk';
                } else {
                    $jamPulang = $singleLog;
                    $singleScanMode = 'pulang';
                }
            }
        } else {
            // kasus scan lebih dari 1
            $jamMasuk = $masukLogs->first();
            $jamPulang = $pulangLogs->last();

            if (! $jamMasuk) {
                $jamMasuk = $allShiftLogs->first();
            }

            // anti false pulang:
            // jangan fallback jam pulang ke scan terakhir sembarang
            // karena double scan saat masuk bisa salah dianggap pulang

            if ($jamMasuk && $jamPulang) {
                $masukTime = Carbon::parse($jamMasuk->scan_time);
                $pulangTime = Carbon::parse($jamPulang->scan_time);

                if ($masukTime->equalTo($pulangTime)) {
                    $jamPulang = null;
                }

                if ($jamPulang && $pulangTime->lessThan($masukTime)) {
                    $jamPulang = null;
                }
            }
        }

        if (! $jamMasuk && ! $jamPulang) {
            return null;
        }

        $score = 0;

        if ($masukLogs->isNotEmpty()) {
            $score += 50;
        }

        if ($pulangLogs->isNotEmpty()) {
            $score += 40;
        }

        if ($allShiftLogs->isNotEmpty()) {
            $score += min($allShiftLogs->count(), 10);
        }

        if ($rule->is_default) {
            $score += 3;
        }

        return [
            'rule' => $rule,
            'shift' => $shift,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'jam_masuk_log' => $jamMasuk,
            'jam_pulang_log' => $jamPulang,
            'matched_logs' => $allShiftLogs,
            'matched_scan_count' => $allShiftLogs->count(),
            'score' => $score,
            'single_scan_mode' => $singleScanMode,
        ];
    }

    protected function upsertAbsensi(
        Karyawan $karyawan,
        string $tanggal,
        ?array $bestMatch,
        Collection $logs,
        ?AbsensiHarian $existing = null,
        ?string $forcedKeterangan = null
    ): void {
        $data = [
            'tanggal' => $tanggal,
            'karyawan_id' => $karyawan->id,
            'device_id' => $karyawan->device_id,
            'kategori_shift_id' => null,
            'shift_master_id' => null,
            'jam_masuk' => null,
            'jam_pulang' => null,
            'status_telat' => false,
            'menit_telat' => 0,
            'status_lembur' => false,
            'menit_lembur' => 0,
            'total_menit_kerja' => 0,
            'source' => 'fingerspot_attlogs',
            'scan_count' => $logs->count(),
            'first_scan_at' => $logs->first()?->scan_time,
            'last_scan_at' => $logs->last()?->scan_time,
            'is_manual' => false,
            'keterangan' => null,
        ];

        if ($bestMatch) {
            $rule = $bestMatch['rule'];
            $shift = $bestMatch['shift'];

            $jamMasuk = $bestMatch['jam_masuk_log']
                ? Carbon::parse($bestMatch['jam_masuk_log']->scan_time)
                : null;

            $jamPulang = $bestMatch['jam_pulang_log']
                ? Carbon::parse($bestMatch['jam_pulang_log']->scan_time)
                : null;

            $shiftStart = $bestMatch['shift_start'];
            $shiftEnd = $bestMatch['shift_end'];

            $toleransiTelat = (int) ($rule->toleransi_telat_menit ?? 0);
            $singleScanMode = $bestMatch['single_scan_mode'] ?? null;

            $menitTelat = 0;
            $statusTelat = false;

            if ($jamMasuk && $singleScanMode !== 'pulang') {
                if ($jamMasuk->greaterThan($shiftStart->copy()->addMinutes($toleransiTelat))) {
                    $menitTelat = $shiftStart->diffInMinutes($jamMasuk);
                    $statusTelat = true;
                }
            }

            $menitLembur = 0;
            $statusLembur = false;

            if ($jamPulang && $singleScanMode !== 'masuk') {
                if ($jamPulang->greaterThan($shiftEnd)) {
                    $menitLembur = $shiftEnd->diffInMinutes($jamPulang);
                    $statusLembur = $menitLembur > 0;
                }
            }

            $totalMenitKerja = 0;

            if ($jamMasuk && $jamPulang && $jamPulang->greaterThan($jamMasuk)) {
                $totalMenitKerja = $jamMasuk->diffInMinutes($jamPulang);

                if ((bool) ($rule->istirahat_aktif ?? false) && (bool) ($rule->istirahat_otomatis_potong ?? false)) {
                    $totalMenitKerja = max(
                        0,
                        $totalMenitKerja - (int) ($rule->menit_istirahat_default ?? 0)
                    );
                }
            }

            $data = array_merge($data, [
                'device_id' => $bestMatch['jam_masuk_log']?->device_id
                    ?? $bestMatch['jam_pulang_log']?->device_id
                    ?? $karyawan->device_id,
                'kategori_shift_id' => $rule->id,
                'shift_master_id' => $shift->id,
                'jam_masuk' => $jamMasuk?->format('H:i:s'),
                'jam_pulang' => $jamPulang?->format('H:i:s'),
                'status_telat' => $statusTelat,
                'menit_telat' => $menitTelat,
                'status_lembur' => $statusLembur,
                'menit_lembur' => $menitLembur,
                'total_menit_kerja' => $totalMenitKerja,
                'scan_count' => $bestMatch['matched_logs']->count(),
                'first_scan_at' => $bestMatch['matched_logs']->first()?->scan_time,
                'last_scan_at' => $bestMatch['matched_logs']->last()?->scan_time,
                'keterangan' => $this->buildKeterangan($bestMatch, $jamMasuk, $jamPulang),
            ]);
        } else {
            $data['keterangan'] = $forcedKeterangan ?: 'Tidak ditemukan rule shift yang cocok dari scan yang tersedia.';
        }

        if ($existing) {
            $existing->update($data);
            return;
        }

        AbsensiHarian::updateOrCreate(
            [
                'karyawan_id' => $karyawan->id,
                'tanggal' => $tanggal,
            ],
            $data
        );
    }

    protected function buildKeterangan(array $bestMatch, ?Carbon $jamMasuk, ?Carbon $jamPulang): string
    {
        $rule = $bestMatch['rule'];
        $shift = $bestMatch['shift'];

        $parts = [];
        $parts[] = 'Rule: ' . ($rule->nama_rule ?? ('Shift ' . $shift->nama));
        $parts[] = 'Shift: ' . $shift->nama;

        if (! empty($bestMatch['single_scan_mode'])) {
            $parts[] = 'Scan tunggal diklasifikasikan sebagai: ' . strtoupper($bestMatch['single_scan_mode']);
        }

        if ($jamMasuk) {
            $parts[] = 'Masuk: ' . $jamMasuk->format('d-m-Y H:i:s');
        }

        if ($jamPulang) {
            $parts[] = 'Pulang: ' . $jamPulang->format('d-m-Y H:i:s');
        }

        $parts[] = 'Scan cocok: ' . $bestMatch['matched_logs']->count();

        return implode(' | ', $parts);
    }
}