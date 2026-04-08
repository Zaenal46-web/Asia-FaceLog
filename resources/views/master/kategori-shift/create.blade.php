<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Tambah Rule Shift per Kategori',
        'subtitle' => 'Buat rule shift berdasarkan kategori karyawan agar engine absensi bisa membaca kondisi scan dengan cerdas.'
    ])

    @include('layouts.partials.flash-message')

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('master.kategori-shift.store') }}" method="POST" class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kategori Karyawan</label>
                <select name="kategori_karyawan_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($kategoriOptions as $kategori)
                        <option value="{{ $kategori->id }}" @selected(old('kategori_karyawan_id') == $kategori->id)>
                            {{ $kategori->nama }} ({{ $kategori->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Shift Master</label>
                <select name="shift_master_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">-- Pilih Shift --</option>
                    @foreach ($shiftOptions as $shift)
                        <option value="{{ $shift->id }}" @selected(old('shift_master_id') == $shift->id)>
                            {{ $shift->nama }} ({{ $shift->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Rule</label>
                <input type="text" name="nama_rule" value="{{ old('nama_rule') }}"
                    placeholder="Contoh: Shift Pagi Normal Asia"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Prioritas</label>
                <input type="number" name="prioritas" value="{{ old('prioritas', 1) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="flex items-center gap-3 pt-8">
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" id="is_default" name="is_default" value="1" @checked(old('is_default'))
                    class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="is_default" class="text-sm font-semibold text-slate-700">Jadikan Default Rule</label>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="lintas_hari" value="0">
                <input type="checkbox" id="lintas_hari" name="lintas_hari" value="1" @checked(old('lintas_hari'))
                    class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="lintas_hari" class="text-sm font-semibold text-slate-700">Lintas Hari</label>
            </div>

            <div></div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Window Masuk Mulai (menit)</label>
                <input type="number" name="window_masuk_mulai_menit" value="{{ old('window_masuk_mulai_menit', -120) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Window Masuk Selesai (menit)</label>
                <input type="number" name="window_masuk_selesai_menit" value="{{ old('window_masuk_selesai_menit', 180) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Window Pulang Mulai (menit)</label>
                <input type="number" name="window_pulang_mulai_menit" value="{{ old('window_pulang_mulai_menit', -180) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Window Pulang Selesai (menit)</label>
                <input type="number" name="window_pulang_selesai_menit" value="{{ old('window_pulang_selesai_menit', 240) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Toleransi Telat (menit)</label>
                <input type="number" name="toleransi_telat_menit" value="{{ old('toleransi_telat_menit', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Toleransi Pulang Cepat (menit)</label>
                <input type="number" name="toleransi_pulang_cepat_menit" value="{{ old('toleransi_pulang_cepat_menit', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Toleransi Lembur (menit)</label>
                <input type="number" name="toleransi_lembur_menit" value="{{ old('toleransi_lembur_menit', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Menit Istirahat Default</label>
                <input type="number" name="menit_istirahat_default" value="{{ old('menit_istirahat_default', 0) }}"
                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>

            <div class="space-y-4 md:col-span-2">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="istirahat_aktif" value="0">
                    <input type="checkbox" id="istirahat_aktif" name="istirahat_aktif" value="1" @checked(old('istirahat_aktif'))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="istirahat_aktif" class="text-sm font-semibold text-slate-700">Istirahat Aktif</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="istirahat_otomatis_potong" value="0">
                    <input type="checkbox" id="istirahat_otomatis_potong" name="istirahat_otomatis_potong" value="1" @checked(old('istirahat_otomatis_potong'))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="istirahat_otomatis_potong" class="text-sm font-semibold text-slate-700">Istirahat Otomatis Potong</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', 1))
                        class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">Aktif</label>
                </div>
            </div>

            <div class="md:col-span-2 flex items-center gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    Simpan
                </button>

                <a href="{{ route('master.kategori-shift.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</x-app-layout>