<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Edit Shift Master',
        'subtitle' => 'Perbarui data shift kerja yang sudah ada.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('master.shift-master.update', $shiftMaster) }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Shift</label>
                <input type="text" name="nama" value="{{ old('nama', $shiftMaster->nama) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kode</label>
                <input type="text" name="kode" value="{{ old('kode', $shiftMaster->kode) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div></div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Jam Masuk</label>
                <input type="time" name="jam_masuk" value="{{ old('jam_masuk', \Carbon\Carbon::createFromFormat('H:i:s', $shiftMaster->jam_masuk)->format('H:i')) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Jam Pulang</label>
                <input type="time" name="jam_pulang" value="{{ old('jam_pulang', \Carbon\Carbon::createFromFormat('H:i:s', $shiftMaster->jam_pulang)->format('H:i')) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="lintas_hari" value="0">
                    <input type="checkbox" id="lintas_hari" name="lintas_hari" value="1" @checked(old('lintas_hari', $shiftMaster->lintas_hari))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="lintas_hari" class="text-sm font-semibold text-slate-700">Lintas Hari</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="sabtu_aktif" value="0">
                    <input type="checkbox" id="sabtu_aktif" name="sabtu_aktif" value="1" @checked(old('sabtu_aktif', $shiftMaster->sabtu_aktif))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="sabtu_aktif" class="text-sm font-semibold text-slate-700">Sabtu Aktif</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="minggu_aktif" value="0">
                    <input type="checkbox" id="minggu_aktif" name="minggu_aktif" value="1" @checked(old('minggu_aktif', $shiftMaster->minggu_aktif))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="minggu_aktif" class="text-sm font-semibold text-slate-700">Minggu Aktif</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $shiftMaster->is_active))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif</label>
                </div>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Update
                </button>

                <a href="{{ route('master.shift-master.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>