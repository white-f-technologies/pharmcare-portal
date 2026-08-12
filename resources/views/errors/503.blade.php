<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Under Maintenance | {{ $systemSettings['system_name'] ?? 'PharmCare' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-gray-900 min-h-screen flex flex-col justify-between">

    <header class="p-6">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-application-logo class="h-10 w-auto" />
                <span class="font-bold text-xl text-gray-800 tracking-tight">{{ $systemSettings['system_name'] ?? 'PharmCare' }}</span>
            </a>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center p-6 text-center">
        <div class="max-w-md w-full space-y-6">
            <div class="inline-flex p-4 bg-amber-50 text-amber-600 rounded-3xl shadow-sm">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            
            <div>
                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase tracking-wider">Service Unavailable</span>
                <h1 class="mt-3 text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">System Maintenance</h1>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                    We are currently conducting scheduled system maintenance and database updates. Please check back shortly.
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    Check Status
                </button>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $systemSettings['system_name'] ?? 'PharmCare' }}. All rights reserved.
    </footer>

</body>
</html>
