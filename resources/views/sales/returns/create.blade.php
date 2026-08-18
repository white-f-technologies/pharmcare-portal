<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('sales.show', $sale) }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Process Customer Return') }} — Invoice #{{ $sale->invoice_no }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Verify purchased quantities and restore returned stock to inventory accurately.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="saleReturnForm()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl">
                    <div class="flex items-center gap-2 font-bold text-sm mb-1">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Return Calculation Error') }}</span>
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Sale Summary Header Card -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400 uppercase">{{ __('Customer') }}:</span>
                        <span class="text-sm font-extrabold text-gray-900">{{ $sale->customer?->name ?? __('Walk-in Customer') }}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        <span>{{ __('Sale Date') }}: {{ $sale->created_at->format('Y-m-d H:i') }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ __('Payment') }}: <strong class="text-gray-800 uppercase">{{ $sale->payment_method }}</strong></span>
                    </div>
                </div>

                <div class="text-right bg-slate-50 p-3 rounded-xl border border-slate-200 w-full md:w-auto">
                    <span class="text-xs text-gray-500 font-semibold block">{{ __('Original Total') }}</span>
                    <span class="text-lg font-extrabold text-emerald-600">{{ setting('currency_symbol', 'UGX') }} {{ number_format($sale->total, 2) }}</span>
                </div>
            </div>

            <!-- Return Process Form -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 md:p-8">
                <form action="{{ route('sales.returns.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">

                    <div>
                        <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider mb-4">{{ __('Purchased Items & Return Calculation') }}</h3>
                        
                        <div class="overflow-x-auto border border-gray-100 rounded-xl">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-slate-50 text-gray-500 font-bold uppercase">
                                    <tr>
                                        <th class="px-4 py-3 text-left">{{ __('Medicine') }}</th>
                                        <th class="px-4 py-3 text-left">{{ __('Batch #') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Sold Qty') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Prev Returned') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Max Returnable') }}</th>
                                        <th class="px-4 py-3 text-center" style="width: 250px;">{{ __('Return Qty & Unit') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Refund Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="(item, idx) in returnItems" :key="item.sale_item_id">
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="px-4 py-3 font-bold text-gray-900">
                                                <input type="hidden" :name="'items[' + idx + '][sale_item_id]'" :value="item.sale_item_id">
                                                <span x-text="item.medicine_name"></span>
                                            </td>
                                            <td class="px-4 py-3 font-mono text-gray-600" x-text="item.batch_number || 'N/A'"></td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800" x-text="item.sold_unit_quantity + ' ' + item.sold_unit_name"></td>
                                            <td class="px-4 py-3 text-right text-gray-500" x-text="item.already_returned_base + ' ' + item.base_unit + 's'"></td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-md" x-text="item.max_returnable_base + ' ' + item.base_unit + 's'"></span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <template x-if="item.max_returnable_base > 0">
                                                    <div class="space-y-1">
                                                        <div class="flex items-center gap-1">
                                                            <input type="number" step="0.01" min="0" :max="item.max_returnable_base" :name="'items[' + idx + '][unit_quantity]'" x-model.number="item.return_qty" class="w-20 text-center py-1 px-2 border-gray-300 rounded-lg text-xs font-bold focus:ring-emerald-500 focus:border-emerald-500">
                                                            <select :name="'items[' + idx + '][unit_name]'" x-model="item.selected_unit_name" @change="recalculateItem(item)" class="text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg py-1 px-2">
                                                                <template x-for="u in item.available_units" :key="u.unit_name">
                                                                    <option :value="u.unit_name" x-text="u.unit_name"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                        <template x-if="isExceeded(item)">
                                                            <div class="text-[10px] text-rose-600 font-bold flex items-center justify-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <span>Exceeds max returnable!</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="item.max_returnable_base <= 0">
                                                    <span class="text-[11px] text-gray-400 font-semibold italic">Fully Returned</span>
                                                </template>
                                            </td>
                                            <td class="px-4 py-3 text-right font-extrabold text-gray-900" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + Math.round(calculateLineRefund(item)).toLocaleString()"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Return Reason -->
                    <div>
                        <x-input-label for="reason" :value="__('Reason for Return')" class="font-bold text-sm text-gray-800" />
                        <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm" placeholder="e.g. Wrong medicine prescribed, sealed box returned by customer..." required></textarea>
                    </div>

                    <!-- Refund Summary Box -->
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-emerald-800 font-bold block">{{ __('Total Refund Amount') }}</span>
                            <span class="text-xs text-emerald-600">{{ __('Stock will be automatically restored to active batch inventory.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-extrabold text-emerald-700" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + Math.round(grandRefundTotal).toLocaleString()"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('sales.show', $sale) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" :disabled="hasErrors || grandRefundTotal <= 0" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Confirm Return & Restore Stock') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function saleReturnForm() {
            return {
                rawItems: @json($itemsWithReturnLimits),
                returnItems: [],

                init() {
                    this.returnItems = this.rawItems.map(item => ({
                        ...item,
                        return_qty: 0,
                        selected_unit_name: item.available_units[0] ? item.available_units[0].unit_name : item.base_unit
                    }));
                },

                recalculateItem(item) {
                    // Trigger Alpine evaluation
                },

                getItemConversionFactor(item) {
                    const found = item.available_units.find(u => u.unit_name === item.selected_unit_name);
                    return found ? parseFloat(found.conversion_factor) : 1.0;
                },

                getRequestedBaseQty(item) {
                    return Math.ceil((parseFloat(item.return_qty) || 0) * this.getItemConversionFactor(item));
                },

                isExceeded(item) {
                    return this.getRequestedBaseQty(item) > item.max_returnable_base;
                },

                calculateLineRefund(item) {
                    const qty = parseFloat(item.return_qty) || 0;
                    if (qty <= 0 || this.isExceeded(item)) return 0;
                    const factor = this.getItemConversionFactor(item);
                    const requestedBaseQty = Math.ceil(qty * factor);
                    const baseUnitPrice = item.sold_base_quantity > 0 ? (item.sold_unit_quantity * item.unit_price) / item.sold_base_quantity : item.unit_price;
                    return requestedBaseQty * baseUnitPrice;
                },

                get grandRefundTotal() {
                    return this.returnItems.reduce((sum, item) => sum + this.calculateLineRefund(item), 0);
                },

                get hasErrors() {
                    return this.returnItems.some(item => this.isExceeded(item));
                }
            };
        }
    </script>
</x-app-layout>
