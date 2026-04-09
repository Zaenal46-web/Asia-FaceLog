<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Edit Hari Libur',
        'subtitle' => 'Perbarui data kalender libur FaceLog v2.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('holiday.update', $holiday) }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Libur</label>
                <input type="text" name="nama" value="{{ old('nama', $holiday->nama) }}"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                @error('nama') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tipe</label>
                <select name="tipe" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="nasional" @selected(old('tipe', $holiday->tipe) === 'nasional')>Nasional</option>
                    <option value="internal" @selected(old('tipe', $holiday->tipe) === 'internal')>Internal</option>
                    <option value="khusus" @selected(old('tipe', $holiday->tipe) === 'khusus')>Khusus</option>
                </select>
                @error('tipe') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', optional($holiday->tanggal_mulai)->format('Y-m-d')) }}"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                @error('tanggal_mulai') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($holiday->tanggal_selesai)->format('Y-m-d')) }}"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                @error('tanggal_selesai') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Keterangan</label>
                <textarea name="keterangan" rows="4"
                          class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">{{ old('keterangan', $holiday->keterangan) }}</textarea>
                @error('keterangan') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $holiday->is_active)) class="rounded border-slate-300 text-blue-600">
                    Aktif
                </label>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Update
                </button>

                <a href="{{ route('holiday.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>