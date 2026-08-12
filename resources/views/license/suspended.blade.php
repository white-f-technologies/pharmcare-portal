<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
        <div class="max-w-lg w-full bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl p-10 text-center">
            <!-- Icon -->
            <div class="mx-auto w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>

            <h1 class="text-2xl font-black text-white tracking-tight">
                License {{ ucfirst(strtolower($statusInfo['status'] ?? 'Suspended')) }}
            </h1>

            <p class="text-sm text-slate-400 mt-4 leading-relaxed">
                Your PharmCare license has been <strong class="text-red-400">{{ strtolower($statusInfo['status'] ?? 'suspended') }}</strong>.
                Normal pharmacy operations are temporarily restricted.
            </p>

            @if(!empty($statusInfo['warning_message']))
                <div class="mt-4 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-xs text-red-300 text-left">
                    {{ $statusInfo['warning_message'] }}
                </div>
            @endif

            <div class="mt-6 space-y-2 text-left bg-white/5 rounded-xl p-4 text-xs text-slate-300">
                <div class="flex justify-between">
                    <span class="text-slate-500">License Key</span>
                    <span class="font-mono">{{ $statusInfo['license_key'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Installation ID</span>
                    <span class="font-mono">{{ $statusInfo['installation_id'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Edition</span>
                    <span>{{ $statusInfo['edition'] ?? 'DEFAULT' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status</span>
                    <span class="font-bold text-red-400 uppercase">{{ $statusInfo['status'] ?? 'SUSPENDED' }}</span>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                <p class="text-xs text-slate-500">
                    Your data is safe. No data has been deleted or modified.
                </p>
                <a href="{{ route('settings.license') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Manage License
                </a>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-600">
            PharmCare v{{ config('license.version', '2.1.0') }} — Contact your vendor for license assistance
        </p>
    </div>
</x-guest-layout>
