<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock Ledger Audit') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="GET" action="{{ route('reports.ledger') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Search Medicine</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="w-full text-xs border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Filter Medicine</label>
                        <select name="medicine_id" class="w-full text-xs border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">All Medicines</option>
                            @foreach($medicines as $id => $name)
                                <option value="{{ $id }}" {{ request('medicine_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Movement Type</label>
                        <select name="movement_type" class="w-full text-xs border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">All Movement Types</option>
                            <option value="purchase" {{ request('movement_type') == 'purchase' ? 'selected' : '' }}>Purchase (+)</option>
                            <option value="sale" {{ request('movement_type') == 'sale' ? 'selected' : '' }}>Sale (-)</option>
                            <option value="damage" {{ request('movement_type') == 'damage' ? 'selected' : '' }}>Damage (-)</option>
                            <option value="adjustment" {{ request('movement_type') == 'adjustment' ? 'selected' : '' }}>Adjustment (+/-)</option>
                            <option value="return" {{ request('movement_type') == 'return' ? 'selected' : '' }}>Return (+)</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm">
                            Filter Ledger
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ledger Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-base">Transaction Stock Audit Log</h3>
                    <span class="text-xs text-gray-500">Showing {{ $ledgers->total() }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-[11px] font-extrabold uppercase text-gray-500 tracking-wider border-b border-gray-100">
                                <th class="px-6 py-3.5">Date & Time</th>
                                <th class="px-6 py-3.5">Medicine</th>
                                <th class="px-6 py-3.5">Batch</th>
                                <th class="px-6 py-3.5">Type</th>
                                <th class="px-6 py-3.5">Unit Transacted</th>
                                <th class="px-6 py-3.5 text-right">Base Qty Change</th>
                                <th class="px-6 py-3.5 text-right">Before → After</th>
                                <th class="px-6 py-3.5">User</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                            @forelse($ledgers as $ledger)
                                <tr class="hover:bg-emerald-50/30 transition">
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500">
                                        {{ $ledger->created_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        {{ $ledger->medicine->name ?? 'Deleted Medicine' }}
                                        <span class="block text-[10px] text-gray-400 font-normal">{{ $ledger->medicine->generic_name ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[11px]">
                                        {{ $ledger->batch->batch_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $typeClasses = [
                                                'purchase' => 'bg-emerald-100 text-emerald-800',
                                                'sale' => 'bg-blue-100 text-blue-800',
                                                'damage' => 'bg-red-100 text-red-800',
                                                'adjustment' => 'bg-amber-100 text-amber-800',
                                                'return' => 'bg-purple-100 text-purple-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $typeClasses[$ledger->movement_type] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $ledger->movement_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        @if($ledger->unit_quantity && $ledger->unit_name)
                                            {{ number_format($ledger->unit_quantity, 0) }} {{ $ledger->unit_name }}
                                        @else
                                            {{ abs($ledger->quantity_change) }} {{ $ledger->medicine->base_unit ?? 'Tablet' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold font-mono text-sm {{ $ledger->quantity_change > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $ledger->quantity_change > 0 ? '+' : '' }}{{ $ledger->quantity_change }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-gray-600">
                                        {{ $ledger->quantity_before }} → <span class="font-bold text-gray-900">{{ $ledger->quantity_after }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        {{ $ledger->user->name ?? 'System' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        No stock ledger transactions recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ledgers->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $ledgers->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
