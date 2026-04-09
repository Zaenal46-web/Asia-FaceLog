<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Kalender Libur',
        'subtitle' => 'Kelola daftar hari libur dan rentang tanggal libur untuk kebutuhan resolver absensi FaceLog v2.',
        'actions' => '<a href="' . route('holiday.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Libur</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Libur</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($total) }}</div>
        </div>

        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-emerald-600">Libur Aktif</div>
            <div class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($totalActive) }}</div>
        </div>

        <div class="rounded-3xl border border-sky-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-sky-600">Tahun Ini</div>
            <div class="mt-3 text-3xl font-bold text-sky-700">{{ number_format($tahunIni) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('holiday.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Tipe</label>
                <select name="tipe" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    <option value="">Semua</option>
                    <option value="nasional" @selected($tipe === 'nasional')>Nasional</option>
                    <option value="internal" @selected($tipe === 'internal')>Internal</option>
                    <option value="khusus" @selected($tipe === 'khusus')>Khusus</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama libur / keterangan"
                       class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
            </div>

            <div class="md:col-span-4 flex items-end gap-3">
                <button type="submit" class="inline-flex rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('holiday.index') }}" class="inline-flex rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Libur</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal Mulai</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal Selesai</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Keterangan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->nama }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ ucfirst($item->tipe) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ optional($item->tanggal_mulai)->format('d-m-Y') }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ optional($item->tanggal_selesai)->format('d-m-Y') }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->keterangan ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    @if($item->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('holiday.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('holiday.destroy', $item) }}" method="POST" onsubmit='return confirm("Yakin ingin menghapus hari libur ini?")'>
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
                                    Belum ada data kalender libur.
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