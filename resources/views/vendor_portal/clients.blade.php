<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                    {{ __('Pharmacy Client Management') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Register and manage pharmacy clients for license issuance') }}</p>
            </div>
            <a href="{{ route('vendor.dashboard') }}" class="px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition">
                ← Back to Control Center
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Add Client Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">+ Register New Pharmacy Client</h3>

                <form action="{{ route('vendor.clients.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pharmacy Name *</label>
                        <input type="text" name="pharmacy_name" required placeholder="e.g. City Care Pharmacy" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Owner / Contact Person *</label>
                        <input type="text" name="owner_name" required placeholder="e.g. Dr. Sarah" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+256 700 000000" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="sarah@citycarepharma.com" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Location / Address</label>
                        <input type="text" name="location" placeholder="Kampala, Uganda" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Save & Create Client ID
                        </button>
                    </div>
                </form>
            </div>

            {{-- Clients Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-sm font-bold">Registered Pharmacy Clients</h3>
                    <span class="text-xs text-slate-400 font-semibold">{{ $clients->total() }} Total</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-6">Client ID</th>
                                <th class="py-3 px-6">Pharmacy Name</th>
                                <th class="py-3 px-6">Owner</th>
                                <th class="py-3 px-6">Phone / Email</th>
                                <th class="py-3 px-6">Location</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($clients as $c)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3.5 px-6 font-mono font-bold text-indigo-600">{{ $c->client_id }}</td>
                                    <td class="py-3.5 px-6 font-bold text-gray-900">{{ $c->pharmacy_name }}</td>
                                    <td class="py-3.5 px-6 text-gray-700">{{ $c->owner_name }}</td>
                                    <td class="py-3.5 px-6 text-gray-600">{{ $c->phone ?? '—' }} <br> {{ $c->email ?? '' }}</td>
                                    <td class="py-3.5 px-6 text-gray-600">{{ $c->location ?? '—' }}</td>
                                    <td class="py-3.5 px-6"><span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">{{ $c->status }}</span></td>
                                    <td class="py-3.5 px-6 text-right">
                                        <a href="{{ route('vendor.license.generator') }}?client={{ $c->client_id }}" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold text-[11px]">
                                            🔑 Issue License
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">No clients registered yet. Use the form above to add your first client.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $clients->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
