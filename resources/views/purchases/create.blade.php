<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Purchase') }}</h2>
    </x-slot>

    <div class="py-12" x-data="purchaseForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success Alert Banner -->
            <div x-show="successMessage" x-transition class="mb-4 p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between" style="display: none;">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-bold text-sm" x-text="successMessage"></span>
                </div>
            </div>

            <!-- Error Alert Banner -->
            <div x-show="errorMessage" x-transition class="mb-4 p-4 bg-rose-100 border border-rose-400 text-rose-800 rounded-xl shadow-sm flex items-center justify-between" style="display: none;">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="whitespace-pre-line text-sm font-semibold" x-text="errorMessage"></div>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form @submit.prevent="submitPurchase" id="purchase-form">
                        @csrf
                        <input type="hidden" name="subtotal" x-bind:value="subtotal">
                        <input type="hidden" name="total" x-bind:value="grandTotal">

                        <div class="mb-6">
                            <x-input-label for="supplier_id" :value="__('Supplier')" />
                            <select id="supplier_id" name="supplier_id" x-model="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select Supplier') }}</option>
                                @foreach($suppliers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Items') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Medicine') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Receiving Unit') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Batch #') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Expiry Date') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Unit Cost') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sell Price') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <select x-model="item.medicine_id" @change="onMedicineChange(item)" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                        <option value="">{{ __('Select Medicine') }}</option>
                                                        <template x-for="m in allMedicines" :key="m.id">
                                                            <option :value="m.id" x-text="m.name"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <select x-model="item.unit_name" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs font-semibold text-emerald-800 bg-emerald-50">
                                                        <template x-for="u in item.available_units" :key="u.unit_name">
                                                            <option :value="u.unit_name" x-text="u.unit_name + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + ' base)' : '')"></option>
                                                        </template>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input type="text" x-model="item.batch_number" class="block w-full text-sm" placeholder="{{ __('Batch #') }}" required />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input type="date" x-model="item.expiry_date" class="block w-full text-sm" required />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input type="number" x-model.number="item.quantity" class="block w-full text-sm" min="1" required />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input type="number" step="0.01" x-model.number="item.unit_price" class="block w-full text-sm" min="0" required />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input type="number" step="0.01" x-model.number="item.selling_price" class="block w-full text-sm" min="0" required />
                                                </td>
                                                <td class="px-4 py-2 text-sm font-semibold text-gray-800" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + Math.round(item.quantity * item.unit_price).toLocaleString()"></td>
                                                <td class="px-4 py-2">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 text-sm font-semibold">{{ __('Remove') }}</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" @click="addItem()" class="mt-3 inline-flex items-center px-3 py-1.5 bg-emerald-50 border border-emerald-300 rounded-md font-semibold text-xs text-emerald-700 uppercase tracking-widest shadow-sm hover:bg-emerald-100 transition">
                                {{ __('+ Add Item') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <x-input-label for="subtotal_display" :value="__('Subtotal')" />
                                <input id="subtotal_display" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-gray-50 font-semibold" x-bind:value="subtotalDisplay" readonly />
                            </div>
                            <div>
                                <x-input-label for="tax" :value="__('Tax')" />
                                <x-text-input id="tax" name="tax" type="number" step="1" class="mt-1 block w-full" x-model.number="tax" min="0" />
                            </div>
                            <div>
                                <x-input-label for="discount" :value="__('Discount')" />
                                <x-text-input id="discount" name="discount" type="number" step="1" class="mt-1 block w-full" x-model.number="discount" min="0" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="total_display" :value="__('Total')" />
                            <input id="total_display" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-gray-50 text-lg font-bold text-emerald-600" x-bind:value="totalDisplay" readonly />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button x-bind:disabled="isSubmitting || items.length === 0" class="flex items-center gap-2">
                                <span x-show="isSubmitting" class="animate-spin text-white">🌀</span>
                                <span>{{ __('Save Purchase') }}</span>
                            </x-primary-button>
                            <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseForm() {
            return {
                allMedicines: @json($medicines),
                supplier_id: '{{ old('supplier_id', '') }}',
                items: [
                    { medicine_id: '', unit_name: '', available_units: [], batch_number: '', expiry_date: '', quantity: 1, unit_price: 0, selling_price: 0 }
                ],
                tax: 0,
                discount: 0,
                isSubmitting: false,
                successMessage: '',
                errorMessage: '',

                onMedicineChange(item) {
                    const med = this.allMedicines.find(m => String(m.id) === String(item.medicine_id));
                    if (med) {
                        item.available_units = med.units || [{ unit_name: med.base_unit || 'Tablet', conversion_factor: 1.0 }];
                        item.unit_name = item.available_units[0].unit_name;
                    } else {
                        item.available_units = [];
                        item.unit_name = '';
                    }
                },

                addItem() {
                    this.items.push({ medicine_id: '', unit_name: '', available_units: [], batch_number: '', expiry_date: '', quantity: 1, unit_price: 0, selling_price: 0 });
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                get subtotal() {
                    return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
                },

                get grandTotal() {
                    const total = this.subtotal + (parseFloat(this.tax) || 0) - (parseFloat(this.discount) || 0);
                    return total > 0 ? total : 0;
                },

                get subtotalDisplay() {
                    return '{{ setting('currency_symbol', 'UGX') }} ' + Math.round(this.subtotal).toLocaleString();
                },

                get totalDisplay() {
                    return '{{ setting('currency_symbol', 'UGX') }} ' + Math.round(this.grandTotal).toLocaleString();
                },

                async submitPurchase() {
                    if (this.items.length === 0) {
                        this.errorMessage = 'Please add at least one item to the purchase.';
                        return;
                    }

                    if (!this.supplier_id) {
                        this.errorMessage = 'Please select a supplier.';
                        return;
                    }

                    for (let i = 0; i < this.items.length; i++) {
                        const item = this.items[i];
                        if (!item.medicine_id || !item.batch_number || !item.expiry_date || item.quantity < 1) {
                            this.errorMessage = `Item ${i+1}: Please complete Medicine, Batch #, Expiry Date, and Quantity.`;
                            return;
                        }
                        if (parseFloat(item.selling_price || 0) < parseFloat(item.unit_price || 0)) {
                            this.errorMessage = `Item ${i+1}: Selling price cannot be less than buying/purchase price.`;
                            return;
                        }
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    const payload = {
                        supplier_id: this.supplier_id,
                        items: this.items.map(item => ({
                            medicine_id: item.medicine_id,
                            unit_name: item.unit_name,
                            unit_quantity: item.quantity,
                            batch_number: item.batch_number,
                            expiry_date: item.expiry_date,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                            selling_price: item.selling_price
                        })),
                        subtotal: this.subtotal,
                        tax: parseFloat(this.tax || 0),
                        discount: parseFloat(this.discount || 0),
                        total: this.grandTotal
                    };

                    try {
                        const response = await fetch('{{ route("purchases.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const contentType = response.headers.get('content-type');
                        let data = {};
                        if (contentType && contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            throw new Error('Server returned an unexpected response (Status ' + response.status + ').');
                        }

                        if (!response.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Failed to save purchase.'));
                        }

                        this.successMessage = data.message || 'Purchase created successfully!';
                        
                        setTimeout(() => {
                            window.location.href = data.redirect || '{{ route("purchases.index") }}';
                        }, 1000);

                    } catch (e) {
                        this.errorMessage = e.message || 'An error occurred while saving the purchase.';
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>