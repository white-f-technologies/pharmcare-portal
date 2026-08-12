<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | {{ $systemSettings['system_name'] ?? 'PharmCare' }}</title>
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
            <div class="inline-flex p-4 bg-indigo-50 text-indigo-600 rounded-3xl shadow-sm">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <div>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase tracking-wider">Error 404</span>
                <h1 class="mt-3 text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">Page Not Found</h1>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                    Sorry, the page or prescription record you are looking for doesn't exist, has been moved, or is temporarily unavailable.
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    Return to Dashboard
                </a>
                <button onclick="history.back()" class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold text-sm transition">
                    Go Back
                </button>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $systemSettings['system_name'] ?? 'PharmCare' }}. All rights reserved.
    </footer>

</body>
</html>
