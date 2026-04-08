<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Webhook Log',
        'subtitle' => 'Kelola dan audit log request webhook dari mesin Fingerspot untuk debugging integrasi dan validasi payload.',
        'actions' => '<a href="' . route('integrasi.webhook-log.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Webhook Log</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Log</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalLog) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Device Terdeteksi</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalDeviceTerdeteksi) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Status Received</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalReceived) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Status Error</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalError) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('integrasi.webhook-log.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
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
                <label class="mb-2 block text-sm font-semibold text-slate-700">Event Type</label>
                <input
                    type="text"
                    name="event_type"
                    value="{{ $eventType }}"
                    placeholder="Contoh: attlog"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <input
                    type="text"
                    name="status"
                    value="{{ $status }}"
                    placeholder="received / error"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div class="md:col-span-6 flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('integrasi.webhook-log.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Received At</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Event Type</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Message</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm text-slate-700">
                                    {{ $item->received_at ? $item->received_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $item->device?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->event_type ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    @if ($item->status === 'received')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            received
                                        </span>
                                    @elseif ($item->status === 'error')
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            error
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->message ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('integrasi.webhook-log.show', $item) }}"
                                           class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Detail
                                        </a>

                                        <a href="{{ route('integrasi.webhook-log.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('integrasi.webhook-log.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus webhook log ini?')">
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
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data webhook log.
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