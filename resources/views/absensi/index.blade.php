<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Absensi Harian',
        'subtitle' => 'Proses dan monitor hasil absensi harian berdasarkan raw log realtime Fingerspot, kategori shift, dan engine FaceLog v2.'
    ])

    <div class="-mt-4 mb-6 flex flex-wrap items-center justify-end gap-2">
    <form action="{{ route('absensi.proses') }}" method="POST" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
        @csrf

        <input type="date"
               name="tanggal"
               value="{{ $tanggal }}"
               class="rounded-xl border border-slate-300 px-3 py-2 text-sm">

        <select name="device_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua Device</option>
            @foreach ($deviceOptions as $device)
                <option value="{{ $device->id }}" @selected((string) $deviceId === (string) $device->id)>
                    {{ $device->nama }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                onclick='return confirm("Jalankan proses absensi untuk tanggal ini?")'
                class="inline-flex items-center rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            Proses Absensi
        </button>
    </form>
</div>

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Data</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($total) }}</div>
        </div>

        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-emerald-600">Sudah Masuk</div>
            <div class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($totalMasuk) }}</div>
        </div>

        <div class="rounded-3xl border border-sky-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-sky-600">Sudah Pulang</div>
            <div class="mt-3 text-3xl font-bold text-sky-700">{{ number_format($totalPulang) }}</div>
        </div>

        <div class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-amber-600">Telat</div>
            <div class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($totalTelat) }}</div>
        </div>

        <div class="rounded-3xl border border-rose-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-rose-600">Belum Pulang</div>
            <div class="mt-3 text-3xl font-bold text-rose-700">{{ number_format($totalBelumPulang) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('absensi.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal</label>
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $tanggal }}"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device</label>
                <select
                    name="device_id"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    @foreach ($deviceOptions as $device)
                        <option value="{{ $device->id }}" @selected((string) $deviceId === (string) $device->id)>
                            {{ $device->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Shift</label>
                <select
                    name="shift_id"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    @foreach ($shiftOptions as $shift)
                        <option value="{{ $shift->id }}" @selected((string) $shiftId === (string) $shift->id)>
                            {{ $shift->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari Karyawan</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama / PIN"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div class="md:col-span-5 flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('absensi.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Shift</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jam Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jam Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Telat</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Lembur</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Scan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Keterangan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-semibold text-slate-800">{{ $item->karyawan?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->karyawan?->kategoriKaryawan?->nama ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->karyawan?->pin_fingerspot ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->device?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->shiftMaster?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->jam_masuk ?: '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->jam_pulang ?: '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    @if ($item->status_telat)
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                            {{ $item->menit_telat }} mnt
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Tepat
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($item->status_lembur)
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">
                                            {{ $item->menit_lembur }} mnt
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->scan_count ?? 0 }}
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-500 max-w-xs">
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data absensi harian untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>