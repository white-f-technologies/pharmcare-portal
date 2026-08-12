<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Sale') }} - {{ $sale->invoice_no }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('sales.update', $sale) }}" method="POST" x-data="posForm()" @submit="return validateSale()">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex gap-4 mb-4">
                                    <div class="flex-1">
                                        <x-input-label for="barcode" :value="__('Search Medicine')" />
                                        <div class="relative" @click.outside="searchResults = []">
                                            <x-text-input id="barcode" type="text" class="mt-1 block w-full" x-model="searchQuery" @input.debounce.300ms="searchMedicines" placeholder="{{ __('Type medicine name...') }}" />
                                            <div x-show="searchResults.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 max-h-60 overflow-y-auto">
                                                <template x-for="med in searchResults" :key="med.id">
                                                    <button type="button" @click="addToCart(med)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                                        <span class="font-medium" x-text="med.name"></span>
                                                        <span class="text-gray-500 text-xs" x-text="med.generic_name ? '(' + med.generic_name + ')' : ''"></span>
                                                        <span class="float-right text-green-600 font-medium" x-text="'UGX ' + parseFloat(med.selling_price).toFixed(2)"></span>
                                                        <span class="float-right text-xs text-gray-400 mr-2" x-text="'Stock: ' + med.stock_qty"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Medicine') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Batch') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Price') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(item, idx) in cart" :key="idx">
                                                <tr>
                                                    <td class="px-4 py-2 text-sm text-gray-800">
                                                        <span x-text="item.name"></span>
                                                        <input type="hidden" x-bind:name="'items[' + idx + '][medicine_id]'" x-model="item.medicine_id">
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">
                                                        <span x-text="item.batch_number"></span>
                                                        <input type="hidden" x-bind:name="'items[' + idx + '][batch_id]'" x-model="item.batch_id">
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-800">
                                                        <span x-text="'UGX ' + item.unit_price.toFixed(2)"></span>
                                                        <input type="hidden" x-bind:name="'items[' + idx + '][unit_price]'" x-model="item.unit_price">
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" x-bind:name="'items[' + idx + '][quantity]'" class="block w-20 text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model.number="item.quantity" min="1" :max="item.max_qty" @input="calculateCart">
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-gray-800 font-medium" x-text="'UGX ' + (item.quantity * item.unit_price).toFixed(2)"></td>
                                                    <td class="px-4 py-2">
                                                        <button type="button" @click="removeFromCart(idx)" class="text-red-600 hover:text-red-900 text-sm">{{ __('Remove') }}</button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                    <p x-show="cart.length === 0" class="text-sm text-gray-500 text-center py-4">{{ __('No items in cart. Search and add medicines above.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Sale Summary') }}</h3>

                                <div class="mb-4">
                                    <x-input-label for="customer_id" :value="__('Customer')" />
                                    <select id="customer_id" name="customer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">{{ __('Walk-in Customer') }}</option>
                                        @foreach($customers as $id => $name)
                                            <option value="{{ $id }}" {{ $sale->customer_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ __('Subtotal') }}</span>
                                        <span class="font-medium" x-text="'UGX ' + subtotal.toFixed(2)"></span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">{{ __('Discount') }}</span>
                                        <input type="number" step="0.01" name="discount" class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model.number="discount" @input="calculateCart">
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">{{ __('Tax') }}</span>
                                        <input type="number" step="0.01" name="tax" class="w-24 text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model.number="tax" @input="calculateCart">
                                    </div>
                                    <div class="flex justify-between text-lg font-bold text-gray-800 border-t border-gray-200 pt-2">
                                        <span>{{ __('Total') }}</span>
                                        <span x-text="'UGX ' + Math.round(grandTotal).toLocaleString()"></span>
                                    </div>
                                </div>

                                <input type="hidden" name="subtotal" x-bind:value="subtotal">
                                <input type="hidden" name="total" x-bind:value="grandTotal">

                                <div class="mt-4">
                                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                                    <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="cash" {{ $sale->payment_method === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                        <option value="card" {{ $sale->payment_method === 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                                        <option value="transfer" {{ $sale->payment_method === 'transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                                    </select>
                                </div>

                                <div class="mt-6">
                                    <x-primary-button class="w-full justify-center py-3 text-base" :disabled="cart.length === 0">
                                        {{ __('Update Sale') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function posForm() {
            return {
                searchQuery: '',
                searchResults: [],
                cart: @js($sale->items->map(fn($item) => [
                    'medicine_id' => $item->medicine_id,
                    'batch_id' => $item->batch_id,
                    'batch_number' => $item->batch?->batch_number ?? '',
                    'name' => $item->medicine?->name ?? '',
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => (int) $item->quantity,
                    'max_qty' => ($item->batch?->quantity ?? 0) + (int) $item->quantity,
                ])->toArray()),
                discount: {{ $sale->discount ?? 0 }},
                tax: {{ $sale->tax ?? 0 }},

                async searchMedicines() {
                    if (this.searchQuery.length < 1) { this.searchResults = []; return; }
                    try {
                        const response = await fetch('{{ url("medicines/search?q=") }}' + encodeURIComponent(this.searchQuery));
                        const data = await response.json();
                        this.searchResults = data;
                    } catch (e) { this.searchResults = []; }
                },

                addToCart(medicine) {
                    const existing = this.cart.find(item => item.batch_id === medicine.batch_id);
                    if (existing) {
                        if (existing.quantity < medicine.stock_qty) {
                            existing.quantity++;
                        }
                    } else {
                        this.cart.push({
                            medicine_id: medicine.medicine_id,
                            batch_id: medicine.batch_id,
                            batch_number: medicine.batch_number,
                            name: medicine.name,
                            unit_price: parseFloat(medicine.selling_price) || 0,
                            quantity: 1,
                            max_qty: medicine.stock_qty
                        });
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                },

                calculateCart() {},

                validateSale() {
                    if (this.cart.length === 0) {
                        alert('Please add at least one item to the cart.');
                        return false;
                    }
                    return true;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                get grandTotal() {
                    const total = this.subtotal + parseFloat(this.tax || 0) - parseFloat(this.discount || 0);
                    return total > 0 ? total : 0;
                }
            }
        }
    </script>
</x-app-layout>
