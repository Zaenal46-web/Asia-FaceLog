<?php

namespace App\Services\Attendance;

use App\Models\KategoriShift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftResolverService
{
    public function __construct(
        protected ScanClassifierService $scanClassifierService
    ) {}

    public function pickBestShiftMatch(string $tanggal, Collection $logs, Collection $rules): ?array
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
        $shiftEnd   = Carbon::parse($tanggal . ' ' . $shift->jam_pulang);

        $lintasHari = (bool) ($rule->lintas_hari ?? $shift->lintas_hari ?? false);
        if ($lintasHari || $shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $windowMasukBefore  = (int) ($rule->window_masuk_before_menit ?? 120);
        $windowMasukAfter   = (int) ($rule->window_masuk_after_menit ?? 180);
        $windowPulangBefore = (int) ($rule->window_pulang_before_menit ?? 240);
        $windowPulangAfter  = (int) ($rule->window_pulang_after_menit ?? 240);

        $masukStart  = $shiftStart->copy()->subMinutes($windowMasukBefore);
        $masukEnd    = $shiftStart->copy()->addMinutes($windowMasukAfter);
        $pulangStart = $shiftEnd->copy()->subMinutes($windowPulangBefore);
        $pulangEnd   = $shiftEnd->copy()->addMinutes($windowPulangAfter);

        $masukLogs = $logs->filter(fn ($log) => Carbon::parse($log->scan_time)->between($masukStart, $masukEnd))->values();
        $pulangLogs = $logs->filter(fn ($log) => Carbon::parse($log->scan_time)->between($pulangStart, $pulangEnd))->values();
        $allShiftLogs = $logs->filter(fn ($log) => Carbon::parse($log->scan_time)->between($masukStart, $pulangEnd))->values();

        if ($allShiftLogs->isEmpty()) {
            return null;
        }

        $classified = $this->scanClassifierService->classify(
            $allShiftLogs,
            $masukLogs,
            $pulangLogs,
            $shiftStart,
            $shiftEnd,
            $masukStart,
            $masukEnd,
            $pulangStart,
            $pulangEnd
        );

        $score = 0;
        if ($masukLogs->isNotEmpty()) $score += 50;
        if ($pulangLogs->isNotEmpty()) $score += 40;
        if ($allShiftLogs->isNotEmpty()) $score += min($allShiftLogs->count(), 10);
        if ($rule->is_default) $score += 3;

        return [
            'rule' => $rule,
            'shift' => $shift,
            'shift_start' => $shiftStart,
            'shift_end' => $shiftEnd,
            'jam_masuk_log' => $classified['jamMasuk'],
            'jam_pulang_log' => $classified['jamPulang'],
            'matched_logs' => $allShiftLogs,
            'matched_scan_count' => $allShiftLogs->count(),
            'score' => $score,
            'single_scan_mode' => $classified['singleScanMode'],
        ];
    }
}