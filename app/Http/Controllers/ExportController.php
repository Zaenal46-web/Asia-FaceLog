<?php

namespace App\Http\Controllers;

use App\Models\AbsensiHarian;
use App\Models\FingerspotDevice;
use App\Models\ShiftMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $tanggalMulai = $request->get('tanggal_mulai', now()->timezone('Asia/Jakarta')->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', now()->timezone('Asia/Jakarta')->toDateString());
        $deviceId = $request->get('device_id');
        $shiftId = $request->get('shift_id');
        $status = trim((string) $request->get('status', ''));
        $search = trim((string) $request->get('search', ''));

        $query = AbsensiHarian::query()
            ->with(['karyawan.kategoriKaryawan.parent', 'device', 'shiftMaster'])
            ->whereHas('karyawan', function ($q) {
                $q->whereNotNull('kategori_karyawan_id');
            })
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId))
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'telat') {
                    $q->where('status_telat', true);
                }

                if ($status === 'belum_pulang') {
                    $q->whereNotNull('jam_masuk')->whereNull('jam_pulang');
                }

                if ($status === 'lengkap') {
                    $q->whereNotNull('jam_masuk')->whereNotNull('jam_pulang');
                }

                if ($status === 'alpha') {
                    $q->whereNull('jam_masuk')->whereNull('jam_pulang');
                }
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->whereNotNull('kategori_karyawan_id')
                        ->where(function ($qq) use ($search) {
                            $qq->where('nama', 'like', "%{$search}%")
                               ->orWhere('pin_fingerspot', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('tanggal')
            ->orderBy('karyawan_id');

        $previewItems = $query->paginate(20)->withQueryString();

        $deviceOptions = FingerspotDevice::query()->orderBy('nama')->get();
        $shiftOptions = ShiftMaster::query()->orderBy('nama')->get();

        $summaryBase = AbsensiHarian::query()
            ->whereHas('karyawan', function ($q) {
                $q->whereNotNull('kategori_karyawan_id');
            })
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId));

        $total = (clone $summaryBase)->count();
        $totalTelat = (clone $summaryBase)->where('status_telat', true)->count();
        $totalBelumPulang = (clone $summaryBase)->whereNotNull('jam_masuk')->whereNull('jam_pulang')->count();
        $totalLengkap = (clone $summaryBase)->whereNotNull('jam_masuk')->whereNotNull('jam_pulang')->count();

        return view('export.index', compact(
            'previewItems',
            'tanggalMulai',
            'tanggalSelesai',
            'deviceId',
            'shiftId',
            'status',
            'search',
            'deviceOptions',
            'shiftOptions',
            'total',
            'totalTelat',
            'totalBelumPulang',
            'totalLengkap'
        ));
    }

    public function downloadXlsx(Request $request): StreamedResponse
    {
        $tanggalMulai = Carbon::parse($request->get('tanggal_mulai', now()->timezone('Asia/Jakarta')->toDateString()))->toDateString();
        $tanggalSelesai = Carbon::parse($request->get('tanggal_selesai', now()->timezone('Asia/Jakarta')->toDateString()))->toDateString();
        $deviceId = $request->get('device_id');
        $shiftId = $request->get('shift_id');
        $status = trim((string) $request->get('status', ''));
        $search = trim((string) $request->get('search', ''));

        $device = $deviceId ? FingerspotDevice::find($deviceId) : null;
        $shift = $shiftId ? ShiftMaster::find($shiftId) : null;

        $rows = AbsensiHarian::query()
            ->with(['karyawan.kategoriKaryawan.parent', 'device', 'shiftMaster'])
            ->whereHas('karyawan', function ($q) {
                $q->whereNotNull('kategori_karyawan_id');
            })
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($shiftId, fn ($q) => $q->where('shift_master_id', $shiftId))
            ->when($status !== '', function ($q) use ($status) {
                if ($status === 'telat') {
                    $q->where('status_telat', true);
                }

                if ($status === 'belum_pulang') {
                    $q->whereNotNull('jam_masuk')->whereNull('jam_pulang');
                }

                if ($status === 'lengkap') {
                    $q->whereNotNull('jam_masuk')->whereNotNull('jam_pulang');
                }

                if ($status === 'alpha') {
                    $q->whereNull('jam_masuk')->whereNull('jam_pulang');
                }
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->whereNotNull('kategori_karyawan_id')
                        ->where(function ($qq) use ($search) {
                            $qq->where('nama', 'like', "%{$search}%")
                               ->orWhere('pin_fingerspot', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('tanggal')
            ->orderBy('karyawan_id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheetExcel = $spreadsheet->getActiveSheet();
        $sheetExcel->setTitle('Export Absensi');

        $lastColumn = 'R';

        $sheetExcel->mergeCells("A1:{$lastColumn}1");
        $sheetExcel->setCellValue('A1', 'LAPORAN EXPORT ABSENSI FACELOG V2');
        $sheetExcel->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1D4ED8'],
            ],
        ]);
        $sheetExcel->getRowDimension(1)->setRowHeight(28);

        $meta = [
            ['Periode', Carbon::parse($tanggalMulai)->format('d-m-Y') . ' s/d ' . Carbon::parse($tanggalSelesai)->format('d-m-Y')],
            ['Device', $device?->nama ?? 'Semua Device'],
            ['Shift', $shift?->nama ?? 'Semua Shift'],
            ['Status Filter', $status !== '' ? strtoupper($status) : 'SEMUA'],
            ['Pencarian', $search !== '' ? $search : '-'],
            ['Total Data', $rows->count()],
        ];

        $metaStartRow = 3;
        foreach ($meta as $i => $row) {
            $r = $metaStartRow + $i;
            $sheetExcel->setCellValue("A{$r}", $row[0]);
            $sheetExcel->setCellValue("B{$r}", $row[1]);
        }

        $sheetExcel->getStyle("A{$metaStartRow}:A" . ($metaStartRow + count($meta) - 1))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']],
        ]);

        $headerRow = 11;
        $headers = [
            'No',
            'Tanggal',
            'Nama',
            'PIN',
            'Kategori',
            'Parent Kategori',
            'Device',
            'Shift',
            'Jam Masuk',
            'Jam Pulang',
            'Status Hadir',
            'Telat',
            'Menit Telat',
            'Lembur',
            'Menit Lembur',
            'Total Menit Kerja',
            'Scan Count',
            'Keterangan',
        ];

        foreach ($headers as $index => $header) {
            $col = Coordinate::stringFromColumnIndex($index + 1);
            $sheetExcel->setCellValue("{$col}{$headerRow}", $header);
        }

        $sheetExcel->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
        $sheetExcel->getRowDimension($headerRow)->setRowHeight(22);

        $rowNum = $headerRow + 1;
        foreach ($rows as $index => $item) {
            $sheetExcel->setCellValue("A{$rowNum}", $index + 1);
            $sheetExcel->setCellValue("B{$rowNum}", optional($item->tanggal)->format('d-m-Y'));
            $sheetExcel->setCellValue("C{$rowNum}", $item->karyawan?->nama);
            $sheetExcel->setCellValueExplicit("D{$rowNum}", (string) ($item->karyawan?->pin_fingerspot ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheetExcel->setCellValue("E{$rowNum}", $item->karyawan?->kategoriKaryawan?->nama);
            $sheetExcel->setCellValue("F{$rowNum}", $item->karyawan?->kategoriKaryawan?->parent?->nama);
            $sheetExcel->setCellValue("G{$rowNum}", $item->device?->nama);
            $sheetExcel->setCellValue("H{$rowNum}", $item->shiftMaster?->nama);
            $sheetExcel->setCellValue("I{$rowNum}", $item->jam_masuk ?: '-');
            $sheetExcel->setCellValue("J{$rowNum}", $item->jam_pulang ?: '-');
            $sheetExcel->setCellValue("K{$rowNum}", $item->status_hadir ?: '-');
            $sheetExcel->setCellValue("L{$rowNum}", $item->status_telat ? 'Ya' : 'Tidak');
            $sheetExcel->setCellValue("M{$rowNum}", $item->menit_telat ?? 0);
            $sheetExcel->setCellValue("N{$rowNum}", $item->status_lembur ? 'Ya' : 'Tidak');
            $sheetExcel->setCellValue("O{$rowNum}", $item->menit_lembur ?? 0);
            $sheetExcel->setCellValue("P{$rowNum}", $item->total_menit_kerja ?? 0);
            $sheetExcel->setCellValue("Q{$rowNum}", $item->scan_count ?? 0);
            $sheetExcel->setCellValue("R{$rowNum}", preg_replace('/\s+/', ' ', (string) ($item->keterangan ?? '-')));

            $rowNum++;
        }

        $lastDataRow = max($headerRow + 1, $rowNum - 1);

        $sheetExcel->getStyle("A{$headerRow}:{$lastColumn}{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
            if ($r % 2 === 0) {
                $sheetExcel->getStyle("A{$r}:{$lastColumn}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }
        }

        $sheetExcel->getStyle("C" . ($headerRow + 1) . ":R{$lastDataRow}")
            ->getAlignment()
            ->setWrapText(true);

        $sheetExcel->freezePane('A12');
        $sheetExcel->setAutoFilter("A{$headerRow}:{$lastColumn}{$lastDataRow}");

        $widths = [
            'A' => 6,
            'B' => 14,
            'C' => 28,
            'D' => 12,
            'E' => 22,
            'F' => 22,
            'G' => 20,
            'H' => 18,
            'I' => 12,
            'J' => 12,
            'K' => 14,
            'L' => 10,
            'M' => 12,
            'N' => 10,
            'O' => 14,
            'P' => 16,
            'Q' => 12,
            'R' => 50,
        ];

        foreach ($widths as $col => $width) {
            $sheetExcel->getColumnDimension($col)->setWidth($width);
        }

        $sheetExcel->setCellValue('T3', 'Ringkasan');
        $sheetExcel->setCellValue('T4', 'Total Data');
        $sheetExcel->setCellValue('U4', $rows->count());
        $sheetExcel->setCellValue('T5', 'Telat');
        $sheetExcel->setCellValue('U5', $rows->where('status_telat', true)->count());
        $sheetExcel->setCellValue('T6', 'Belum Pulang');
        $sheetExcel->setCellValue('U6', $rows->filter(fn ($x) => !empty($x->jam_masuk) && empty($x->jam_pulang))->count());
        $sheetExcel->setCellValue('T7', 'Lengkap');
        $sheetExcel->setCellValue('U7', $rows->filter(fn ($x) => !empty($x->jam_masuk) && !empty($x->jam_pulang))->count());

        $sheetExcel->getStyle('T3:U7')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
        $sheetExcel->getStyle('T3:U3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
        ]);
        $sheetExcel->getStyle('T4:T7')->getFont()->setBold(true);

        $filename = 'export-absensi-' . $tanggalMulai . '_sd_' . $tanggalSelesai . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}