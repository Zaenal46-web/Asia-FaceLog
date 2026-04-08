@php
    $title = $title ?? 'Page Title';
    $subtitle = $subtitle ?? null;
    $actions = $actions ?? null;
@endphp

<div class="mb-6 flex flex-col gap-4 rounded-3xl border border-blue-100 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-500 px-6 py-6 text-white shadow-lg shadow-blue-100 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100/80">FaceLog v2</div>
        <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-2 max-w-2xl text-sm text-blue-50/90">{{ $subtitle }}</p>
        @endif
    </div>

    @if (!empty($actions))
        <div class="shrink-0">
            {!! $actions !!}
        </div>
    @endif
</div>