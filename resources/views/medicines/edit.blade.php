<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Medicine') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6">
                    <form action="{{ route('medicines.update', $medicine) }}" method="POST" enctype="multipart/form-data" x-data="{
                        units: {{ json_encode($medicine->units->map(fn($u) => ['unit_name' => $u->unit_name, 'conversion_factor' => (float)$u->conversion_factor, 'selling_price' => $u->selling_price ? (float)$u->selling_price : ''])) }},
                        addUnit() {
                            this.units.push({ unit_name: '', conversion_factor: 10, selling_price: '' });
                        },
                        removeUnit(index) {
                            this.units.splice(index, 1);
                        }
                    }">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $medicine->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="generic_name" :value="__('Generic Name')" />
                                <x-text-input id="generic_name" name="generic_name" type="text" class="mt-1 block w-full" :value="old('generic_name', $medicine->generic_name)" />
                                <x-input-error :messages="$errors->get('generic_name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="category_id" :value="__('Category')" />
                                <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('category_id', $medicine->category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="manufacturer" :value="__('Manufacturer')" />
                                <x-text-input id="manufacturer" name="manufacturer" type="text" class="mt-1 block w-full" :value="old('manufacturer', $medicine->manufacturer)" />
                                <x-input-error :messages="$errors->get('manufacturer')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <x-input-label for="base_unit" :value="__('Base Physical Selling Unit')" />
                                <select id="base_unit" name="base_unit" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-bold text-gray-800">
                                    <option value="Tablet" {{ old('base_unit', $medicine->base_unit ?? 'Tablet') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                    <option value="Capsule" {{ old('base_unit', $medicine->base_unit) == 'Capsule' ? 'selected' : '' }}>Capsule</option>
                                    <option value="Bottle" {{ old('base_unit', $medicine->base_unit) == 'Bottle' ? 'selected' : '' }}>Bottle (Syrup/Liquid)</option>
                                    <option value="Tube" {{ old('base_unit', $medicine->base_unit) == 'Tube' ? 'selected' : '' }}>Tube (Cream/Ointment)</option>
                                    <option value="Vial" {{ old('base_unit', $medicine->base_unit) == 'Vial' ? 'selected' : '' }}>Vial (Injection)</option>
                                    <option value="Sachet" {{ old('base_unit', $medicine->base_unit) == 'Sachet' ? 'selected' : '' }}>Sachet (Powder/ORS)</option>
                                    <option value="Piece" {{ old('base_unit', $medicine->base_unit) == 'Piece' ? 'selected' : '' }}>Piece / Item</option>
                                </select>
                                <p class="text-[11px] text-gray-500 mt-1">This is the smallest countable unit stored in stock.</p>
                            </div>

                            <div>
                                <x-input-label for="purchase_price" :value="__('Purchase Price / Base Cost (') . setting('currency_symbol', 'UGX') . ')'" />
                                <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('purchase_price', $purchasePrice)" oninput="document.getElementById('selling_price').min = this.value" />
                                <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="selling_price" :value="__('Retail Price / Base Unit (') . setting('currency_symbol', 'UGX') . ')'" />
                                <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" :min="old('purchase_price', $purchasePrice)" class="mt-1 block w-full text-emerald-700 font-bold" :value="old('selling_price', $sellingPrice)" required />
                                <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Dynamic Packaging Units Builder Section -->
                        <div class="mb-6 p-4 rounded-xl border border-emerald-100 bg-emerald-50/40">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <h4 class="text-xs font-bold uppercase text-emerald-900 tracking-wider">Packaging Hierarchy Units (Optional)</h4>
                                    <p class="text-[11px] text-emerald-700">Define larger selling units e.g. 1 Strip = 10 Tablets, 1 Box = 100 Tablets.</p>
                                </div>
                                <button type="button" @click="addUnit()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Packaging Unit</span>
                                </button>
                            </div>

                            <template x-for="(unit, index) in units" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end bg-white p-3 rounded-lg border border-emerald-100 mb-2 shadow-xs">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Unit Name</label>
                                        <input type="text" :name="'units[' + index + '][unit_name]'" x-model="unit.unit_name" placeholder="e.g. Strip or Box" class="w-full text-xs border-gray-200 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Base Units Multiplier</label>
                                        <input type="number" step="0.01" :name="'units[' + index + '][conversion_factor]'" x-model="unit.conversion_factor" placeholder="e.g. 10" class="w-full text-xs border-gray-200 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Custom Unit Price (Optional)</label>
                                        <input type="number" step="0.01" :name="'units[' + index + '][selling_price]'" x-model="unit.selling_price" placeholder="Leave empty for auto-calc" class="w-full text-xs border-gray-200 rounded-lg">
                                    </div>
                                    <div>
                                        <button type="button" @click="removeUnit(index)" class="w-full py-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200">
                                            Remove Unit
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="image" :value="__('Medicine Image / Photo')" />
                            @if($medicine->image_url)
                                <div class="mb-2 flex items-center gap-3">
                                    <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="w-16 h-16 object-contain rounded border border-gray-200 bg-gray-50 p-1">
                                    <span class="text-xs text-gray-500">{{ __('Current Image') }}</span>
                                </div>
                            @endif
                            <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $medicine->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="mb-4">
                                <x-input-label for="reorder_level" :value="__('Reorder Level')" />
                                <x-text-input id="reorder_level" name="reorder_level" type="number" class="mt-1 block w-full" :value="old('reorder_level', $medicine->reorder_level)" />
                                <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label :value="__('Requires Prescription')" />
                                <label class="inline-flex items-center mt-2">
                                    <input type="checkbox" name="requires_prescription" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('requires_prescription', $medicine->requires_prescription) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Yes') }}</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <x-input-label :value="__('Active')" />
                                <label class="inline-flex items-center mt-2">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('is_active', $medicine->is_active) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Update Medicine') }}</x-primary-button>
                            <a href="{{ route('medicines.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>