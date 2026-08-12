<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                    {{ __('System Diagnostics') }}
                </h2>
                <p class="text-xs text-gray-500">{{ __('Installation identity, license status, database health, and system information') }}</p>
            </div>
            <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">
                ← Back to Settings
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Status Cards Row --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- App Version --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">App Version</p>
                            <p class="text-lg font-black text-gray-900">{{ $diagnostics['app_version'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Edition --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $diagnostics['edition'] === 'PREMIUM' ? 'bg-amber-100' : 'bg-slate-100' }} flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $diagnostics['edition'] === 'PREMIUM' ? 'text-amber-600' : 'text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">Edition</p>
                            <p class="text-lg font-black {{ $diagnostics['edition'] === 'PREMIUM' ? 'text-amber-600' : 'text-gray-900' }}">
                                {{ $diagnostics['edition'] }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- License Status --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        @php
                            $statusColor = match($diagnostics['license_status']) {
                                'ACTIVE' => 'bg-emerald-100 text-emerald-600',
                                'GRACE' => 'bg-amber-100 text-amber-600',
                                'EXPIRED' => 'bg-red-100 text-red-600',
                                'SUSPENDED', 'REVOKED' => 'bg-red-100 text-red-600',
                                default => 'bg-slate-100 text-slate-600',
                            };
                            $statusTextColor = match($diagnostics['license_status']) {
                                'ACTIVE' => 'text-emerald-600',
                                'GRACE' => 'text-amber-600',
                                'EXPIRED', 'SUSPENDED', 'REVOKED' => 'text-red-600',
                                default => 'text-gray-900',
                            };
                        @endphp
                        <div class="w-10 h-10 rounded-xl {{ $statusColor }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">License Status</p>
                            <p class="text-lg font-black {{ $statusTextColor }}">{{ $diagnostics['license_status'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Database Status --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $diagnostics['db_healthy'] ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $diagnostics['db_healthy'] ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-semibold tracking-wider">Database</p>
                            <p class="text-lg font-black {{ $diagnostics['db_healthy'] ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $diagnostics['db_healthy'] ? 'HEALTHY' : 'ERROR' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detailed Diagnostics Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white">
                    <h3 class="text-base font-bold tracking-tight">System Information</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Non-sensitive diagnostic data for troubleshooting</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @php
                        $rows = [
                            ['PharmCare Version', $diagnostics['app_version'], 'blue'],
                            ['Installation ID', $diagnostics['installation_id'] ?? 'Not generated', 'indigo'],
                            ['License Key', $diagnostics['license_key'] ?? 'UNLICENSED', 'violet'],
                            ['Edition', $diagnostics['edition'], $diagnostics['edition'] === 'PREMIUM' ? 'amber' : 'slate'],
                            ['License Status', $diagnostics['license_status'], match($diagnostics['license_status']) {
                                'ACTIVE' => 'emerald', 'GRACE' => 'amber', default => 'red'
                            }],
                            ['License Type', $diagnostics['license_type'] ?? 'N/A', 'slate'],
                            ['Business Name', $diagnostics['business_name'] ?? 'N/A', 'slate'],
                            ['Expiry Date', $diagnostics['expiry_date'] ?? 'Perpetual (No Expiry)', 'slate'],
                            ['Days Remaining', $diagnostics['days_remaining'] !== null ? $diagnostics['days_remaining'] . ' days' : 'N/A (Perpetual)', 'slate'],
                            ['Grace Period', $diagnostics['grace_days'] . ' days', 'slate'],
                            ['Max Terminals', $diagnostics['max_terminals'] ?? '1', 'slate'],
                            ['Last Verification', $diagnostics['last_verification'] ?? 'Never', 'slate'],
                            ['Verification Stale', $diagnostics['verification_stale'] ? 'Yes — online re-verification recommended' : 'No', $diagnostics['verification_stale'] ? 'amber' : 'emerald'],
                            ['Database Engine', $diagnostics['db_engine'], 'slate'],
                            ['Database Path', $diagnostics['db_path'], 'slate'],
                            ['Database Size', $diagnostics['db_size'], 'slate'],
                            ['Database Integrity', $diagnostics['db_integrity'], $diagnostics['db_healthy'] ? 'emerald' : 'red'],
                            ['Last Backup', $diagnostics['last_backup'] ?? 'Never', 'slate'],
                            ['Operating System', $diagnostics['os'], 'slate'],
                            ['PHP Version', $diagnostics['php_version'], 'slate'],
                            ['Server Time', $diagnostics['server_time'], 'slate'],
                            ['Install Path', $diagnostics['install_path'], 'slate'],
                            ['Data Path', $diagnostics['data_path'], 'slate'],
                        ];
                    @endphp

                    @foreach($rows as [$label, $value, $color])
                        <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50/50 transition">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">{{ $label }}</span>
                            <span class="text-sm font-bold text-{{ $color }}-600 text-right break-all w-2/3">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Support Code Generator --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Support Session Code
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    Generate a temporary support code to share with your vendor for remote assistance.
                    The code contains only your Installation ID, license status, and app version — no pharmacy data.
                </p>

                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4" x-data="{ code: null, copied: false }">
                    <button @click="code = '{{ $diagnostics['support_code'] }}'; $clipboard(code); copied = true; setTimeout(() => copied = false, 2000)"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copied ? 'Copied!' : 'Copy Support Code'"></span>
                    </button>
                    <div x-show="code" class="mt-3">
                        <code class="block text-xs font-mono text-slate-600 bg-white p-3 rounded border border-slate-200 break-all select-all" x-text="code"></code>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
