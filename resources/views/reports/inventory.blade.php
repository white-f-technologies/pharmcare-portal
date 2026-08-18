<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">{{ __('Dynamic Inventory Report') }}</h2>
                <p class="text-xs text-gray-500 mt-1">{{ __('Real-time stock quantities, valuation, and reorder status') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(feature_enabled('advanced_inventory'))
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="inline-flex items-center px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition shadow-sm gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>{{ __('Export Excel') }}</span>
                    </a>
                @else
                    <a href="{{ route('settings.license') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-100 border border-gray-300 text-gray-400 rounded-xl text-xs font-bold transition shadow-sm gap-2 cursor-pointer hover:bg-amber-50 hover:border-amber-300 hover:text-amber-600 group" title="Premium feature — activate your license to unlock Excel export">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>{{ __('Export Excel') }}</span>
                        <span class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase rounded bg-amber-200 text-amber-800 group-hover:bg-amber-300">PRO</span>
                    </a>
                @endif
                <button onclick="window.print()" class="inline-flex items-center px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>{{ __('Print Report') }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Dynamic Live Stats Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                    <p class="text-xs text-gray-500 font-medium">{{ __('Total Medicines') }}</p>
                    <p class="text-xl font-extrabold text-gray-900 mt-1">{{ number_format($totalMedicines ?? 0) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Active Products') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                    <p class="text-xs text-gray-500 font-medium">{{ __('Total Units in Stock') }}</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalStockQty ?? 0) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Live Quantity') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                    <p class="text-xs text-gray-500 font-medium">{{ __('Cost Valuation') }}</p>
                    <p class="text-base font-extrabold text-gray-900 mt-1 truncate">{{ setting('currency_symbol', 'UGX') }} {{ format_price($totalCostValue ?? 0) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Purchase Cost Total') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                    <p class="text-xs text-gray-500 font-medium">{{ __('Retail Valuation') }}</p>
                    <p class="text-base font-extrabold text-emerald-600 mt-1 truncate">{{ setting('currency_symbol', 'UGX') }} {{ format_price($totalRetailValue ?? 0) }}</p>
                    <p class="text-[10px] text-emerald-600 font-medium mt-0.5">{{ __('Potential Revenue') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                    <p class="text-xs text-gray-500 font-medium">{{ __('Stock Status') }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[11px] font-bold">{{ $lowStockCount ?? 0 }} Low</span>
                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded text-[11px] font-bold">{{ $outOfStockCount ?? 0 }} Out</span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">{{ __('Alert Summaries') }}</p>
                </div>
            </div>

            <!-- Interactive Filter & Search Bar -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80">
                <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    
                    <div class="md:col-span-4 relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search medicine by name or generic...') }}" class="w-full text-xs py-2.5 pl-3 pr-8 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-emerald-500 focus:border-emerald-500" />
                    </div>

                    <div class="md:col-span-3">
                        <select name="category_id" class="w-full text-xs py-2.5 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}" {{ $categoryId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <select name="stock_status" class="w-full text-xs py-2.5 bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">{{ __('All Stock Statuses') }}</option>
                            <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>{{ __('In Stock Only') }}</option>
                            <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>{{ __('Low Stock Only') }}</option>
                            <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>{{ __('Out of Stock Only') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2">
                        <button type="submit" class="w-full py-2.5 px-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                            {{ __('Filter') }}
                        </button>
                        @if($search || $categoryId || $stockStatus)
                            <a href="{{ route('reports.inventory') }}" class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-semibold transition flex items-center justify-center" title="{{ __('Reset Filters') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Inventory Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span>{{ __('Live Inventory Table') }}</span>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">{{ count($medicines) }} {{ __('Items') }}</span>
                    </h3>
                    <span class="text-xs text-gray-400">{{ __('Last updated:') }} {{ date('H:i:s') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-xs">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                <th class="px-4 py-3.5 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Category') }}</th>
                                <th class="px-4 py-3.5 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Stock Qty') }}</th>
                                <th class="px-4 py-3.5 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Reorder Level') }}</th>
                                <th class="px-4 py-3.5 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-4 py-3.5 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Avg Cost') }}</th>
                                <th class="px-4 py-3.5 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Avg Price') }}</th>
                                <th class="px-4 py-3.5 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Cost Value') }}</th>
                                <th class="px-5 py-3.5 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Retail Value') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($medicines as $medicine)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap flex items-center gap-3">
                                        <div class="w-9 h-9 shrink-0 bg-gray-50 border border-gray-200 rounded-lg p-0.5 flex items-center justify-center">
                                            @if($medicine->image_url)
                                                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="max-h-full max-w-full object-contain">
                                            @else
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('medicines.show', $medicine) }}" class="font-bold text-gray-900 hover:text-emerald-600 transition">{{ $medicine->name }}</a>
                                            <div class="text-[10px] text-gray-400">{{ $medicine->generic_name ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-gray-600">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[11px] font-medium">{{ $medicine->category?->name ?? 'Uncategorized' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-center font-extrabold text-gray-900">
                                        {{ number_format($medicine->total_stock) }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-center text-gray-500 font-medium">
                                        {{ $medicine->reorder_level }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full {{ $medicine->status_badge }}">
                                            {{ __($medicine->status_label) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right text-gray-600 font-medium">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($medicine->avg_purchase_price) }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right text-emerald-600 font-bold">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($medicine->avg_selling_price) }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-bold text-gray-800">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($medicine->stock_cost_value) }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right font-extrabold text-emerald-600">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($medicine->stock_retail_value) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="font-medium text-xs">{{ __('No inventory items match your criteria.') }}</p>
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