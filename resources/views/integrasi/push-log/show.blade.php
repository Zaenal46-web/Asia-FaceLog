<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Detail Push Log',
        'subtitle' => 'Lihat detail lengkap pengiriman data ke device Fingerspot.'
    ])

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Device</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $pushLog->device?->nama ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">PIN</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $pushLog->pin ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Action</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $pushLog->action }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</div>
                    <div class="mt-2 text-sm text-slate-700">{{ $pushLog->status }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Processed At</div>
                    <div class="mt-2 text-sm text-slate-700">
                        {{ $pushLog->processed_at ? $pushLog->processed_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Response Message</div>
                    <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $pushLog->response_message ?: '-' }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Payload JSON</div>
                    <pre class="mt-2 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs text-slate-100">{{ $pushLog->payload_json ?: '-' }}</pre>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-lg font-bold text-slate-900">Aksi Cepat</div>

            <div class="mt-5 space-y-3">
                <a href="{{ route('integrasi.push-log.edit', $pushLog) }}"
                   class="block rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-100">
                    Edit Push Log
                </a>

                <a href="{{ route('integrasi.push-log.index') }}"
                   class="block rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali ke List
                </a>

                <form action="{{ route('integrasi.push-log.destroy', $pushLog) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus push log ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="block w-full rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-left text-sm font-semibold text-red-700 hover:bg-red-100">
                        Hapus Push Log
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>