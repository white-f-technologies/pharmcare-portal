<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Purchase / Stock In') }}</h2>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6">
                    <form @submit.prevent="submitPurchase" id="purchase-form">
                        @csrf
                        <input type="hidden" name="subtotal" x-bind:value="subtotal">
                        <input type="hidden" name="total" x-bind:value="grandTotal">

                        <!-- Supplier Selection & Quick Add Section -->
                        <div class="mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <x-input-label for="supplier_id" :value="__('Select Wholesale Supplier / Distributor')" class="font-bold text-gray-800" />
                                <button type="button" @click="showSupplierModal = true" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-100 hover:bg-emerald-200 px-3 py-1 rounded-lg transition flex items-center gap-1 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Quick Add Supplier</span>
                                </button>
                            </div>
                            
                            <select id="supplier_id" name="supplier_id" x-model="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm text-sm font-medium" required>
                                <option value="">{{ __('-- Select Supplier / Distributor --') }}</option>
                                <template x-for="sup in suppliersList" :key="sup.id">
                                    <option :value="sup.id" x-text="sup.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Purchase Order Table Section -->
                        <div class="mb-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>{{ __('Received Stock Items & Invoicing') }}</span>
                                    </h3>
                                    <p class="text-xs text-gray-500">Prices and units auto-populate from catalogue. You can customize any price, batch, or quantity.</p>
                                </div>

                                <!-- Bulk Action Helpers -->
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" @click="autoFillAllBatches()" class="px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition border border-gray-300 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Auto-Fill Batches</span>
                                    </button>
                                    <button type="button" @click="copyFirstRowBatchExpiryToAll()" x-show="items.length > 1" class="px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition border border-gray-300 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                        <span>Copy 1st Batch & Expiry to All</span>
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-xs">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 24%;">{{ __('Medicine') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 14%;">{{ __('Receiving Unit') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 15%;">{{ __('Batch #') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 17%;">{{ __('Expiry Date') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 8%;">{{ __('Qty') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 10%;">{{ __('Cost / Unit') }}</th>
                                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase" style="width: 10%;">{{ __('Sell Price') }}</th>
                                            <th class="px-3 py-3 text-right text-xs font-bold text-gray-700 uppercase" style="width: 10%;">{{ __('Total') }}</th>
                                            <th class="px-2 py-3 text-center text-xs font-bold text-gray-700 uppercase" style="width: 4%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="hover:bg-slate-50/80 transition">
                                                <!-- Medicine Selection -->
                                                <td class="px-3 py-2.5">
                                                    <select x-model="item.medicine_id" @change="onMedicineChange(item)" class="block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-xs text-xs font-semibold text-gray-800" required>
                                                        <option value="">{{ __('-- Select Medicine --') }}</option>
                                                        <template x-for="m in allMedicines" :key="m.id">
                                                            <option :value="m.id" x-text="m.name"></option>
                                                        </template>
                                                    </select>
                                                </td>

                                                <!-- Receiving Unit Selection -->
                                                <td class="px-3 py-2.5">
                                                    <select x-model="item.unit_name" @change="onUnitChange(item)" class="block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-xs text-xs font-semibold text-emerald-800 bg-emerald-50/60">
                                                        <template x-for="u in item.available_units" :key="u.unit_name">
                                                            <option :value="u.unit_name" x-text="u.unit_name + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + 'x)' : '')"></option>
                                                        </template>
                                                    </select>
                                                </td>

                                                <!-- Batch Number with Quick-Gen Button -->
                                                <td class="px-3 py-2.5">
                                                    <div class="relative">
                                                        <x-text-input type="text" x-model="item.batch_number" class="block w-full text-xs pr-7 font-mono uppercase" placeholder="e.g. BN-2026-01" required />
                                                        <button type="button" @click="item.batch_number = generateBatchNumber()" title="Auto-Generate Batch #" class="absolute right-1.5 top-1.5 text-gray-400 hover:text-emerald-600 text-xs">
                                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                        </button>
                                                    </div>
                                                </td>

                                                <!-- Expiry Date with 1-Click Shortcuts -->
                                                <td class="px-3 py-2.5">
                                                    <div>
                                                        <x-text-input type="date" x-model="item.expiry_date" class="block w-full text-xs" required />
                                                        <div class="flex gap-1 mt-1 text-[9px] font-semibold text-gray-500">
                                                            <button type="button" @click="setExpiryShortcut(item, 24)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 rounded border border-gray-200">+2 Yrs</button>
                                                            <button type="button" @click="setExpiryShortcut(item, 12)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 rounded border border-gray-200">+1 Yr</button>
                                                            <button type="button" @click="setExpiryShortcut(item, 36)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 rounded border border-gray-200">+3 Yrs</button>
                                                            <button type="button" @click="setExpiryShortcut(item, 6)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 rounded border border-gray-200">+6 Mo</button>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Quantity -->
                                                <td class="px-3 py-2.5">
                                                    <x-text-input type="number" x-model.number="item.quantity" class="block w-full text-xs font-bold" min="1" required />
                                                </td>

                                                <!-- Unit Cost Price -->
                                                <td class="px-3 py-2.5">
                                                    <x-text-input type="number" step="0.01" x-model.number="item.unit_price" class="block w-full text-xs" min="0" required />
                                                </td>

                                                <!-- Unit Selling Price -->
                                                <td class="px-3 py-2.5">
                                                    <x-text-input type="number" step="0.01" x-model.number="item.selling_price" class="block w-full text-xs text-emerald-700 font-bold" min="0" required />
                                                </td>

                                                <!-- Line Item Total -->
                                                <td class="px-3 py-2.5 text-right text-xs font-bold text-gray-800 whitespace-nowrap" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + Math.round(item.quantity * item.unit_price).toLocaleString()"></td>

                                                <!-- Remove Action -->
                                                <td class="px-2 py-2.5 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold text-base p-1" title="Remove line item">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" @click="addItem()" class="mt-3 inline-flex items-center px-3.5 py-2 bg-emerald-50 border border-emerald-300 rounded-xl font-bold text-xs text-emerald-700 uppercase tracking-wider shadow-xs hover:bg-emerald-100 transition gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>{{ __('+ Add Another Medicine') }}</span>
                            </button>
                        </div>

                        <!-- Summary & Totals Calculation Box -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                            <div>
                                <x-input-label for="subtotal_display" :value="__('Subtotal Amount')" />
                                <input id="subtotal_display" type="text" class="mt-1 block w-full border-gray-300 rounded-xl shadow-sm bg-white font-bold text-gray-800" x-bind:value="subtotalDisplay" readonly />
                            </div>
                            <div>
                                <x-input-label for="tax" :value="__('Tax / VAT Added (' . setting('currency_symbol', 'UGX') . ')')" />
                                <x-text-input id="tax" name="tax" type="number" step="1" class="mt-1 block w-full" x-model.number="tax" min="0" />
                            </div>
                            <div>
                                <x-input-label for="discount" :value="__('Supplier Discount (' . setting('currency_symbol', 'UGX') . ')')" />
                                <x-text-input id="discount" name="discount" type="number" step="1" class="mt-1 block w-full" x-model.number="discount" min="0" />
                            </div>
                        </div>

                        <div class="mb-6 flex justify-between items-center bg-emerald-50 p-5 rounded-2xl border border-emerald-200">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">{{ __('Net Total Purchase Payable') }}</div>
                                <div class="text-xs text-emerald-600 mt-0.5">{{ __('All items, taxes, and discounts calculated') }}</div>
                            </div>
                            <div class="text-2xl md:text-3xl font-extrabold text-emerald-700 font-mono" x-text="totalDisplay"></div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button x-bind:disabled="isSubmitting || items.length === 0" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 text-sm font-bold flex items-center gap-2 rounded-xl">
                                <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>{{ __('Confirm & Receive Stock') }}</span>
                            </x-primary-button>
                            <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Add Supplier Modal -->
        <div x-show="showSupplierModal" x-transition class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-100" @click.away="showSupplierModal = false">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Quick Add Wholesale Supplier</span>
                    </h3>
                    <button type="button" @click="showSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                </div>

                <div x-show="supplierError" class="mb-3 p-2.5 bg-rose-100 border border-rose-300 text-rose-800 rounded-lg text-xs font-semibold" x-text="supplierError"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Supplier / Distributor Name *</label>
                        <input type="text" x-model="newSupplier.name" placeholder="e.g. Abacus Pharma, Joint Medical Store" class="w-full text-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Phone Number</label>
                        <input type="text" x-model="newSupplier.phone" placeholder="e.g. +256 700 000 000" class="w-full text-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Company / Branch</label>
                        <input type="text" x-model="newSupplier.company" placeholder="e.g. Kampala Central Wholesale" class="w-full text-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                    </div>
                </div>

                <div class="flex justify-end items-center gap-2 mt-5">
                    <button type="button" @click="showSupplierModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold">
                        Cancel
                    </button>
                    <button type="button" @click="saveQuickSupplier()" :disabled="isSavingSupplier" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5">
                        <svg x-show="isSavingSupplier" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Save Supplier</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseForm() {
            return {
                allMedicines: @json($medicines),
                suppliersList: @json(collect($suppliers)->map(fn($name, $id) => ['id' => $id, 'name' => $name])->values()),
                supplier_id: '{{ old('supplier_id', '') }}',
                items: [
                    { medicine_id: '', unit_name: '', available_units: [], batch_number: '', expiry_date: '', quantity: 1, unit_price: 0, selling_price: 0 }
                ],
                tax: 0,
                discount: 0,
                isSubmitting: false,
                successMessage: '',
                errorMessage: '',

                // Quick Supplier Modal State
                showSupplierModal: false,
                newSupplier: { name: '', phone: '', company: '' },
                isSavingSupplier: false,
                supplierError: '',

                generateBatchNumber() {
                    const now = new Date();
                    const ymd = now.toISOString().slice(2,10).replace(/-/g, '');
                    const rand = Math.floor(100 + Math.random() * 900);
                    return `BN-${ymd}-${rand}`;
                },

                getFutureDate(months) {
                    const d = new Date();
                    d.setMonth(d.getMonth() + months);
                    return d.toISOString().split('T')[0];
                },

                setExpiryShortcut(item, months) {
                    item.expiry_date = this.getFutureDate(months);
                },

                onMedicineChange(item) {
                    const med = this.allMedicines.find(m => String(m.id) === String(item.medicine_id));
                    if (med) {
                        item.available_units = med.units || [{ unit_name: med.base_unit || 'Tablet', conversion_factor: 1.0, purchase_price: med.purchase_price || 0, selling_price: med.selling_price || 0 }];
                        const defaultUnit = item.available_units[0];
                        item.unit_name = defaultUnit.unit_name;
                        item.unit_price = (defaultUnit.purchase_price !== undefined && defaultUnit.purchase_price > 0) ? defaultUnit.purchase_price : (med.purchase_price || 0);
                        item.selling_price = (defaultUnit.selling_price !== undefined && defaultUnit.selling_price > 0) ? defaultUnit.selling_price : (med.selling_price || 0);
                        
                        if (!item.batch_number) {
                            item.batch_number = this.generateBatchNumber();
                        }
                        if (!item.expiry_date) {
                            item.expiry_date = this.getFutureDate(24);
                        }
                    } else {
                        item.available_units = [];
                        item.unit_name = '';
                        item.unit_price = 0;
                        item.selling_price = 0;
                    }
                },

                onUnitChange(item) {
                    const selectedUnit = item.available_units.find(u => u.unit_name === item.unit_name);
                    if (selectedUnit) {
                        if (selectedUnit.purchase_price !== undefined && selectedUnit.purchase_price > 0) {
                            item.unit_price = selectedUnit.purchase_price;
                        }
                        if (selectedUnit.selling_price !== undefined && selectedUnit.selling_price > 0) {
                            item.selling_price = selectedUnit.selling_price;
                        }
                    }
                },

                autoFillAllBatches() {
                    this.items.forEach(item => {
                        if (!item.batch_number) {
                            item.batch_number = this.generateBatchNumber();
                        }
                        if (!item.expiry_date) {
                            item.expiry_date = this.getFutureDate(24);
                        }
                    });
                },

                copyFirstRowBatchExpiryToAll() {
                    if (this.items.length < 2) return;
                    const first = this.items[0];
                    for (let i = 1; i < this.items.length; i++) {
                        if (first.batch_number) this.items[i].batch_number = first.batch_number;
                        if (first.expiry_date) this.items[i].expiry_date = first.expiry_date;
                    }
                },

                addItem() {
                    this.items.push({
                        medicine_id: '',
                        unit_name: '',
                        available_units: [],
                        batch_number: this.generateBatchNumber(),
                        expiry_date: this.getFutureDate(24),
                        quantity: 1,
                        unit_price: 0,
                        selling_price: 0
                    });
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

                async saveQuickSupplier() {
                    if (!this.newSupplier.name) {
                        this.supplierError = 'Supplier name is required.';
                        return;
                    }
                    this.isSavingSupplier = true;
                    this.supplierError = '';
                    try {
                        const res = await fetch('{{ route("suppliers.quick-store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.newSupplier)
                        });
                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message || 'Failed to save supplier.');

                        this.suppliersList.push({ id: data.supplier.id, name: data.supplier.name });
                        this.supplier_id = data.supplier.id;
                        this.showSupplierModal = false;
                        this.newSupplier = { name: '', phone: '', company: '' };
                    } catch(err) {
                        this.supplierError = err.message || 'Error creating supplier.';
                    } finally {
                        this.isSavingSupplier = false;
                    }
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