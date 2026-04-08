<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Edit Webhook Log',
        'subtitle' => 'Perbarui data webhook log untuk kebutuhan audit atau koreksi.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('integrasi.webhook-log.update', $webhookLog) }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device</label>
                <select name="device_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Pilih Device --</option>
                    @foreach ($deviceOptions as $device)
                        <option value="{{ $device->id }}" @selected(old('device_id', $webhookLog->device_id) == $device->id)>
                            {{ $device->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Event Type</label>
                <input type="text" name="event_type" value="{{ old('event_type', $webhookLog->event_type) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <input type="text" name="status" value="{{ old('status', $webhookLog->status) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Received At</label>
                <input type="datetime-local" name="received_at"
                    value="{{ old('received_at', $webhookLog->received_at ? $webhookLog->received_at->format('Y-m-d\TH:i') : '') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Message</label>
                <textarea name="message" rows="3"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('message', $webhookLog->message) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Payload JSON</label>
                <textarea name="payload_json" rows="10"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">{{ old('payload_json', $webhookLog->payload_json) }}</textarea>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Update
                </button>

                <a href="{{ route('integrasi.webhook-log.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>