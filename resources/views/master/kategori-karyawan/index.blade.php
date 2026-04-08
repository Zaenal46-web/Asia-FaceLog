<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Kategori Karyawan',
        'subtitle' => 'Kelola struktur parent-child kategori karyawan untuk pondasi role scope, shift, dan filter data.',
        'actions' => '<a href="' . route('master.kategori-karyawan.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Kategori</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Kategori</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalKategori) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Kategori Parent</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalParent) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Subkategori</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalChild) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Aktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalActive) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('master.kategori-karyawan.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-3">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari nama / kode kategori</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Contoh: ASIA / OUTSOURCING / IT SUPPORT"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none ring-0 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
            </div>

            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('master.kategori-karyawan.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Parent</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Urutan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Subkategori</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-slate-800">{{ $item->nama }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->kode }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->parent?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->urutan }}</td>
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
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->children->count() }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('master.kategori-karyawan.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('master.kategori-karyawan.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
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
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Belum ada data kategori karyawan.
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