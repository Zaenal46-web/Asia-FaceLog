<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Export Absensi',
        'subtitle' => 'Filter dan unduh data absensi harian FaceLog v2 dalam format CSV yang siap dibuka di Excel.'
    ])

    <div class="-mt-4 mb-6 flex flex-wrap items-center justify-end gap-2">
        <a href="{{ route('export.download-xlsx', request()->query()) }}"
            class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            Download Excel Premium
        </a>
    </div>

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Data</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($total) }}</div>
        </div>

        <div class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-amber-600">Telat</div>
            <div class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($totalTelat) }}</div>
        </div>

        <div class="rounded-3xl border border-rose-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-rose-600">Belum Pulang</div>
            <div class="mt-3 text-3xl font-bold text-rose-700">{{ number_format($totalBelumPulang) }}</div>
        </div>

        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-emerald-600">Lengkap</div>
            <div class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($totalLengkap) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('export.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device</label>
                <select name="device_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    @foreach($deviceOptions as $device)
                        <option value="{{ $device->id }}" @selected((string)$deviceId === (string)$device->id)>{{ $device->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Shift</label>
                <select name="shift_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    @foreach($shiftOptions as $shift)
                        <option value="{{ $shift->id }}" @selected((string)$shiftId === (string)$shift->id)>{{ $shift->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="telat" @selected($status === 'telat')>Telat</option>
                    <option value="belum_pulang" @selected($status === 'belum_pulang')>Belum Pulang</option>
                    <option value="lengkap" @selected($status === 'lengkap')>Lengkap</option>
                    <option value="alpha" @selected($status === 'alpha')>Alpha</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / PIN"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>

            <div class="md:col-span-6 flex items-end gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('export.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Shift</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Telat</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Scan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($previewItems as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-sm text-slate-700">{{ optional($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->karyawan?->nama }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->karyawan?->pin_fingerspot }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->shiftMaster?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->jam_masuk ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->jam_pulang ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->status_telat ? $item->menit_telat . ' mnt' : '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->scan_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data export untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $previewItems->links() }}
        </div>
    </div>
</x-app-layout>