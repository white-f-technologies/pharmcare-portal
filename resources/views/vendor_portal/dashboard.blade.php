<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight flex items-center gap-2">
                    <span class="px-2.5 py-1 bg-amber-500 text-white rounded-lg text-xs font-black">VENDOR</span>
                    {{ __('PharmCare Control Center') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Client management, license issuance, active installation tracking & release publishing') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vendor.clients') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                    + Add Client
                </a>
                <a href="{{ route('vendor.releases') }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                    + Publish Release
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total Pharmacy Clients</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalClients }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Active Client PCs</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ $totalInstallations }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Issued Licenses</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ $activeLicenses }} <span class="text-xs text-gray-400 font-normal">/ {{ $totalLicenses }}</span></p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Latest Version</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ $latestRelease?->version ?? config('license.version', '2.1.0') }}</p>
                </div>
            </div>

            {{-- Navigation Pills --}}
            <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                <a href="{{ route('vendor.dashboard') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-extrabold">Dashboard</a>
                <a href="{{ route('vendor.clients') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold transition">Clients</a>
                <a href="{{ route('vendor.installations') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold transition">Installations</a>
                <a href="{{ route('vendor.releases') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold transition">Releases</a>
                <a href="{{ route('settings.license.generator') }}" class="px-4 py-2 bg-white text-amber-700 hover:bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold transition">RSA Key Generator</a>
            </div>

            {{-- Recent Clients & Recent Installations --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Recent Clients --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 bg-slate-900 text-white flex justify-between items-center">
                        <h3 class="text-sm font-bold">Recent Clients</h3>
                        <a href="{{ route('vendor.clients') }}" class="text-xs text-amber-400 font-bold hover:underline">View All →</a>
                    </div>
                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse($recentClients as $client)
                            <div class="p-3.5 flex justify-between items-center hover:bg-gray-50">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $client->pharmacy_name }}</p>
                                    <p class="text-[11px] text-gray-500">ID: <span class="font-mono text-gray-700">{{ $client->client_id }}</span> • {{ $client->owner_name }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $client->status }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-400">No clients registered yet.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Installations --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4 bg-slate-900 text-white flex justify-between items-center">
                        <h3 class="text-sm font-bold">Active Client PCs (Installation IDs)</h3>
                        <a href="{{ route('vendor.installations') }}" class="text-xs text-amber-400 font-bold hover:underline">View All →</a>
                    </div>
                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse($recentInstallations as $inst)
                            <div class="p-3.5 flex justify-between items-center hover:bg-gray-50">
                                <div>
                                    <p class="font-mono font-bold text-indigo-700">{{ $inst->installation_id }}</p>
                                    <p class="text-[11px] text-gray-500">App v{{ $inst->app_version ?? '2.1.0' }} • {{ $inst->hostname ?? 'PC' }}</p>
                                </div>
                                <span class="text-[10px] text-gray-400 font-semibold">{{ $inst->last_verified_at?->diffForHumans() ?? 'Just now' }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-400">No active client PCs registered yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
