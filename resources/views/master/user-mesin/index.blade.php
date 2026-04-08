<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'User Mesin',
        'subtitle' => 'Kelola data user dari mesin Fingerspot untuk kebutuhan sinkronisasi PIN, template biometrik, dan integrasi device.'
    ])

    <div class="-mt-4 mb-6 flex flex-wrap items-center justify-end gap-2">
        <form action="{{ route('master.user-mesin.get-userinfo-massal') }}" method="POST" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
            @csrf

            <select name="device_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Device</option>
                @foreach ($deviceOptions as $device)
                    <option value="{{ $device->id }}">{{ $device->nama }}</option>
                @endforeach
            </select>

            <input type="number" name="limit" value="10" min="1" max="100" class="w-20 rounded-xl border border-slate-300 px-3 py-2 text-sm">

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="hidden" name="only_empty_name" value="0">
                <input type="checkbox" name="only_empty_name" value="1" checked class="rounded border-slate-300 text-blue-600">
                Nama kosong saja
            </label>

            <button type="submit"
                    onclick='return confirm("Jalankan Get Userinfo Massal sesuai filter ini?")'
                    class="inline-flex items-center rounded-2xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                Get Userinfo Massal
            </button>
        </form>

        <form action="{{ route('master.user-mesin.sync-massal-to-karyawan') }}" method="POST" onsubmit='return confirm("Sync semua user mesin yang belum ada ke master karyawan?")'>
            @csrf
            <button type="submit" class="inline-flex items-center rounded-2xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                Sync Massal ke Karyawan
            </button>
        </form>

        <a href="{{ route('master.user-mesin.create') }}"
           class="inline-flex items-center rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-slate-200 hover:bg-blue-50">
            + Tambah User Mesin
        </a>
    </div>

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Total User Mesin</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalUserMesin) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Pernah Synced</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalSynced) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Punya Face Template</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalWithFace) }}</div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-medium text-slate-500">Punya Finger Template</div>
            <div class="mt-3 text-3xl font-bold text-slate-900">{{ number_format($totalWithFinger) }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('master.user-mesin.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="md:col-span-3">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Cari user mesin</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari PIN / nama / privilege / RFID"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
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
                <label class="mb-2 block text-sm font-semibold text-slate-700">Privilege</label>
                <select
                    name="privilege"
                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Semua</option>
                    <option value="0" @selected($privilege === '0')>User</option>
                    <option value="14" @selected($privilege === '14')>Admin</option>
                </select>
            </div>

            <div class="md:col-span-6 flex items-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Filter
                </button>

                <a href="{{ route('master.user-mesin.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">PIN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Privilege</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">RFID</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Face</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Finger</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Vein</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Synced At</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50 align-top">
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $item->device?->nama ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-800">{{ $item->pin }}</td>
                                <td class="px-4 py-4 text-sm text-slate-700">{{ $item->nama ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    @if ($item->privilege === '14')
                                        Admin
                                    @elseif ($item->privilege === '0')
                                        User
                                    @else
                                        {{ $item->privilege ?: '-' }}
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->rfid ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->face_template_count ?? 0 }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->finger_template_count ?? 0 }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $item->vein_template_count ?? 0 }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ $item->synced_at ? $item->synced_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <form action="{{ route('master.user-mesin.sync-to-karyawan', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-100">
                                                Jadikan Karyawan
                                            </button>
                                        </form>

                                        <a href="{{ route('master.user-mesin.edit', $item) }}"
                                           class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            Edit
                                        </a>

                                        <form action="{{ route('master.user-mesin.request-userinfo', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                                Get Userinfo
                                            </button>
                                        </form>

                                        <form action="{{ route('master.user-mesin.push-set-userinfo', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                Set Userinfo
                                            </button>
                                        </form>

                                        <form action="{{ route('master.user-mesin.delete-from-device', $item) }}" method="POST" onsubmit='return confirm("Yakin hapus user ini dari mesin?")'>
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                Delete Device
                                            </button>
                                        </form>

                                        <form action="{{ route('master.user-mesin.destroy', $item) }}" method="POST" onsubmit='return confirm("Yakin ingin menghapus user mesin ini?")'>
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
                                    Belum ada data user mesin.
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