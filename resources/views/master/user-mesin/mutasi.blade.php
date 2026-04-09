<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Mutasi User Mesin',
        'subtitle' => 'Pindahkan user mesin ke device tujuan dengan deteksi konflik PIN dan opsi PIN baru.'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('master.user-mesin.mutasi-device', $userMesin) }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @csrf

                <input type="hidden" name="user_device_id" value="{{ $userMesin->device_id }}">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Device Asal</label>
                    <input type="text" value="{{ $userMesin->device?->nama ?? '-' }}" disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Device Tujuan</label>
                    <select name="device_tujuan_id" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="">-- Pilih Device Tujuan --</option>
                        @foreach($deviceOptions as $device)
                            <option value="{{ $device->id }}" @selected(old('device_tujuan_id') == $device->id)>{{ $device->nama }}</option>
                        @endforeach
                    </select>
                    @error('device_tujuan_id') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">PIN Lama</label>
                    <input type="text" value="{{ $userMesin->pin }}" disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">PIN Baru (opsional)</label>
                    <input type="text" name="pin_baru" value="{{ old('pin_baru') }}"
                           placeholder="Isi hanya jika PIN tujuan bentrok"
                           class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @error('pin_baru') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                    <input type="text" value="{{ $userMesin->nama }}" disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Template Tersedia</label>
                    <input type="text" value="{{ $userMesin->template ? 'Ya' : 'Tidak' }}" disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div class="md:col-span-2 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    Sistem akan cek konflik PIN di device tujuan. Jika PIN lama sudah dipakai, isi PIN baru agar mutasi tetap bisa dilanjutkan dengan aman.
                </div>

                <div class="md:col-span-2 flex flex-col gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="update_master_karyawan" value="0">
                        <input type="checkbox" name="update_master_karyawan" value="1" checked class="rounded border-slate-300 text-blue-600">
                        Update device dan PIN pada Master Karyawan
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="hapus_dari_device_lama" value="0">
                        <input type="checkbox" name="hapus_dari_device_lama" value="1" class="rounded border-slate-300 text-blue-600">
                        Hapus dari device lama setelah sukses kirim ke device tujuan
                    </label>
                </div>

                <div class="md:col-span-2 flex items-center gap-3">
                    <button type="submit"
                            onclick='return confirm("Lanjutkan mutasi user mesin ini ke device tujuan?")'
                            class="inline-flex rounded-2xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white hover:bg-violet-700">
                        Proses Mutasi
                    </button>

                    <a href="{{ route('master.user-mesin.index') }}"
                       class="inline-flex rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Kembali
                    </a>
                </div>
            </form>
        </div>

        @if($konflikDevices->isNotEmpty())
            <div class="rounded-3xl border border-amber-200 bg-white p-6 shadow-sm">
                <div class="mb-4 text-sm font-bold uppercase tracking-wide text-amber-700">
                    Info Konflik PIN di Device Lain
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Synced At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($konflikDevices as $konflik)
                                    <tr>
                                        <td class="px-4 py-4 text-sm text-slate-700">{{ $konflik->device?->nama }}</td>
                                        <td class="px-4 py-4 text-sm text-slate-700">{{ $konflik->pin }}</td>
                                        <td class="px-4 py-4 text-sm text-slate-700">{{ $konflik->nama }}</td>
                                        <td class="px-4 py-4 text-sm text-slate-700">{{ $konflik->synced_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>