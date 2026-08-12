<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('app_name', config('app.name', 'PharmCare')) }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <!-- Tailwind CSS & Alpine JS CDN Fallback for Uncompiled Dev -->
            <script src="https://cdn.tailwindcss.com"></script>
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 bg-slate-100 p-4">
            
            <!-- Large Prominent Logo on Login -->
            <div class="flex flex-col items-center mb-4">
                <a href="/" class="flex flex-col items-center">
                    <x-application-logo class="h-28 w-auto max-w-[240px] shadow-sm rounded-2xl p-2 bg-white border border-gray-200" />
                </a>
                <h1 class="mt-3 font-extrabold text-2xl text-gray-800 tracking-tight">{{ setting('app_name', 'PharmCare') }}</h1>
                <p class="text-xs text-gray-500 font-medium">{{ __('Pharmacy Management System') }}</p>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-8 py-6 bg-white shadow-lg border border-gray-200/80 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
