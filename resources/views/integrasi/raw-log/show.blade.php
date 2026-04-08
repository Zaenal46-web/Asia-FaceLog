<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Detail Raw Log',
        'subtitle' => 'Lihat detail lengkap scan mentah dari device Fingerspot.'
    ])

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">PIN</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $rawLog->pin }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Device</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $rawLog->device?->nama ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Device SN</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $rawLog->device_sn ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Scan Time</div>
                    <div class="mt-2 text-sm text-slate-700">
                        {{ $rawLog->scan_time ? $rawLog->scan_time->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Verify Mode</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $rawLog->verify_mode ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Scan</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $rawLog->status_scan ?: '-' }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Photo URL</div>
                    <div class="mt-2 text-sm text-slate-700 break-all">
                        @if ($rawLog->photo_url)
                            <a href="{{ $rawLog->photo_url }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ $rawLog->photo_url }}
                            </a>
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Raw Payload</div>
                    <pre class="mt-2 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs text-slate-100">{{ $rawLog->raw ?: '-' }}</pre>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-lg font-bold text-slate-900">Aksi Cepat</div>

            <div class="mt-5 space-y-3">
                <a href="{{ route('integrasi.raw-log.edit', $rawLog) }}"
                   class="block rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                    Edit Raw Log
                </a>

                <a href="{{ route('integrasi.raw-log.index') }}"
                   class="block rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali ke List
                </a>

                <form action="{{ route('integrasi.raw-log.destroy', $rawLog) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus raw log ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="block w-full rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-left text-sm font-semibold text-red-700 hover:bg-red-100">
                        Hapus Raw Log
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>