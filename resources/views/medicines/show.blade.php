<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('medicines.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ $medicine->name }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $medicine->generic_name ? $medicine->generic_name . ' • ' : '' }}{{ $medicine->category?->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('medicines.edit', $medicine) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Edit Medicine') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Financial Profit Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Realized Net Profit -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-emerald-100 text-emerald-700 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Realized Sales Profit') }}</span>
                        <span class="text-xl font-extrabold text-emerald-600">{{ setting('currency_symbol', 'UGX') }} {{ format_price($totalRealizedProfit) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Earned from past customer sales') }}</span>
                    </div>
                </div>

                <!-- Potential Inventory Profit -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-sky-100 text-sky-700 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Potential Stock Profit') }}</span>
                        <span class="text-xl font-extrabold text-sky-600">{{ setting('currency_symbol', 'UGX') }} {{ format_price($totalPotentialInventoryProfit) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Expected from unsold stock (' . number_format($totalRemainingStockBaseUnits) . ' ' . ($medicine->base_unit ?? 'units') . ')') }}</span>
                    </div>
                </div>

                <!-- Net Sales Revenue -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-purple-100 text-purple-700 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Net Revenue') }}</span>
                        <span class="text-xl font-extrabold text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ format_price($netSalesRevenue) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Net of customer returns') }}</span>
                    </div>
                </div>

                <!-- Cost of Goods Sold (COGS) -->
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-amber-100 text-amber-700 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Cost of Goods Sold') }}</span>
                        <span class="text-xl font-extrabold text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ format_price($netCogs) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Total purchase expense of sold units') }}</span>
                    </div>
                </div>
            </div>

            <!-- Medicine Details & Specifications Card -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 md:p-8">
                <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-4">{{ __('Medicine Profile') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Medicine Name') }}</span>
                        <span class="font-extrabold text-gray-900">{{ $medicine->name }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Generic Name') }}</span>
                        <span class="font-bold text-gray-800">{{ $medicine->generic_name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Category') }}</span>
                        <span class="font-bold text-emerald-700">{{ $medicine->category?->name ?? 'Uncategorized' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Base Unit') }}</span>
                        <span class="font-bold text-gray-800">{{ $medicine->base_unit ?? 'Tablet' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Reorder Level') }}</span>
                        <span class="font-bold text-gray-800">{{ number_format($medicine->reorder_level) }} {{ $medicine->base_unit ?? 'units' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase block">{{ __('Prescription Required') }}</span>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $medicine->requires_prescription ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $medicine->requires_prescription ? __('Prescription Required') : __('Over The Counter (OTC)') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Packaging Units Profit Breakdown Table -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">{{ __('Packaging Units & Unit Profit Margin Breakdown') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Calculated selling price vs. purchase cost per unit') }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-slate-50 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">{{ __('Packaging Unit') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Conversion Factor') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Unit Purchase Cost') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Unit Selling Price') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Profit / Unit') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Margin %') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @php
                                $latestBatch = $medicine->batches->where('is_active', true)->first() ?? $medicine->batches->first();
                                $basePurchase = $latestBatch ? (float)$latestBatch->purchase_price : 0;
                                $baseSelling = $latestBatch ? (float)$latestBatch->selling_price : 0;
                                $baseProfit = max(0, $baseSelling - $basePurchase);
                                $baseMargin = $baseSelling > 0 ? round(($baseProfit / $baseSelling) * 100, 1) : 0;
                            @endphp
                            <!-- Base Unit Row -->
                            <tr class="bg-emerald-50/40 font-semibold">
                                <td class="px-6 py-4 font-extrabold text-emerald-900 flex items-center gap-2">
                                    <span>{{ $medicine->base_unit ?? 'Tablet' }}</span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] rounded-md font-bold">{{ __('Base Unit') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-600">1 {{ $medicine->base_unit ?? 'Tablet' }}</td>
                                <td class="px-6 py-4 text-right text-gray-700">{{ setting('currency_symbol', 'UGX') }} {{ format_price($basePurchase) }}</td>
                                <td class="px-6 py-4 text-right text-gray-900 font-bold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($baseSelling) }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-emerald-600">+{{ setting('currency_symbol', 'UGX') }} {{ format_price($baseProfit) }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-emerald-700">{{ $baseMargin }}%</td>
                            </tr>
                            <!-- Secondary Packaging Units Rows -->
                            @foreach($medicine->units as $u)
                                @php
                                    $factor = (float)$u->conversion_factor;
                                    $unitCost = $basePurchase * $factor;
                                    $unitSell = $u->selling_price !== null && (float)$u->selling_price > 0 ? (float)$u->selling_price : ($baseSelling * $factor);
                                    $unitProfit = max(0, $unitSell - $unitCost);
                                    $unitMargin = $unitSell > 0 ? round(($unitProfit / $unitSell) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $u->unit_name }}</td>
                                    <td class="px-6 py-4 text-right text-gray-600">{{ number_format($factor) }} {{ $medicine->base_unit ?? 'Tablets' }}</td>
                                    <td class="px-6 py-4 text-right text-gray-700">{{ setting('currency_symbol', 'UGX') }} {{ format_price($unitCost) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-900 font-bold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($unitSell) }}</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-emerald-600">+{{ setting('currency_symbol', 'UGX') }} {{ format_price($unitProfit) }}</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-emerald-700">{{ $unitMargin }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Batches & Inventory Table -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">{{ __('Batches & Inventory Stock Batches') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('Active stock batches, expiration dates, purchase prices, and batch profit metrics') }}</p>
                    </div>
                    <a href="{{ route('medicines.batches.create', $medicine) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add Batch') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-slate-50 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">{{ __('Batch #') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Supplier') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Expiry Date') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Stock Qty') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Purchase Price') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Selling Price') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Profit / Base Unit') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Batch Potential Profit') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($medicine->batches as $batch)
                                @php
                                    $unitProfit = max(0, (float)$batch->selling_price - (float)$batch->purchase_price);
                                    $batchPotentialProfit = $unitProfit * (float)$batch->quantity;
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900 font-mono">{{ $batch->batch_number }}</td>
                                    <td class="px-6 py-4 text-gray-600 font-semibold">{{ $batch->supplier?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 font-bold whitespace-nowrap {{ $batch->expiry_date->isPast() ? 'text-rose-600 font-extrabold' : 'text-gray-700' }}">
                                        {{ $batch->expiry_date->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-gray-900">{{ number_format($batch->quantity) }} {{ $medicine->base_unit ?? 'units' }}</td>
                                    <td class="px-6 py-4 text-right text-gray-700">{{ setting('currency_symbol', 'UGX') }} {{ format_price($batch->purchase_price) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ format_price($batch->selling_price) }}</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-emerald-600">+{{ setting('currency_symbol', 'UGX') }} {{ format_price($unitProfit) }}</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-sky-700">{{ setting('currency_symbol', 'UGX') }} {{ format_price($batchPotentialProfit) }}</td>
                                    <td class="px-6 py-4 text-center space-x-2">
                                        <a href="{{ route('batches.edit', $batch) }}" class="text-emerald-600 hover:text-emerald-900 font-bold">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-400">
                                        {{ __('No batches found for this medicine.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>