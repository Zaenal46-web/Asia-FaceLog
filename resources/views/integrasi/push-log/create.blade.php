<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Tambah Push Log',
        'subtitle' => 'Tambahkan data push log manual untuk testing atau audit.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('integrasi.push-log.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                <label class="mb-2 block text-sm font-semibold text-slate-700">Action</label>
                <input type="text" name="action" value="{{ old('action') }}"
                    placeholder="Contoh: create_user / update_user / delete_user"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <input type="text" name="status" value="{{ old('status', 'pending') }}"
                    placeholder="pending / success / failed"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Processed At</label>
                <input type="datetime-local" name="processed_at" value="{{ old('processed_at') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div></div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Response Message</label>
                <textarea name="response_message" rows="3"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('response_message') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Payload JSON</label>
                <textarea name="payload_json" rows="10"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('payload_json') }}</textarea>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan
                </button>

                <a href="{{ route('integrasi.push-log.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>