<?php

namespace App\Services\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScanClassifierService
{
    public function classify(
        Collection $allShiftLogs,
        Collection $masukLogs,
        Collection $pulangLogs,
        Carbon $shiftStart,
        Carbon $shiftEnd,
        Carbon $masukStart,
        Carbon $masukEnd,
        Carbon $pulangStart,
        Carbon $pulangEnd
    ): array {
        $jamMasuk = null;
        $jamPulang = null;
        $singleScanMode = null;

        if ($allShiftLogs->isEmpty()) {
            return compact('jamMasuk', 'jamPulang', 'singleScanMode');
        }

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

            return compact('jamMasuk', 'jamPulang', 'singleScanMode');
        }

        $jamMasuk = $masukLogs->first() ?: $allShiftLogs->first();
        $jamPulang = $pulangLogs->last();

        if (! $jamPulang && $allShiftLogs->count() > 1) {
            $jamPulang = $allShiftLogs->last();
        }

        if ($jamMasuk && $jamPulang) {
            $masukTime = Carbon::parse($jamMasuk->scan_time);
            $pulangTime = Carbon::parse($jamPulang->scan_time);

            if ($masukTime->equalTo($pulangTime) || $pulangTime->lessThan($masukTime)) {
                $jamPulang = null;
            }
        }

        return compact('jamMasuk', 'jamPulang', 'singleScanMode');
    }
}