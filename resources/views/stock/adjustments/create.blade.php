<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('stock.adjustments.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Record Stock Adjustment / Damage') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Update inventory balance for damaged items, customer/supplier returns, or audit counts.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="stockAdjustmentForm()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl">
                    <ul class="list-disc list-inside text-xs font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 md:p-8">
                <form action="{{ route('stock.adjustments.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Movement Type Selector -->
                    <div>
                        <x-input-label :value="__('Movement Type')" class="font-bold text-sm text-gray-800 mb-2" />
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="movement_type" value="damage" x-model="movement_type" class="peer sr-only">
                                <div class="p-4 border rounded-2xl text-center transition peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:text-rose-800 border-gray-200 hover:bg-gray-50">
                                    <div class="text-xl mb-1">🚨</div>
                                    <div class="font-bold text-xs">{{ __('Damaged / Expired') }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ __('Deducts from stock') }}</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="movement_type" value="return" x-model="movement_type" class="peer sr-only">
                                <div class="p-4 border rounded-2xl text-center transition peer-checked:bg-sky-50 peer-checked:border-sky-500 peer-checked:text-sky-800 border-gray-200 hover:bg-gray-50">
                                    <div class="text-xl mb-1">🔄</div>
                                    <div class="font-bold text-xs">{{ __('Stock Return') }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ __('Customer/Supplier') }}</div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="movement_type" value="adjustment" x-model="movement_type" class="peer sr-only">
                                <div class="p-4 border rounded-2xl text-center transition peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:text-amber-800 border-gray-200 hover:bg-gray-50">
                                    <div class="text-xl mb-1">✏️</div>
                                    <div class="font-bold text-xs">{{ __('Inventory Correction') }}</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">{{ __('Manual Audit Count') }}</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Return Type sub-toggle -->
                    <div x-show="movement_type === 'return'" class="bg-sky-50/50 p-4 border border-sky-100 rounded-xl space-y-2">
                        <label class="text-xs font-bold text-sky-900 block">{{ __('Return Direction') }}</label>
                        <div class="flex gap-4 text-xs font-semibold text-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="return_type" value="customer_return" checked class="text-sky-600 focus:ring-sky-500">
                                <span>{{ __('Customer Return (Adds stock back)') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="return_type" value="supplier_return" class="text-sky-600 focus:ring-sky-500">
                                <span>{{ __('Supplier Return (Deducts stock)') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Adjustment Direction sub-toggle -->
                    <div x-show="movement_type === 'adjustment'" class="bg-amber-50/50 p-4 border border-amber-100 rounded-xl space-y-2">
                        <label class="text-xs font-bold text-amber-900 block">{{ __('Correction Direction') }}</label>
                        <div class="flex gap-4 text-xs font-semibold text-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="adjustment_direction" value="add" x-model="adjustment_direction" class="text-amber-600 focus:ring-amber-500">
                                <span>{{ __('Add Stock (+ Count higher)') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="adjustment_direction" value="subtract" x-model="adjustment_direction" class="text-amber-600 focus:ring-amber-500">
                                <span>{{ __('Subtract Stock (- Count lower)') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Medicine Selection -->
                    <div>
                        <x-input-label for="medicine_id" :value="__('Select Medicine')" class="font-bold text-sm" />
                        <select id="medicine_id" name="medicine_id" x-model="medicine_id" @change="onMedicineChange()" class="mt-1 block w-full py-2.5 px-4 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm" required>
                            <option value="">-- {{ __('Select Product') }} --</option>
                            <template x-for="m in medicines" :key="m.id">
                                <option :value="m.id" x-text="m.name + (m.generic_name ? ' (' + m.generic_name + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Batch Selection -->
                    <div x-show="medicine_id">
                        <x-input-label for="batch_id" :value="__('Select Batch')" class="font-bold text-sm" />
                        <select id="batch_id" name="batch_id" x-model="batch_id" @change="onBatchChange()" class="mt-1 block w-full py-2.5 px-4 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm" required>
                            <option value="">-- {{ __('Select Active Batch') }} --</option>
                            <template x-for="b in selectedMedicineBatches" :key="b.id">
                                <option :value="b.id" x-text="'Batch: ' + b.batch_number + ' | Current Stock: ' + b.quantity + ' ' + selectedMedicineBaseUnit + 's' + (b.expiry_date ? ' (Exp: ' + b.expiry_date + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Current Balance Card -->
                    <div x-show="selectedBatch" class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-500 font-semibold block">{{ __('Current Batch Balance') }}</span>
                            <span class="font-extrabold text-xl text-slate-800" x-text="selectedBatch ? selectedBatch.quantity.toLocaleString() + ' ' + selectedMedicineBaseUnit + 's' : ''"></span>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <span block x-text="'Batch #: ' + (selectedBatch ? selectedBatch.batch_number : '')"></span>
                            <span block class="font-semibold text-rose-600" x-text="selectedBatch && selectedBatch.expiry_date ? 'Expiry: ' + selectedBatch.expiry_date : ''"></span>
                        </div>
                    </div>

                    <div x-show="selectedBatch" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Quantity -->
                        <div>
                            <x-input-label for="unit_quantity" :value="__('Transacted Quantity')" class="font-bold text-sm" />
                            <x-text-input id="unit_quantity" name="unit_quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full py-2.5 px-4 rounded-xl text-base" x-model.number="unit_quantity" required />
                        </div>

                        <!-- Unit Dropdown -->
                        <div>
                            <x-input-label for="unit_name" :value="__('Transacted Unit')" class="font-bold text-sm" />
                            <select id="unit_name" name="unit_name" x-model="unit_name" class="mt-1 block w-full py-2.5 px-4 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm font-semibold text-emerald-800 bg-emerald-50" required>
                                <template x-for="u in selectedMedicineUnits" :key="u.unit_name">
                                    <option :value="u.unit_name" x-text="u.unit_name + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + ' ' + selectedMedicineBaseUnit + 's)' : '')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Calculated Impact Summary -->
                    <div x-show="selectedBatch && unit_quantity > 0" class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs space-y-1">
                        <div class="flex justify-between font-bold text-emerald-900">
                            <span>{{ __('Calculated Impact') }}:</span>
                            <span x-text="calculatedImpactText"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('Stock Balance After Transaction') }}:</span>
                            <span class="font-bold text-gray-900" x-text="calculatedStockAfter + ' ' + selectedMedicineBaseUnit + 's'"></span>
                        </div>
                    </div>

                    <!-- Reason / Notes -->
                    <div>
                        <x-input-label for="notes" :value="__('Reason / Audit Notes')" class="font-bold text-sm" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl text-sm" placeholder="e.g. Expired batch discarded, broken bottle during transport, or physical count correction..." required></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <a href="{{ route('stock.adjustments.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Save Stock Adjustment') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function stockAdjustmentForm() {
            return {
                medicines: @json($medicines),
                movement_type: 'damage',
                adjustment_direction: 'add',
                medicine_id: '',
                batch_id: '',
                unit_name: '',
                unit_quantity: 1,
                selectedMedicineBatches: [],
                selectedMedicineUnits: [],
                selectedMedicineBaseUnit: 'Tablet',
                selectedBatch: null,

                onMedicineChange() {
                    const med = this.medicines.find(m => String(m.id) === String(this.medicine_id));
                    if (med) {
                        this.selectedMedicineBatches = med.batches || [];
                        this.selectedMedicineUnits = med.units || [{ unit_name: med.base_unit || 'Tablet', conversion_factor: 1.0 }];
                        this.selectedMedicineBaseUnit = med.base_unit || 'Tablet';
                        this.unit_name = this.selectedMedicineUnits[0].unit_name;
                        this.batch_id = this.selectedMedicineBatches.length ? this.selectedMedicineBatches[0].id : '';
                        this.onBatchChange();
                    } else {
                        this.selectedMedicineBatches = [];
                        this.selectedMedicineUnits = [];
                        this.selectedMedicineBaseUnit = 'Tablet';
                        this.batch_id = '';
                        this.selectedBatch = null;
                    }
                },

                onBatchChange() {
                    this.selectedBatch = this.selectedMedicineBatches.find(b => String(b.id) === String(this.batch_id)) || null;
                },

                get currentConversionFactor() {
                    const found = this.selectedMedicineUnits.find(u => u.unit_name === this.unit_name);
                    return found ? parseFloat(found.conversion_factor) : 1.0;
                },

                get calculatedBaseQtyChange() {
                    return Math.ceil((parseFloat(this.unit_quantity) || 0) * this.currentConversionFactor);
                },

                get calculatedImpactText() {
                    const change = this.calculatedBaseQtyChange;
                    if (this.movement_type === 'damage') {
                        return '-' + change + ' ' + this.selectedMedicineBaseUnit + 's (Deducted)';
                    }
                    if (this.movement_type === 'return') {
                        return '+' + change + ' ' + this.selectedMedicineBaseUnit + 's (Added)';
                    }
                    return (this.adjustment_direction === 'add' ? '+' : '-') + change + ' ' + this.selectedMedicineBaseUnit + 's';
                },

                get calculatedStockAfter() {
                    if (!this.selectedBatch) return 0;
                    const base = parseInt(this.selectedBatch.quantity) || 0;
                    const change = this.calculatedBaseQtyChange;
                    if (this.movement_type === 'damage') return base - change;
                    if (this.movement_type === 'return') return base + change;
                    return this.adjustment_direction === 'add' ? base + change : base - change;
                }
            };
        }
    </script>
</x-app-layout>
