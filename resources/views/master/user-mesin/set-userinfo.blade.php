<x-app-layout>
    @include('layouts.partials.page-header', [
        'title' => 'Set Userinfo ke Mesin',
        'subtitle' => 'Kirim data user dari website ke device mesin Fingerspot dengan mode aman.'
    ])

    @include('layouts.partials.flash-message')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('master.user-mesin.set-userinfo-submit', $userMesin) }}" method="POST" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Device Tujuan</label>
                    <input type="text"
                           value="{{ $userMesin->device?->nama ?? '-' }}"
                           disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">PIN</label>
                    <input type="text"
                           value="{{ $userMesin->pin }}"
                           disabled
                           class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nama</label>
                    <input type="text"
                           name="nama"
                           value="{{ old('nama', $userMesin->api_name ?? $userMesin->nama) }}"
                           class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @error('nama') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Privilege</label>
                    <input type="text"
                           name="privilege"
                           value="{{ old('privilege', $userMesin->api_privilege ?? $userMesin->privilege) }}"
                           class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @error('privilege') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">RFID</label>
                    <input type="text"
                           name="rfid"
                           value="{{ old('rfid', $userMesin->api_rfid ?? $userMesin->rfid) }}"
                           class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @error('rfid') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input type="text"
                           name="password"
                           value="{{ old('password', $userMesin->api_password ?? $userMesin->password) }}"
                           class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm">
                    @error('password') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
                </div>

                <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="kirim_template" value="0">
                        <input type="checkbox"
                               name="kirim_template"
                               value="1"
                               @checked(old('kirim_template', $hasTemplate ? 1 : 0))
                               class="rounded border-slate-300 text-blue-600">
                        Kirim template biometrik jika tersedia
                    </label>

                    <div class="mt-3 text-xs text-slate-600">
                        @if($hasTemplate)
                            Template tersedia. Face: {{ $userMesin->api_face }}, Finger: {{ $userMesin->api_finger }}, Vein: {{ $userMesin->api_vein }}
                        @else
                            Template tidak tersedia. User ini hanya akan dikirim sebagai data dasar tanpa biometrik.
                        @endif
                    </div>
                </div>

                <div class="md:col-span-2 flex items-center gap-3">
                    <button type="submit"
                            onclick='return confirm("Kirim data user ini ke mesin?")'
                            class="inline-flex rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Set Userinfo ke Mesin
                    </button>

                    <a href="{{ route('master.user-mesin.index') }}"
                       class="inline-flex rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Kembali
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-bold uppercase tracking-wide text-slate-500">
                Ringkasan User Mesin
            </div>

            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama</div>
                    <div class="mt-1">{{ $userMesin->nama ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">PIN</div>
                    <div class="mt-1">{{ $userMesin->pin ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Device</div>
                    <div class="mt-1">{{ $userMesin->device?->nama ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Privilege</div>
                    <div class="mt-1">{{ $userMesin->api_privilege ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Template</div>
                    <div class="mt-1">
                        @if($hasTemplate)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                Tersedia
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                Tidak Ada
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Synced At</div>
                    <div class="mt-1">
                        {{ $userMesin->synced_at ? $userMesin->synced_at->format('d-m-Y H:i:s') : '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>