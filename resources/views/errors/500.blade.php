<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | {{ $systemSettings['system_name'] ?? 'PharmCare' }}</title>
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
            <div class="inline-flex p-4 bg-rose-50 text-rose-600 rounded-3xl shadow-sm">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.072 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            
            <div>
                <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold uppercase tracking-wider">Error 500</span>
                <h1 class="mt-3 text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">Internal Server Error</h1>
                <p class="mt-3 text-sm text-gray-500 leading-relaxed">
                    Something went wrong on our end. The server encountered an unexpected error while processing your request.
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    Return to Dashboard
                </a>
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold text-sm transition">
                    Reload Page
                </button>
            </div>
        </div>
    </main>

    <footer class="p-6 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $systemSettings['system_name'] ?? 'PharmCare' }}. All rights reserved.
    </footer>

</body>
</html>
