<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'FaceLog v2') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-[Figtree] antialiased bg-[#F6F8FF] text-slate-900">
        <div class="min-h-screen bg-[#F6F8FF]">
            <div class="flex min-h-screen">
                @include('layouts.partials.sidebar')

                <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
                    @include('layouts.partials.topbar')

                    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                        <div class="mx-auto w-full max-w-7xl">
                            {{ $slot }}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>