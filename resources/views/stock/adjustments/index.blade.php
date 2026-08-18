<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 text-amber-800 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('Stock Adjustments & Damage') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Log damaged stock, customer/supplier returns, and inventory count corrections.') }}</p>
                </div>
            </div>
            <a href="{{ route('stock.adjustments.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('+ Record Adjustment / Damage') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-base">{{ __('Adjustment Audit Trail') }}</h3>
                    <a href="{{ route('reports.ledger') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                        {{ __('View Full Audit Ledger') }} &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-slate-50 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">{{ __('Date & Time') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Medicine') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Batch #') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Type') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Transacted Unit') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Base Qty Change') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Balance After') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Recorded By') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Reason / Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($adjustments as $adj)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-600 whitespace-nowrap">
                                        {{ $adj->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $adj->medicine?->name }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-mono whitespace-nowrap">
                                        {{ $adj->batch?->batch_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($adj->movement_type === 'damage')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-100 text-rose-800 rounded-full font-extrabold text-[10px] uppercase">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                <span>{{ __('Damage') }}</span>
                                            </span>
                                        @elseif($adj->movement_type === 'return')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-sky-100 text-sky-800 rounded-full font-extrabold text-[10px] uppercase">
                                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>{{ __('Return') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full font-extrabold text-[10px] uppercase">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span>{{ __('Adjustment') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">
                                        {{ number_format($adj->unit_quantity) }} {{ $adj->unit_name }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold whitespace-nowrap {{ $adj->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $adj->quantity_change > 0 ? '+' : '' }}{{ number_format($adj->quantity_change) }} {{ $adj->medicine?->base_unit ?? 'base' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 whitespace-nowrap">
                                        {{ number_format($adj->quantity_after) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                        {{ $adj->user?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">
                                        {{ $adj->notes }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="font-bold text-gray-600">{{ __('No stock adjustments recorded yet.') }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ __('Click "+ Record Adjustment / Damage" above to log damages or returns.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $adjustments->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
