<?php

namespace App\Services\Attendance;

use Carbon\Carbon;

class AttendanceCalculatorService
{
    public function calculate(
        ?Carbon $jamMasuk,
        ?Carbon $jamPulang,
        Carbon $shiftStart,
        Carbon $shiftEnd,
        int $toleransiTelat = 0,
        ?string $singleScanMode = null
    ): array {
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
        }

        return [
            'status_telat' => $statusTelat,
            'menit_telat' => $menitTelat,
            'status_lembur' => $statusLembur,
            'menit_lembur' => $menitLembur,
            'total_menit_kerja' => $totalMenitKerja,
        ];
    }
}