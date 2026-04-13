@php
    $user = auth()->user();
    $roleCode = $user?->role?->code;
    $roleName = $user?->role?->name ?? 'No Role';

    $isSuperadmin = $roleCode === 'superadmin';
    $isHrdAsia = $roleCode === 'hrd_asia';
    $isHrdOutsourcing = $roleCode === 'hrd_outsourcing';

    $navClass = function ($routeNames) {
        $routeNames = (array) $routeNames;

        foreach ($routeNames as $routeName) {
            if (request()->routeIs($routeName)) {
                return 'bg-white/15 text-white shadow-sm ring-1 ring-white/20';
            }
        }

        return 'text-blue-100/85 hover:bg-white/10 hover:text-white';
    };
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-72 overflow-y-auto bg-gradient-to-b from-[#1D4ED8] via-[#1E40AF] to-[#172554] lg:block">
    <div class="flex h-full flex-col px-5 py-6">
        <div class="flex items-center gap-3 border-b border-white/10 pb-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white shadow-lg backdrop-blur">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4m-18 0l9 4m-9-4v10l9 4m0-10l9-4m-9 4v10" />
                </svg>
            </div>

            <div>
                <div class="text-lg font-extrabold tracking-tight text-white">FaceLog v2</div>
                <div class="text-xs text-blue-100/80">Attendance Management System</div>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
            <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-100/70">Login as</div>
            <div class="mt-2 text-sm font-bold text-white">{{ $user?->name }}</div>
            <div class="mt-2 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-blue-50 ring-1 ring-white/15">
                {{ $roleName }}
            </div>
        </div>

        <nav class="mt-6 flex-1 space-y-7">
            <div>
                <div class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-100/60">Main Menu</div>
                <div class="space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('dashboard') }}">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l9-9 9 9M4.5 12v7.5A1.5 1.5 0 006 21h3.75v-6h4.5v6H18a1.5 1.5 0 001.5-1.5V12" />
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('absensi.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('absensi.index') }}">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75V6m7.5-2.25V6M3.75 9h16.5M5.25 6.75h13.5A1.5 1.5 0 0120.25 8.25v10.5a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V8.25a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                        </span>
                        <span>Absensi</span>
                    </a>

                    <a href="{{ route('export.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('export.index') }}">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75v10.5m0 0l4.5-4.5m-4.5 4.5l-4.5-4.5M4.5 15.75v2.25A2.25 2.25 0 006.75 20.25h10.5A2.25 2.25 0 0019.5 18v-2.25" />
                            </svg>
                        </span>
                        <span>Export</span>
                    </a>

                    <a href="{{ route('holiday.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('holiday.index') }}">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9.75h16.5M5.25 5.25h13.5A1.5 1.5 0 0120.25 6.75v11.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                        </span>
                        <span>Kalender Libur</span>
                    </a>
                </div>
            </div>

            @if ($isSuperadmin)
                <div>
                    <div class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-100/60">Master Data</div>
                    <div class="space-y-2">
                        <a href="{{ route('master.karyawan.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.karyawan.index') }}">
                            <span>Karyawan</span>
                        </a>

                        <a href="{{ route('master.kategori-karyawan.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.kategori-karyawan.index') }}">
                            <span>Kategori Karyawan</span>
                        </a>

                        <a href="{{ route('master.shift-master.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.shift-master.index') }}">
                            <span>Shift Master</span>
                        </a>

                        <a href="{{ route('master.kategori-shift.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.kategori-shift.index') }}">
                            <span>Shift per Kategori</span>
                        </a>

                        <a href="{{ route('master.device.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.device.index') }}">
                            <span>Device Mesin</span>
                        </a>

                        <a href="{{ route('master.user-mesin.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('master.user-mesin.index') }}">
                            <span>User Mesin</span>
                        </a>
                    </div>
                </div>

                <div>
                    <div class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-100/60">Integrasi</div>
                    <div class="space-y-2">
                        <a href="{{ route('integrasi.raw-log.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('integrasi.raw-log.index') }}">
                            <span>Raw Log</span>
                        </a>

                        <a href="{{ route('integrasi.sinkronisasi-log.create') }}"
                        class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition
                        {{ request()->routeIs('integrasi.sinkronisasi-log.*') ? 'bg-white/15 text-white shadow-sm' : 'text-blue-100 hover:bg-white/10 hover:text-white' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0 1 12.8-5.3M19.5 12a7.5 7.5 0 0 1-12.8 5.3M16.5 4.5v3h-3M7.5 19.5v-3h3" />
                            </svg>
                            <span>Sinkronisasi Log</span>
                        </a>

                        <a href="{{ route('integrasi.webhook-log.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('integrasi.webhook-log.index') }}">
                            <span>Webhook Log</span>
                        </a>

                        <a href="{{ route('integrasi.push-log.index') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition {{ $navClass('integrasi.push-log.index') }}">
                            <span>Push Log</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <div class="mt-6 rounded-2xl border border-white/10 bg-white/10 p-4 text-xs text-blue-100/80 backdrop-blur">
            <div class="font-semibold text-white">FaceLog v2</div>
            <div class="mt-1">Premium Attendance Dashboard</div>
        </div>
    </div>
</aside>