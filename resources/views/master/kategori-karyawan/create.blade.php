<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Tambah Kategori Karyawan',
        'subtitle' => 'Buat kategori parent atau subkategori baru sesuai struktur perusahaan.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('master.kategori-karyawan.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Parent Kategori</label>
                <select name="parent_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Tidak Ada / Kategori Utama --</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>
                            {{ $parent->nama }} ({{ $parent->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Kategori</label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kode</label>
                <input type="text" name="kode" value="{{ old('kode') }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="flex items-center gap-3 pt-8">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', 1))
                    class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif</label>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan
                </button>

                <a href="{{ route('master.kategori-karyawan.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>