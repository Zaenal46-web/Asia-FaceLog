<?php

namespace App\Services\Attendance;

use App\Models\HolidayCalendar;

class HolidayResolverService
{
    public function resolve(string $tanggal): array
    {
        $holiday = HolidayCalendar::query()
            ->where('is_active', true)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->orderBy('tanggal_mulai')
            ->first();

        return [
            'is_holiday' => (bool) $holiday,
            'holiday_calendar_id' => $holiday?->id,
            'holiday_name' => $holiday?->nama,
            'holiday_type' => $holiday?->tipe,
        ];
    }
}