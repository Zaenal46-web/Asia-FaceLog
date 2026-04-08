@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div>
            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">FaceLog v2</div>
            <div class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ $pageTitle ?? 'Dashboard' }}
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="hidden rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-right shadow-sm sm:block">
                <div class="text-xs font-medium text-slate-500">Hari ini</div>
                <div class="text-sm font-semibold text-slate-800">
                    {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                </div>
            </div>

            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2 shadow-sm transition hover:border-blue-200 hover:shadow">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-sky-400 text-sm font-bold text-white">
                        {{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden text-left sm:block">
                        <div class="text-sm font-semibold text-slate-800">{{ $user?->name }}</div>
                        <div class="text-xs text-slate-500">{{ $user?->role?->name ?? 'No Role' }}</div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25L12 15.75 4.5 8.25" />
                    </svg>
                </summary>

                <div class="absolute right-0 mt-3 w-60 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                    <div class="rounded-xl px-3 py-3">
                        <div class="text-sm font-semibold text-slate-800">{{ $user?->name }}</div>
                        <div class="text-xs text-slate-500">{{ $user?->email }}</div>
                    </div>

                    <div class="my-2 border-t border-slate-100"></div>

                    <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                            Logout
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </div>
</header>