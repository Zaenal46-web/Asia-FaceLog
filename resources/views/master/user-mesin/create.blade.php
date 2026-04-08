<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Tambah User Mesin',
        'subtitle' => 'Tambahkan user mesin secara manual untuk kebutuhan sinkronisasi awal atau koreksi data.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('master.user-mesin.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device Mesin</label>
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
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Privilege</label>
                <select name="privilege" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Pilih Privilege --</option>
                    @foreach ($privilegeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('privilege') === $value)>
                            {{ $label }} ({{ $value }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                <input type="text" name="password" value="{{ old('password') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">RFID</label>
                <input type="text" name="rfid" value="{{ old('rfid') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Face Template Count</label>
                <input type="number" name="face_template_count" value="{{ old('face_template_count', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Finger Template Count</label>
                <input type="number" name="finger_template_count" value="{{ old('finger_template_count', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Vein Template Count</label>
                <input type="number" name="vein_template_count" value="{{ old('vein_template_count', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Synced At</label>
                <input type="datetime-local" name="synced_at" value="{{ old('synced_at') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan
                </button>

                <a href="{{ route('master.user-mesin.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>