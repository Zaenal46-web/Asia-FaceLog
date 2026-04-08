<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Edit Kategori Karyawan',
        'subtitle' => 'Perbarui struktur kategori karyawan yang sudah ada.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('master.kategori-karyawan.update', $kategoriKaryawan) }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Parent Kategori</label>
                <select name="parent_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Tidak Ada / Kategori Utama --</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $kategoriKaryawan->parent_id) == $parent->id)>
                            {{ $parent->nama }} ({{ $parent->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Kategori</label>
                <input type="text" name="nama" value="{{ old('nama', $kategoriKaryawan->nama) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kode</label>
                <input type="text" name="kode" value="{{ old('kode', $kategoriKaryawan->kode) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', $kategoriKaryawan->urutan) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="flex items-center gap-3 pt-8">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $kategoriKaryawan->is_active))
                    class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif</label>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Update
                </button>

                <a href="{{ route('master.kategori-karyawan.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>