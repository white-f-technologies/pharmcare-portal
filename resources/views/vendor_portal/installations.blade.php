<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                    {{ __('Active Client Installations') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Track registered client PCs by Installation ID and last verification heartbeat') }}</p>
            </div>
            <a href="{{ route('vendor.dashboard') }}" class="px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition">
                ← Back to Control Center
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-sm font-bold">Client PCs (Installation Identities)</h3>
                    <span class="text-xs text-slate-400 font-semibold">{{ $installations->total() }} Tracked</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-6">Installation ID</th>
                                <th class="py-3 px-6">Client ID</th>
                                <th class="py-3 px-6">App Version</th>
                                <th class="py-3 px-6">Hostname / OS</th>
                                <th class="py-3 px-6">First Activated</th>
                                <th class="py-3 px-6">Last Heartbeat</th>
                                <th class="py-3 px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($installations as $inst)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">{{ $inst->installation_id }}</td>
                                    <td class="py-3.5 px-6 font-mono text-gray-700">{{ $inst->client_id ?? '—' }}</td>
                                    <td class="py-3.5 px-6 font-bold text-gray-900">v{{ $inst->app_version ?? '2.1.0' }}</td>
                                    <td class="py-3.5 px-6 text-gray-600">{{ $inst->hostname ?? 'PC' }} ({{ $inst->os_info ?? 'Windows' }})</td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $inst->first_activated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="py-3.5 px-6 text-gray-500">{{ $inst->last_verified_at?->diffForHumans() ?? 'Never' }}</td>
                                    <td class="py-3.5 px-6"><span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">{{ $inst->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">No client installations registered yet. Active installations sync automatically when client PCs activate.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $installations->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
