<x-guest-layout>
    <div class="min-h-screen bg-[linear-gradient(135deg,#F6F8FF_0%,#EAF1FF_45%,#DCE8FF_100%)]">
        <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full max-w-6xl grid-cols-1 overflow-hidden rounded-[32px] border border-white/60 bg-white/70 shadow-[0_25px_80px_rgba(29,78,216,0.18)] backdrop-blur xl:grid-cols-2">

                {{-- Kiri: Branding --}}
                <div class="relative hidden overflow-hidden bg-[linear-gradient(160deg,#1D4ED8_0%,#2563EB_45%,#60A5FA_100%)] p-10 text-white xl:flex xl:flex-col xl:justify-between">
                    <div class="absolute -left-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -bottom-20 -right-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>

                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-2 ring-1 ring-white/20 backdrop-blur">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-[#1D4ED8] shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m3 6V7m3 10v-4m3 7H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8.172a2 2 0 0 1 1.414.586l3.828 3.828A2 2 0 0 1 20 9.828V18a2 2 0 0 1-2 2Z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100">FaceLog V2</div>
                                <div class="text-sm font-medium text-white/90">Attendance Management System</div>
                            </div>
                        </div>

                        <div class="mt-10 max-w-lg">
                            <h1 class="text-4xl font-bold leading-tight">
                                Buat akun baru
                                <span class="text-blue-100">FaceLog-V2</span>
                            </h1>
                            <p class="mt-4 text-base leading-7 text-blue-50/90">
                                Daftarkan akun untuk mengakses dashboard FaceLog-V2 dan mengelola data absensi,
                                user mesin, raw log, serta proses absensi harian.
                            </p>
                        </div>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                            <div class="text-sm font-semibold">Multi Device</div>
                            <div class="mt-1 text-xs text-blue-100">Integrasi Fingerspot lintas mesin</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                            <div class="text-sm font-semibold">Realtime + Sync</div>
                            <div class="mt-1 text-xs text-blue-100">Webhook, Get Attlog, dan rekonsiliasi log</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                            <div class="text-sm font-semibold">Daily Attendance</div>
                            <div class="mt-1 text-xs text-blue-100">Pemrosesan absensi harian yang fleksibel</div>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Form Register --}}
                <div class="flex items-center justify-center bg-white/85 px-6 py-10 sm:px-10 lg:px-14">
                    <div class="w-full max-w-md">
                        <div class="xl:hidden mb-8 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[20px] bg-[linear-gradient(160deg,#1D4ED8_0%,#2563EB_100%)] text-white shadow-lg shadow-blue-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m3 6V7m3 10v-4m3 7H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8.172a2 2 0 0 1 1.414.586l3.828 3.828A2 2 0 0 1 20 9.828V18a2 2 0 0 1-2 2Z" />
                                </svg>
                            </div>
                            <h1 class="mt-4 text-2xl font-bold text-slate-900">FaceLog-V2</h1>
                            <p class="mt-1 text-sm text-slate-500">Silakan buat akun baru</p>
                        </div>

                        <div class="mb-8 hidden xl:block">
                            <div class="text-sm font-semibold uppercase tracking-[0.25em] text-[#2563EB]">Create Account</div>
                            <h2 class="mt-3 text-3xl font-bold text-slate-900">Daftarkan akun Anda</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Isi data di bawah ini untuk membuat akun baru dan mengakses sistem FaceLog-V2.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('register') }}" class="space-y-5">
                            @csrf

                            <div>
                                <x-input-label for="name" :value="__('Name')" class="mb-2 block text-sm font-semibold text-slate-700" />
                                <x-text-input
                                    id="name"
                                    class="block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-[#2563EB] focus:ring-[#60A5FA]"
                                    type="text"
                                    name="name"
                                    :value="old('name')"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Masukkan nama lengkap"
                                />
                                <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-rose-600" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" class="mb-2 block text-sm font-semibold text-slate-700" />
                                <x-text-input
                                    id="email"
                                    class="block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-[#2563EB] focus:ring-[#60A5FA]"
                                    type="email"
                                    name="email"
                                    :value="old('email')"
                                    required
                                    autocomplete="username"
                                    placeholder="Masukkan email"
                                />
                                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Password')" class="mb-2 block text-sm font-semibold text-slate-700" />
                                <x-text-input
                                    id="password"
                                    class="block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-[#2563EB] focus:ring-[#60A5FA]"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Masukkan password"
                                />
                                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-2 block text-sm font-semibold text-slate-700" />
                                <x-text-input
                                    id="password_confirmation"
                                    class="block w-full rounded-2xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-[#2563EB] focus:ring-[#60A5FA]"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-600" />
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-2">
                                <a
                                    class="text-sm font-medium text-[#2563EB] transition hover:text-[#1D4ED8]"
                                    href="{{ route('login') }}"
                                >
                                    {{ __('Already registered?') }}
                                </a>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[linear-gradient(160deg,#1D4ED8_0%,#2563EB_100%)] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:translate-y-[-1px] hover:shadow-xl"
                                >
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </form>

                        <div class="mt-8 text-center text-xs text-slate-400">
                            © {{ date('Y') }} FaceLog-V2 • Attendance Management System
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>