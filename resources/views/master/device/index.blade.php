<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Device Mesin',
        'subtitle' => 'Kelola mesin Fingerspot, lokasi perangkat, serial number, dan status aktif device untuk kebutuhan multi-device attendance.',
        'actions' => '<a href="' . route('master.device.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Device</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Device</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalDevice) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Device Aktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalActive) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Device Nonaktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalInactive) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Pernah Last Seen</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalOnlineHint) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('master.device.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="md:col-span-3">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari device</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, serial number, cloud id, lokasi, atau IP"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('master.device.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Serial Number</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Cloud ID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Timezone</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">IP Address</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Last Seen</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->nama }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->serial_number ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->cloud_id ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->lokasi ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->timezone }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->ip_address ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->last_seen_at ? $item->last_seen_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    @if ($item->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('master.device.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('master.device.get-all-pin', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                                Get All PIN
                                            </button>
                                        </form>

                                        <form action="{{ route('master.device.set-time', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                Set Time
                                            </button>
                                        </form>

                                        <form action="{{ route('master.device.get-attlog', $item) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="start_date" value="{{ now()->timezone('Asia/Jakarta')->toDateString() }}">
                                            <input type="hidden" name="end_date" value="{{ now()->timezone('Asia/Jakarta')->toDateString() }}">
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                                Get Attlog
                                            </button>
                                        </form>

                                        <form action="{{ route('master.device.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus device ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data device mesin.
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