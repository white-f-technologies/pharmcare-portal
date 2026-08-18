<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Medicine') }}</h2>
    </x-slot>

    <div class="py-12" x-data="medicineCreateForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Fast-Fill Essential Medicine Template Banner -->
            <div class="mb-6 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl shadow-md p-5 text-white">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-white/20 rounded-lg">
                                <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <h3 class="font-bold text-base md:text-lg">Quick-Fill from Common Medicine Preset</h3>
                        </div>
                        <p class="text-xs text-emerald-100 mt-1">
                            Save time by picking from pre-configured essential medicines, or create a completely custom medicine below.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <select x-model="selectedTemplateIndex" @change="applyTemplate()" class="bg-white text-gray-800 text-sm font-semibold rounded-xl border-none shadow-sm px-4 py-2.5 focus:ring-2 focus:ring-emerald-300">
                            <option value="">-- Choose Essential Medicine Preset --</option>
                            <template x-for="(tpl, idx) in templates" :key="idx">
                                <option :value="idx" x-text="tpl.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Template Applied Notice -->
                <div x-show="templateAppliedNotice" x-transition class="mt-3 py-2 px-3 bg-emerald-800/80 rounded-lg text-xs font-semibold text-emerald-100 flex items-center gap-2" style="display: none;">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Template applied successfully! All fields are fully customizable.</span>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6">
                    <form action="{{ route('medicines.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <x-input-label for="name" :value="__('Medicine Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full font-semibold text-gray-800" x-model="name" placeholder="e.g. Paracetamol 500mg Tablets" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="generic_name" :value="__('Generic Name / Active Ingredients')" />
                                <x-text-input id="generic_name" name="generic_name" type="text" class="mt-1 block w-full" x-model="generic_name" placeholder="e.g. Paracetamol" />
                                <x-input-error :messages="$errors->get('generic_name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="category_id" :value="__('Category')" />
                                <select id="category_id" name="category_id" x-model="category_id" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach($categories as $id => $catName)
                                        <option value="{{ $id }}">{{ $catName }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="manufacturer" :value="__('Manufacturer / Brand')" />
                                <input id="manufacturer" name="manufacturer" type="text" list="manufacturers-list" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm" x-model="manufacturer" placeholder="Type or select manufacturer..." />
                                <datalist id="manufacturers-list">
                                    @foreach($commonManufacturers ?? [] as $mfg)
                                        <option value="{{ $mfg }}"></option>
                                    @endforeach
                                </datalist>
                                <x-input-error :messages="$errors->get('manufacturer')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Base Physical Unit & Pricing Details -->
                        <div class="mb-5 bg-slate-50 p-5 rounded-2xl border border-slate-200">
                            <h3 class="text-xs font-bold uppercase text-slate-700 tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Base Physical Selling Unit & Price Configuration</span>
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <x-input-label for="base_unit" :value="__('Base Physical Unit')" />
                                    <select id="base_unit" name="base_unit" x-model="base_unit" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm font-bold text-gray-800">
                                        <option value="Tablet">Tablet</option>
                                        <option value="Capsule">Capsule</option>
                                        <option value="Bottle">Bottle (Syrup / Liquid)</option>
                                        <option value="Tube">Tube (Cream / Ointment)</option>
                                        <option value="Vial">Vial (Injection)</option>
                                        <option value="Ampoule">Ampoule</option>
                                        <option value="Sachet">Sachet (Powder / ORS)</option>
                                        <option value="Piece">Piece / Device</option>
                                        <option value="Box">Box</option>
                                        <option value="Strip">Strip</option>
                                    </select>
                                    <p class="text-[11px] text-gray-500 mt-1">Smallest single unit counted in inventory.</p>

                                    <!-- Quick Unit Preset Pills -->
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <button type="button" @click="setBaseUnit('Tablet')" class="px-2 py-0.5 text-[10px] font-semibold bg-white border border-gray-300 rounded hover:bg-emerald-50 hover:border-emerald-400">Tablet</button>
                                        <button type="button" @click="setBaseUnit('Capsule')" class="px-2 py-0.5 text-[10px] font-semibold bg-white border border-gray-300 rounded hover:bg-emerald-50 hover:border-emerald-400">Capsule</button>
                                        <button type="button" @click="setBaseUnit('Bottle')" class="px-2 py-0.5 text-[10px] font-semibold bg-white border border-gray-300 rounded hover:bg-emerald-50 hover:border-emerald-400">Bottle</button>
                                        <button type="button" @click="setBaseUnit('Tube')" class="px-2 py-0.5 text-[10px] font-semibold bg-white border border-gray-300 rounded hover:bg-emerald-50 hover:border-emerald-400">Tube</button>
                                        <button type="button" @click="setBaseUnit('Sachet')" class="px-2 py-0.5 text-[10px] font-semibold bg-white border border-gray-300 rounded hover:bg-emerald-50 hover:border-emerald-400">Sachet</button>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="purchase_price" value="Purchase Cost / Base Unit ({{ setting('currency_symbol', 'UGX') }})" />
                                    <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model="purchase_price" placeholder="0.00" />
                                    <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                                    <p class="text-[11px] text-gray-500 mt-1">Default cost when ordering or recording stock.</p>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center">
                                        <x-input-label for="selling_price" value="Retail Price / Base Unit ({{ setting('currency_symbol', 'UGX') }})" />
                                        <span class="text-[10px] font-bold text-emerald-700">Quick Markup:</span>
                                    </div>
                                    <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full text-emerald-700 font-bold text-base" x-model="selling_price" placeholder="0.00" required />
                                    <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />

                                    <!-- Quick Markup Calculator Pills -->
                                    <div class="flex items-center gap-1 mt-1.5">
                                        <button type="button" @click="applyMarkup(20)" class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded text-[10px] font-bold">+20%</button>
                                        <button type="button" @click="applyMarkup(30)" class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded text-[10px] font-bold">+30%</button>
                                        <button type="button" @click="applyMarkup(50)" class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded text-[10px] font-bold">+50%</button>
                                        <button type="button" @click="applyMarkup(100)" class="px-2 py-0.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded text-[10px] font-bold">+100%</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Packaging Hierarchy Units Section -->
                        <div class="mb-6 p-5 rounded-2xl border border-emerald-200 bg-emerald-50/50">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
                                <div>
                                    <h4 class="text-xs font-bold uppercase text-emerald-900 tracking-wider">Packaging Hierarchy Units (Multi-Pack Selling)</h4>
                                    <p class="text-[11px] text-emerald-700">Define larger units for faster wholesale/retail dispensing (e.g. 1 Strip = 10 Tablets, 1 Box = 100 Tablets).</p>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase mr-1">Quick Presets:</span>
                                    <button type="button" @click="addPresetUnit('Strip', 10)" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold shadow-xs">+ Strip (10x)</button>
                                    <button type="button" @click="addPresetUnit('Box', 100)" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold shadow-xs">+ Box (100x)</button>
                                    <button type="button" @click="addPresetUnit('Box', 10)" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold shadow-xs">+ Box (10x)</button>
                                    <button type="button" @click="addPresetUnit('Pack', 12)" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold shadow-xs">+ Pack (12x)</button>
                                    <button type="button" @click="addPresetUnit('Carton', 50)" class="px-2 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-xs font-semibold shadow-xs">+ Carton (50x)</button>
                                    <button type="button" @click="addUnit()" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition shadow-xs flex items-center gap-1">
                                        <span>+ Custom Unit</span>
                                    </button>
                                </div>
                            </div>

                            <template x-for="(unit, index) in units" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end bg-white p-3.5 rounded-xl border border-emerald-200 mb-2.5 shadow-xs">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Unit Name</label>
                                        <input type="text" :name="'units[' + index + '][unit_name]'" x-model="unit.unit_name" placeholder="e.g. Strip, Box, Pack" class="w-full text-xs border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Base Units Multiplier</label>
                                        <input type="number" step="0.01" min="0.01" :name="'units[' + index + '][conversion_factor]'" x-model="unit.conversion_factor" placeholder="e.g. 10" class="w-full text-xs border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Custom Selling Price (Optional)</label>
                                        <input type="number" step="0.01" min="0" :name="'units[' + index + '][selling_price]'" x-model="unit.selling_price" placeholder="Auto-calc from base price" class="w-full text-xs border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
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
                            <x-input-label for="image" :value="__('Medicine Image / Product Photo (Optional)')" />
                            <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description / Clinical Notes / Indications')" />
                            <textarea id="description" name="description" rows="3" x-model="description" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm" placeholder="Indications, dosage warnings, or storage instructions..."></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div>
                                <x-input-label for="reorder_level" :value="__('Reorder Stock Alert Level')" />
                                <x-text-input id="reorder_level" name="reorder_level" type="number" min="0" class="mt-1 block w-full" x-model="reorder_level" />
                                <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="requires_prescription" :value="__('Prescription Requirement')" />
                                <label class="inline-flex items-center mt-3 cursor-pointer">
                                    <input type="checkbox" name="requires_prescription" value="1" x-model="requires_prescription" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                    <span class="ms-2 text-sm text-gray-700 font-medium">{{ __('Requires Doctor Prescription') }}</span>
                                </label>
                                <x-input-error :messages="$errors->get('requires_prescription')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="is_active" :value="__('Status')" />
                                <label class="inline-flex items-center mt-3 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500" checked>
                                    <span class="ms-2 text-sm text-gray-700 font-medium">{{ __('Active (Available in Stock & POS)') }}</span>
                                </label>
                                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button class="bg-emerald-600 hover:bg-emerald-700">{{ __('Save Medicine') }}</x-primary-button>
                            <a href="{{ route('medicines.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function medicineCreateForm() {
            return {
                name: @json(old('name', '')),
                generic_name: @json(old('generic_name', '')),
                category_id: @json(old('category_id', '')),
                manufacturer: @json(old('manufacturer', '')),
                base_unit: @json(old('base_unit', 'Tablet')),
                purchase_price: @json(old('purchase_price', '0.00')),
                selling_price: @json(old('selling_price', '0.00')),
                description: @json(old('description', '')),
                reorder_level: {{ old('reorder_level', 10) }},
                requires_prescription: {{ old('requires_prescription') ? 'true' : 'false' }},
                selectedTemplateIndex: '',
                templates: @json($medicineTemplates ?? []),
                categorySlugMap: @json($categoriesSlugMap ?? []),
                units: @json(old('units', [])),
                templateAppliedNotice: false,

                applyTemplate() {
                    if (this.selectedTemplateIndex === '') return;
                    const t = this.templates[this.selectedTemplateIndex];
                    if (!t) return;

                    this.name = t.name || '';
                    this.generic_name = t.generic_name || '';
                    this.manufacturer = t.manufacturer || '';
                    this.base_unit = t.base_unit || 'Tablet';
                    this.description = t.description || '';
                    this.reorder_level = t.reorder_level || 10;
                    this.requires_prescription = !!t.requires_prescription;

                    // Map category by slug if available
                    if (t.category_slug && this.categorySlugMap && this.categorySlugMap[t.category_slug]) {
                        this.category_id = this.categorySlugMap[t.category_slug];
                    }

                    // Apply standard packaging units
                    if (t.units && Array.isArray(t.units)) {
                        this.units = t.units.map(u => ({
                            unit_name: u.unit_name,
                            conversion_factor: u.conversion_factor,
                            selling_price: u.selling_price || ''
                        }));
                    }

                    this.templateAppliedNotice = true;
                    setTimeout(() => { this.templateAppliedNotice = false; }, 4000);
                },

                addUnit() {
                    this.units.push({ unit_name: '', conversion_factor: 10, selling_price: '' });
                },

                addPresetUnit(name, factor) {
                    const exists = this.units.some(u => u.unit_name.toLowerCase() === name.toLowerCase());
                    if (!exists) {
                        this.units.push({ unit_name: name, conversion_factor: factor, selling_price: '' });
                    }
                },

                removeUnit(index) {
                    this.units.splice(index, 1);
                },

                applyMarkup(percentage) {
                    const cost = parseFloat(this.purchase_price) || 0;
                    if (cost > 0) {
                        const markup = cost * (1 + percentage / 100);
                        this.selling_price = Math.round(markup).toFixed(2);
                    }
                },

                setBaseUnit(unit) {
                    this.base_unit = unit;
                }
            };
        }
    </script>
</x-app-layout>