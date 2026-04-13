<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Sinkronisasi Log',
        'subtitle' => 'Backup sinkronisasi raw log melalui Get Attlog API dan import manual Excel export mesin.',
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <div class="text-xs font-bold uppercase tracking-[0.25em] text-blue-600">Get Attlog Backup</div>
                <h3 class="mt-2 text-xl font-bold text-slate-900">Sinkronisasi dari API Vendor</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Gunakan fitur ini untuk menarik ulang raw log dari cloud Fingerspot sebagai backup apabila webhook realtime terlambat atau ada data yang miss.
                </p>
            </div>

            <form method="POST" action="" id="get-attlog-form" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Device</label>
                    <select id="get-attlog-device"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        required>
                        <option value="">-- Pilih device --</option>
                        @foreach ($devices as $device)
                            <option value="{{ route('integrasi.sinkronisasi-log.get-attlog', $device) }}">
                                {{ $device->nama }}{{ $device->serial_number ? ' - ' . $device->serial_number : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ now()->timezone('Asia/Jakarta')->toDateString() }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">End Date</label>
                        <input type="date" name="end_date" value="{{ now()->timezone('Asia/Jakarta')->toDateString() }}"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Maksimal range 2 hari per request. Data yang sudah ada akan otomatis dilewati sebagai duplicate.
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        onclick="return confirm('Jalankan Get Attlog untuk device terpilih?')"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        Jalankan Get Attlog
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <div class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-600">Import Excel Mesin</div>
                <h3 class="mt-2 text-xl font-bold text-slate-900">Rekonsiliasi Manual</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Upload file Excel export dari mesin Fingerspot. Sistem hanya akan memproses baris dengan <strong>Status = Berhasil</strong>.
                </p>
            </div>

            <form action="{{ route('integrasi.sinkronisasi-log.import-excel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Device</label>
                    <select name="device_id"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        required>
                        <option value="">-- Pilih device --</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">
                                {{ $device->nama }}{{ $device->serial_number ? ' - ' . $device->serial_number : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('device_id')
                        <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">File Excel</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        required>
                    @error('file')
                        <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-800">
                    Format mengikuti export mesin yang memiliki kolom seperti <strong>No.</strong>, <strong>Status</strong>, dan <strong>Waktu</strong>.
                    Baris dengan status gagal akan otomatis dilewati.
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        onclick="return confirm('Import raw log dari file Excel ini?')"
                        class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        Import Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('get-attlog-device');
            const form = document.getElementById('get-attlog-form');

            select.addEventListener('change', function () {
                form.action = this.value || '';
            });
        });
    </script>
</x-app-layout>