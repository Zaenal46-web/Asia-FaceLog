<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Dashboard FaceLog v2',
        'subtitle' => 'Ringkasan cepat absensi, device, scan harian, dan status operasional sistem dalam satu tampilan premium.'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Karyawan</div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['total_karyawan']) }}</div>
            <div class="mt-2 text-xs text-emerald-600">Karyawan aktif terdaftar</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Device Aktif</div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['total_device_aktif']) }}</div>
            <div class="mt-2 text-xs text-blue-600">Mesin aktif siap sinkron</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Scan Hari Ini</div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['scan_hari_ini']) }}</div>
            <div class="mt-2 text-xs text-amber-600">Raw log tanggal hari ini</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Absensi Diproses</div>
            <div class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($stats['absensi_diproses']) }}</div>
            <div class="mt-2 text-xs text-sky-600">Data absensi harian tersimpan</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-lg font-bold text-slate-900">Selamat datang di FaceLog v2</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Dashboard sekarang sudah membaca data asli dari database untuk statistik utama sistem.
                        </p>
                    </div>

                    <div class="hidden rounded-2xl bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-700 sm:block">
                        Live Data
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Struktur Sistem</div>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li>• Multi device attendance</li>
                            <li>• Raw log terpisah dari hasil olahan</li>
                            <li>• Role superadmin / HRD Asia / HRD Outsourcing</li>
                            <li>• Kalender libur dan rule shift kategori</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Progress Pondasi</div>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li>• Migration final selesai</li>
                            <li>• Model dan relasi selesai</li>
                            <li>• Seeder role dan kategori selesai</li>
                            <li>• Layout premium shell aktif</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Raw Log Terbaru</h3>
                        <p class="mt-1 text-sm text-slate-500">8 scan terakhir yang masuk ke sistem</p>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Waktu Scan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($latestRawLogs as $log)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $log->pin }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->device?->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ optional($log->scan_time)->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">
                                            Belum ada raw log.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-lg font-bold text-slate-900">Quick Menu</div>

                <div class="mt-5 space-y-3">
                    <a href="{{ route('master.karyawan.index') }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        Master Karyawan
                    </a>

                    <a href="{{ route('master.kategori-karyawan.index') }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        Kategori Karyawan
                    </a>

                    <a href="{{ route('master.device.index') }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        Device Mesin
                    </a>

                    <a href="{{ route('integrasi.raw-log.index') }}" class="block rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        Raw Log
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-lg font-bold text-slate-900">Karyawan Terbaru</div>
                <div class="mt-4 space-y-3">
                    @forelse ($latestKaryawans as $karyawan)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-sm font-semibold text-slate-800">{{ $karyawan->nama }}</div>
                            <div class="mt-1 text-xs text-slate-500">PIN: {{ $karyawan->pin_fingerspot ?: '-' }}</div>
                            <div class="mt-2 text-xs text-slate-600">
                                {{ $karyawan->kategoriKaryawan?->nama ?? '-' }} • {{ $karyawan->device?->nama ?? '-' }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                            Belum ada data karyawan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>