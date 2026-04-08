<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Shift per Kategori',
        'subtitle' => 'Kelola rule shift berdasarkan kategori karyawan sebagai pondasi engine absensi cerdas FaceLog v2.',
        'actions' => '<a href="' . route('master.kategori-shift.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Rule</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Rule</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalRule) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Rule Aktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalActive) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Default Rule</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalDefault) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Istirahat Aktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalIstirahatAktif) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('master.kategori-shift.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari rule</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama rule / kategori / shift"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kategori</label>
                <select
                    name="kategori_karyawan_id"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    @foreach ($kategoriOptions as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $kategoriId === (string) $kategori->id)>
                            {{ $kategori->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Shift</label>
                <select
                    name="shift_master_id"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    @foreach ($shiftOptions as $shift)
                        <option value="{{ $shift->id }}" @selected((string) $shiftId === (string) $shift->id)>
                            {{ $shift->nama }} ({{ $shift->kode }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="md:col-span-6 flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('master.kategori-shift.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Shift</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Rule</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Prioritas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Default</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Window Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Window Pulang</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Istirahat</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-semibold text-slate-800">{{ $item->kategoriKaryawan?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->kategoriKaryawan?->parent?->nama ?? 'Parent: -' }}</div>
                                </td>

                                <td class="px-4 py-4 text-sm text-slate-600">
                                    <div class="font-medium text-slate-800">{{ $item->shiftMaster?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->shiftMaster?->kode ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->nama_rule ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->prioritas }}</td>

                                <td class="px-4 py-4">
                                    @if ($item->is_default)
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                            Default
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            No
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-xs text-slate-600">
                                    {{ $item->window_masuk_mulai_menit }} s/d {{ $item->window_masuk_selesai_menit }} menit
                                </td>

                                <td class="px-4 py-4 text-xs text-slate-600">
                                    {{ $item->window_pulang_mulai_menit }} s/d {{ $item->window_pulang_selesai_menit }} menit
                                </td>

                                <td class="px-4 py-4 text-xs text-slate-600">
                                    @if ($item->istirahat_aktif)
                                        Aktif
                                        @if ($item->istirahat_otomatis_potong)
                                            <br><span class="text-slate-500">Auto potong {{ $item->menit_istirahat_default }} menit</span>
                                        @else
                                            <br><span class="text-slate-500">Manual / tanpa auto potong</span>
                                        @endif
                                    @else
                                        Nonaktif
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    @if ($item->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('master.kategori-shift.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('master.kategori-shift.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus rule ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data shift per kategori.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>