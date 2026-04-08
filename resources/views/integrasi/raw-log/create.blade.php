<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Tambah Raw Log',
        'subtitle' => 'Tambahkan data scan mentah manual untuk testing, koreksi, atau simulasi.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('integrasi.raw-log.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device</label>
                <select name="device_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Pilih Device --</option>
                    @foreach ($deviceOptions as $device)
                        <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>
                            {{ $device->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">PIN</label>
                <input type="text" name="pin" value="{{ old('pin') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device SN</label>
                <input type="text" name="device_sn" value="{{ old('device_sn') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Scan Time</label>
                <input type="datetime-local" name="scan_time" value="{{ old('scan_time') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Verify Mode</label>
                <input type="text" name="verify_mode" value="{{ old('verify_mode') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status Scan</label>
                <input type="text" name="status_scan" value="{{ old('status_scan') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Photo URL</label>
                <input type="url" name="photo_url" value="{{ old('photo_url') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Raw JSON / Raw Payload</label>
                <textarea name="raw" rows="8"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('raw') }}</textarea>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan
                </button>

                <a href="{{ route('integrasi.raw-log.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>