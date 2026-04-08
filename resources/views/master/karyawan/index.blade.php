<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Master Karyawan',
        'subtitle' => 'Kelola data karyawan, PIN Fingerspot, kategori, device kerja, dan status aktif untuk pondasi proses absensi.',
        'actions' => '<a href="' . route('master.karyawan.create') . '" class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-white/70 hover:bg-blue-50">+ Tambah Karyawan</a>'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total Karyawan</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalKaryawan) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Karyawan Aktif</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalActive) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Sudah Punya PIN</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalWithPin) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Belum Punya PIN</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalWithoutPin) }}</div>
        </div>

        <div class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-emerald-600">Data HR Lengkap</div>
            <div class="mt-3 text-3xl font-bold text-emerald-700">{{ number_format($totalLengkap) }}</div>
        </div>

        <div class="rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-amber-600">Belum Lengkap</div>
            <div class="mt-3 text-3xl font-bold text-amber-700">{{ number_format($totalBelumLengkap) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('master.karyawan.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari karyawan</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama / PIN / jabatan / status kerja"
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
                <label class="mb-2 block text-sm font-semibold text-slate-700">Device</label>
                <select
                    name="device_id"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    @foreach ($deviceOptions as $device)
                        <option value="{{ $device->id }}" @selected((string) $deviceId === (string) $device->id)>
                            {{ $device->nama }}
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
                    <option value="with_pin" @selected($status === 'with_pin')>Sudah Punya PIN</option>
                    <option value="without_pin" @selected($status === 'without_pin')>Belum Punya PIN</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Kelengkapan HR</label>
                <select
                    name="kelengkapan"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    <option value="lengkap" @selected($kelengkapan === 'lengkap')>Lengkap</option>
                    <option value="belum_lengkap" @selected($kelengkapan === 'belum_lengkap')>Belum Lengkap</option>
                    <option value="belum_ada_kategori" @selected($kelengkapan === 'belum_ada_kategori')>Belum Ada Kategori</option>
                    <option value="belum_ada_jabatan" @selected($kelengkapan === 'belum_ada_jabatan')>Belum Ada Jabatan</option>
                    <option value="belum_ada_tanggal_masuk" @selected($kelengkapan === 'belum_ada_tanggal_masuk')>Belum Ada Tgl Masuk</option>
                    <option value="belum_ada_status_kerja" @selected($kelengkapan === 'belum_ada_status_kerja')>Belum Ada Status Kerja</option>
                </select>
            </div>

            <div class="md:col-span-6 flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('master.karyawan.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tgl Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status Kerja</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kelengkapan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Aktif</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            @php
                                $missing = [];
                                if (!$item->kategori_karyawan_id) $missing[] = 'Kategori';
                                if (!$item->jabatan) $missing[] = 'Jabatan';
                                if (!$item->tanggal_masuk) $missing[] = 'Tgl Masuk';
                                if (!$item->status_kerja) $missing[] = 'Status Kerja';

                                $isLengkap = count($missing) === 0;
                            @endphp

                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->nama }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->pin_fingerspot ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    <div>{{ $item->kategoriKaryawan?->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->kategoriKaryawan?->parent?->nama ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->device?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->jabatan ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->tanggal_masuk ? $item->tanggal_masuk->format('d-m-Y') : '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->status_kerja ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    @if ($isLengkap)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Lengkap
                                        </span>
                                    @else
                                        <div class="space-y-2">
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Belum Lengkap
                                            </span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($missing as $field)
                                                    <span class="inline-flex rounded-full bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-700 ring-1 ring-rose-200">
                                                        {{ $field }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
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
                                        <a href="{{ route('master.karyawan.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('master.karyawan.destroy', $item) }}" method="POST" onsubmit='return confirm("Yakin ingin menghapus karyawan ini?")'>
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
                                    Belum ada data karyawan.
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