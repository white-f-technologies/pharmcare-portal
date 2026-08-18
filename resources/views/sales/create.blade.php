<x-app-layout>
    {{-- No x-slot header to maximize vertical space for POS terminal --}}

    <!-- POS Main Container (Light Blue Palette, Natural Page Scrolling, Spacious Well-Structured Layout) -->
    <div class="py-4 px-3 sm:px-5 bg-slate-100/90 min-h-screen" x-data="posForm()">
        <div class="max-w-[1650px] mx-auto space-y-4">
        
            <!-- Error Alert Toast -->
            <div x-show="errorMessage" x-transition class="p-3.5 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl shadow-sm flex items-center justify-between" style="display: none;">
                <div class="flex items-center gap-2.5 text-xs font-bold">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-rose-600 hover:text-rose-800 font-bold text-base leading-none p-1">&times;</button>
            </div>

            <form @submit.prevent="submitSale" id="sale-form">
                @csrf
                <input type="hidden" name="customer_id" x-model="customer_id">
                <input type="hidden" name="payment_method" x-model="payment_method">
                <input type="hidden" name="subtotal" x-model="subtotal">
                <input type="hidden" name="discount" x-model="discount">
                <input type="hidden" name="tax" x-model="tax">
                <input type="hidden" name="total" x-model="grandTotal">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                    <!-- LEFT PANE: Search, Category Filters & Independently Scrollable Medicine Catalog (7 or 8 cols) -->
                    <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden flex flex-col">
                        
                        <!-- Top Toolbar: Search, Barcode Scanner & View Mode Toggle -->
                        <div class="p-4 border-b border-gray-100 bg-white shrink-0 space-y-3">
                            
                            <div class="flex items-center gap-3">
                                <!-- Styled Search Bar -->
                                <div class="relative flex-1" @click.outside="searchResults = []">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input type="text" x-model="searchQuery" @input="searchMedicines" placeholder="{{ __('Search medicine by name, generic, or scan barcode...') }}" class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white transition shadow-2xs" autofocus />
                                    <button type="button" x-show="searchQuery" @click="searchQuery = ''; searchResults = []" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>

                                    <!-- Live Autocomplete Overlay -->
                                    <div x-show="searchResults.length > 0" class="absolute z-50 mt-1.5 w-full bg-white shadow-2xl rounded-2xl border border-gray-200 max-h-72 overflow-y-auto divide-y divide-gray-100 custom-scrollbar" style="display: none;">
                                        <template x-for="med in searchResults" :key="med.batch_id">
                                            <div @click="addToCart(med)" :class="med.stock_qty <= 0 ? 'opacity-60 bg-gray-50 cursor-not-allowed' : 'hover:bg-sky-50/70 cursor-pointer'" class="p-3.5 flex items-center justify-between transition">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center font-bold text-xs shrink-0 border border-sky-100">
                                                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900 text-xs sm:text-sm flex items-center gap-1.5">
                                                            <span x-text="med.name"></span>
                                                            <template x-if="med.stock_qty <= 0">
                                                                <span class="px-1.5 py-0.2 bg-rose-100 text-rose-700 rounded text-[9px] font-bold">{{ __('Out of Stock') }}</span>
                                                            </template>
                                                        </div>
                                                        <div class="text-[11px] text-gray-500" x-text="med.generic_name + ' • ' + med.category_name"></div>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <div class="font-black text-sky-700 text-xs sm:text-sm" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(med.selling_price).toLocaleString()"></div>
                                                    <div class="text-[10px] font-bold text-gray-400" x-text="'Stock: ' + med.stock_qty + ' ' + med.base_unit + 's'"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Styled Barcode Scanner Light Blue Quick Button -->
                                <button type="button" class="btn-sky-light px-3.5 py-2.5 rounded-xl transition shrink-0 flex items-center gap-2 text-xs font-bold shadow-2xs" title="{{ __('Scan Barcode') }}">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m-7-7h1m14 0h1m-3.293-6.293l-.707.707M6.707 17.293l-.707.707m0-12l.707.707m10.586 10.586l.707.707M4 8V6a2 2 0 012-2h2m8 0h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2m-8 0H6a2 2 0 01-2-2v-2"/></svg>
                                    <span class="hidden sm:inline font-bold">{{ __('Scan Barcode') }}</span>
                                </button>

                                <!-- View Mode Toggle (Grid vs Compact Table List) -->
                                <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50 p-0.5 shrink-0 shadow-2xs">
                                    <button type="button" @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white text-sky-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800'" class="p-2 rounded-lg text-xs transition" title="{{ __('Grid View') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    </button>
                                    <button type="button" @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white text-sky-700 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800'" class="p-2 rounded-lg text-xs transition" title="{{ __('List View') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Horizontal Category Filter Pills (Light Blue Theme) -->
                            <div class="flex items-center gap-2 overflow-x-auto pt-1 pb-0.5 custom-scrollbar border-t border-gray-100">
                                <button type="button" @click="selectedCategory = ''" :class="selectedCategory === '' ? 'btn-sky-primary font-black shadow-sm' : 'bg-slate-100 hover:bg-sky-50 hover:text-sky-800 text-slate-700 font-semibold'" class="px-3.5 py-1.5 rounded-xl text-xs shrink-0 transition flex items-center gap-2">
                                    <span>{{ __('All') }}</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedCategory === '' ? 'bg-sky-800 text-white' : 'bg-slate-200 text-slate-700'" x-text="popularMedicines.length"></span>
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button" @click="selectedCategory = '{{ $cat->id }}'" :class="selectedCategory === '{{ $cat->id }}' ? 'btn-sky-primary font-black shadow-sm' : 'bg-slate-100 hover:bg-sky-50 hover:text-sky-800 text-slate-700 font-semibold'" class="px-3.5 py-1.5 rounded-xl text-xs shrink-0 transition whitespace-nowrap flex items-center gap-2">
                                        <span>{{ $cat->name }}</span>
                                        @if(isset($cat->medicines_count) && $cat->medicines_count > 0)
                                            <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="selectedCategory === '{{ $cat->id }}' ? 'bg-sky-800 text-white' : 'bg-slate-200 text-slate-700'">{{ $cat->medicines_count }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Active Catalog Header Bar -->
                        <div class="px-4 py-2 bg-slate-50 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500 shrink-0">
                            <div class="font-bold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <span>{{ __('Medicine Catalog') }}</span>
                                <span class="text-sky-700 font-extrabold" x-text="'(' + filteredPopularMedicines.length + ' available)'"></span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                {{ __('Click any product to add to order') }}
                            </div>
                        </div>

                        <!-- SEPARATELY SCROLLABLE MEDICINE CATALOG SECTION (Fixed max-height with dedicated visible scrollbar) -->
                        <div class="overflow-y-auto max-h-[640px] p-4 custom-scrollbar bg-slate-50/50">
                            
                            <!-- GRID VIEW MODE (Bigger, Clear Product Images) -->
                            <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <template x-for="med in filteredPopularMedicines" :key="med.batch_id">
                                    <div @click="addToCart(med)" :class="med.stock_qty <= 0 ? 'opacity-50 bg-gray-50 border-gray-200 cursor-not-allowed' : 'bg-white border-slate-200 hover:border-sky-500 hover:shadow-md cursor-pointer'" class="border rounded-2xl p-3.5 transition-all duration-150 flex flex-col justify-between text-left relative overflow-hidden group">
                                        
                                        <!-- Out of Stock Pill -->
                                        <template x-if="med.stock_qty <= 0">
                                            <div class="absolute top-2 right-2 px-2 py-0.5 bg-rose-500 text-white rounded-md text-[9px] font-extrabold z-10 shadow-xs">
                                                OUT OF STOCK
                                            </div>
                                        </template>

                                        <!-- Prominent Larger Medicine Visual -->
                                        <div class="h-28 sm:h-32 bg-slate-50 rounded-xl mb-3 flex items-center justify-center border border-slate-100 group-hover:bg-sky-50/40 transition p-2 relative">
                                            <template x-if="med.image_url">
                                                <img :src="med.image_url" :alt="med.name" class="max-h-full max-w-full object-contain p-1 group-hover:scale-105 transition duration-200" :class="med.stock_qty <= 0 ? 'grayscale opacity-50' : ''">
                                            </template>
                                            <template x-if="!med.image_url">
                                                <div class="w-12 h-12 rounded-full bg-sky-100/70 text-sky-600 flex items-center justify-center group-hover:scale-110 transition duration-200">
                                                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Details -->
                                        <div class="space-y-1">
                                            <h4 class="font-extrabold text-gray-900 text-xs sm:text-sm leading-tight line-clamp-2 group-hover:text-sky-700 transition" x-text="med.name" :title="med.name"></h4>
                                            <div class="text-[11px] text-gray-400 line-clamp-1" x-text="med.category_name"></div>
                                            <div class="text-xs sm:text-sm text-sky-700 font-black mt-1" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(med.selling_price).toLocaleString()"></div>
                                        </div>

                                        <!-- Stock & Quick Action Light Blue Button -->
                                        <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                            <span :class="med.stock_qty <= 0 ? 'text-rose-600 font-bold' : 'text-slate-500 font-semibold'" x-text="'Stock: ' + med.stock_qty + ' ' + med.base_unit + 's'"></span>
                                            <span class="btn-sky-light font-black px-3 py-1 rounded-lg transition">+ Add</span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- COMPACT TABLE LIST VIEW MODE -->
                            <div x-show="viewMode === 'list'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                                <table class="w-full text-left text-xs sm:text-sm divide-y divide-slate-200">
                                    <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                                        <tr>
                                            <th class="py-3 px-4">{{ __('Medicine Name') }}</th>
                                            <th class="py-3 px-4">{{ __('Category') }}</th>
                                            <th class="py-3 px-4 text-right">{{ __('Price') }}</th>
                                            <th class="py-3 px-4 text-center">{{ __('Stock') }}</th>
                                            <th class="py-3 px-4 text-right">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 font-medium">
                                        <template x-for="med in filteredPopularMedicines" :key="med.batch_id">
                                            <tr @click="addToCart(med)" :class="med.stock_qty <= 0 ? 'bg-gray-50 opacity-60 cursor-not-allowed' : 'hover:bg-sky-50/60 cursor-pointer'" class="transition">
                                                <td class="py-3 px-4 font-bold text-gray-900">
                                                    <div x-text="med.name"></div>
                                                    <div class="text-[11px] font-normal text-gray-400" x-text="med.generic_name"></div>
                                                </td>
                                                <td class="py-3 px-4 text-gray-500 text-xs" x-text="med.category_name"></td>
                                                <td class="py-3 px-4 text-right font-extrabold text-sky-700" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(med.selling_price).toLocaleString()"></td>
                                                <td class="py-3 px-4 text-center">
                                                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold" :class="med.stock_qty <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700'" x-text="med.stock_qty + ' ' + med.base_unit + 's'"></span>
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <button type="button" :disabled="med.stock_qty <= 0" :class="med.stock_qty <= 0 ? 'bg-gray-200 text-gray-400' : 'btn-sky-primary shadow-xs'" class="px-3.5 py-1.5 rounded-xl text-xs font-black transition">
                                                        <span x-text="med.stock_qty <= 0 ? 'Out' : '+ Add'"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div x-show="filteredPopularMedicines.length === 0" class="py-16 text-center text-gray-400 text-xs">
                                <p class="font-bold text-gray-600">{{ __('No medicines found matching criteria') }}</p>
                                <p class="text-[11px] text-gray-400 mt-1">{{ __('Try adjusting category filter or search query.') }}</p>
                            </div>

                        </div>

                    </div>

                    <!-- RIGHT PANE: Cart Terminal & Docked Checkout (Sticky to top on desktop) -->
                    <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden flex flex-col lg:sticky lg:top-4 lg:self-start">
                        
                        <!-- Cart Top Header -->
                        <div class="p-4 border-b border-gray-100 bg-white flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2.5">
                                <span class="p-2 bg-sky-100 text-sky-800 rounded-xl">
                                    <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                </span>
                                <div>
                                    <h3 class="font-extrabold text-gray-900 text-sm leading-tight">{{ __('Current Order') }}</h3>
                                    <div class="text-[11px] text-slate-500 font-semibold" x-text="cart.length + (cart.length === 1 ? ' item selected' : ' items selected')"></div>
                                </div>
                            </div>
                            <button type="button" @click="clearCart" x-show="cart.length > 0" class="text-xs text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>{{ __('Clear All') }}</span>
                            </button>
                        </div>

                        <!-- SEPARATELY SCROLLABLE CART LINE ITEMS SECTION (Well-Spaced, Clean Cards) -->
                        <div class="overflow-y-auto max-h-80 sm:max-h-96 p-3.5 space-y-3 custom-scrollbar bg-slate-50/40">
                            <template x-for="(item, idx) in cart" :key="idx">
                                <div class="bg-white border border-slate-200/90 rounded-2xl p-3.5 space-y-3 shadow-2xs hover:border-sky-300 hover:shadow-xs transition">
                                    
                                    <!-- Top Row: Name, Generic, Batch & Remove Button -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug truncate" x-text="item.name"></h4>
                                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                <template x-if="item.batch_number">
                                                    <span class="text-[10px] text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md font-mono font-medium" x-text="'Batch: ' + item.batch_number"></span>
                                                </template>
                                                <span class="text-[10px] text-slate-400 truncate" x-text="item.generic_name"></span>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFromCart(idx)" class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 p-1.5 rounded-xl transition shrink-0" title="{{ __('Remove item') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>

                                    <!-- Middle Row: Packaging Unit Selector & Unit Price Badge -->
                                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                                        <template x-if="item.units && item.units.length > 0">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider shrink-0">Unit:</label>
                                                <select x-model="item.unit_name" @change="changeUnit(idx, $event.target.value)" class="text-xs font-bold text-sky-950 bg-sky-50 border border-sky-300 rounded-xl py-1 px-2.5 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                                                    <template x-for="u in item.units" :key="u.unit_name">
                                                        <option :value="u.unit_name" x-text="u.unit_name + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + ' ' + item.base_unit + 's)' : '')"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>
                                        <div class="text-right shrink-0">
                                            <div class="text-[10px] text-slate-400 font-medium">Rate / Unit</div>
                                            <div class="text-xs font-bold text-slate-700" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + item.unit_price.toLocaleString()"></div>
                                        </div>
                                    </div>

                                    <!-- Bottom Row: Stepper Quantity Controls & Total Item Price -->
                                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                                        <!-- Quantity Stepper -->
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Qty:</span>
                                            <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50 shadow-2xs">
                                                <button type="button" @click="decrementQty(idx)" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-sky-500 hover:text-white transition font-black text-sm">-</button>
                                                <span class="px-2.5 py-1 font-extrabold text-slate-900 text-xs min-w-[28px] text-center bg-white" x-text="item.quantity"></span>
                                                <button type="button" @click="incrementQty(idx)" class="w-8 h-8 flex items-center justify-center text-slate-700 hover:bg-sky-500 hover:text-white transition font-black text-sm">+</button>
                                            </div>
                                        </div>

                                        <!-- Line Total -->
                                        <div class="text-right">
                                            <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Item Total</div>
                                            <div class="text-sm font-black text-sky-700" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + (item.quantity * item.unit_price).toLocaleString()"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty Cart Visual -->
                            <div x-show="cart.length === 0" class="py-14 flex flex-col items-center justify-center text-center text-gray-400 space-y-2.5">
                                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center border border-sky-100">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">{{ __('Your cart is empty') }}</p>
                                <p class="text-xs text-slate-400 max-w-[240px]">{{ __('Click any medicine on the left catalog to add to order.') }}</p>
                            </div>
                        </div>

                        <!-- Docked Checkout Area (Generously Spaced, Light Blue Actions) -->
                        <div class="border-t border-slate-200 p-4 sm:p-5 bg-slate-50 shrink-0 space-y-3.5">
                            
                            <!-- Customer Selection -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[11px] font-extrabold text-slate-600 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>{{ __('Customer') }}</span>
                                    </label>
                                    <a href="{{ route('customers.create') }}" target="_blank" class="text-xs text-sky-600 hover:text-sky-700 font-bold">+ {{ __('Add Customer') }}</a>
                                </div>
                                <select x-model="customer_id" class="w-full text-xs font-semibold border-slate-200 rounded-xl focus:ring-sky-500 focus:border-sky-500 py-2 px-3 bg-white shadow-2xs">
                                    <option value="">{{ __('Walk-in Customer') }}</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Calculations Summary -->
                            <div class="space-y-2 text-xs border-t border-slate-200 pt-3">
                                <div class="flex justify-between text-slate-600">
                                    <span>{{ __('Subtotal') }}</span>
                                    <span class="font-bold text-gray-900" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + subtotal.toLocaleString()"></span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>{{ __('Discount') }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-400">{{ setting('currency_symbol', 'UGX') }}</span>
                                        <input type="number" x-model="discount" min="0" :max="subtotal" class="w-24 text-right text-xs font-bold py-1 px-2 border-slate-200 rounded-lg focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Grand Total Banner (Light Blue Gradient Banner) -->
                            <div class="banner-sky-total p-3.5 rounded-2xl flex items-center justify-between shadow-md">
                                <span class="text-xs font-bold uppercase tracking-wider text-white">{{ __('Total Payable') }}</span>
                                <span class="text-xl font-black tracking-tight text-white" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + grandTotal.toLocaleString()"></span>
                            </div>

                            <!-- Payment Method Select (Light Blue Styled Buttons) -->
                            <div>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="payment_method = 'cash'" :class="payment_method === 'cash' ? 'btn-sky-primary shadow-sm ring-2 ring-sky-300' : 'bg-white border-2 border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-800 hover:border-sky-300'" class="py-2.5 px-2 rounded-xl text-xs transition text-center flex items-center justify-center gap-1.5 font-bold shadow-2xs">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Cash</span>
                                    </button>
                                    <button type="button" @click="payment_method = 'card'" :class="payment_method === 'card' ? 'btn-sky-primary shadow-sm ring-2 ring-sky-300' : 'bg-white border-2 border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-800 hover:border-sky-300'" class="py-2.5 px-2 rounded-xl text-xs transition text-center flex items-center justify-center gap-1.5 font-bold shadow-2xs">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>Card</span>
                                    </button>
                                    <button type="button" @click="payment_method = 'mobile_money'" :class="payment_method === 'mobile_money' ? 'btn-sky-primary shadow-sm ring-2 ring-sky-300' : 'bg-white border-2 border-slate-200 text-slate-700 hover:bg-sky-50 hover:text-sky-800 hover:border-sky-300'" class="py-2.5 px-2 rounded-xl text-xs transition text-center flex items-center justify-center gap-1.5 font-bold shadow-2xs">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span>M-Money</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Complete Sale Light Blue Primary Action Button -->
                            <button type="submit" :disabled="cart.length === 0 || isSubmitting" :class="cart.length === 0 || isSubmitting ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'btn-sky-primary hover:opacity-95 active:scale-[0.99] shadow-lg hover:shadow-xl'" class="w-full py-3.5 px-5 rounded-2xl text-xs sm:text-sm font-black transition-all flex items-center justify-center gap-2">
                                <template x-if="isSubmitting">
                                    <div class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span>{{ __('Processing Sale...') }}</span>
                                    </div>
                                </template>
                                <template x-if="!isSubmitting">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ __('Complete Sale') }}</span>
                                    </div>
                                </template>
                            </button>

                        </div>

                    </div>

                </div>
            </form>

            <!-- Completed Sale Success Modal Overlay -->
            <div x-show="completedSaleModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                <div class="bg-white rounded-3xl shadow-2xl max-w-xs w-full p-5 text-center space-y-3.5 border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
                    <div class="w-12 h-12 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                        <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">{{ __('Sale Completed!') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="'Invoice: #' + completedSaleData.invoice_no"></p>
                        <div class="mt-2 text-xl font-black text-sky-600" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(completedSaleData.total).toLocaleString()"></div>
                    </div>

                    <div class="flex flex-col gap-2 pt-1">
                        <a :href="completedSaleData.invoice_url" target="_blank" class="btn-sky-primary w-full py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>{{ __('Print Receipt') }}</span>
                        </a>
                        <button type="button" @click="completedSaleModal = false" class="btn-sky-light w-full py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>{{ __('Start New Sale') }}</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Custom Styling & Visible Light Blue Custom Scrollbar -->
    <style>
        .btn-sky-primary {
            background-color: #0284c7 !important;
            color: #ffffff !important;
        }
        .btn-sky-primary:hover {
            background-color: #0369a1 !important;
        }
        .btn-sky-light {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            border: 1px solid #7dd3fc !important;
        }
        .btn-sky-light:hover {
            background-color: #0284c7 !important;
            color: #ffffff !important;
        }
        .banner-sky-total {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 8px;
            border: 2px solid #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #0284c7;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <!-- Alpine POS Logic Script -->
    <script>
        function posForm() {
            return {
                searchQuery: '',
                searchResults: [],
                popularMedicines: @json($popularMedicines ?? []),
                selectedCategory: '',
                viewMode: 'grid', // 'grid' or 'list'
                cart: [],
                customer_id: '',
                payment_method: 'cash',
                discount: 0,
                tax: 0,
                isSubmitting: false,
                errorMessage: '',
                completedSaleModal: false,
                completedSaleData: { invoice_no: '', total: 0, invoice_url: '#' },

                searchMedicines() {
                    const q = this.searchQuery.trim();
                    if (q.length < 1) {
                        this.searchResults = [];
                        return;
                    }
                    fetch('{{ route("medicines.search") }}?q=' + encodeURIComponent(q), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return res.json();
                        }
                        return [];
                    })
                    .then(data => {
                        this.searchResults = data || [];
                    })
                    .catch(() => {
                        this.searchResults = [];
                    });
                },

                get filteredPopularMedicines() {
                    let list = this.popularMedicines;
                    if (this.selectedCategory) {
                        list = list.filter(m => String(m.category_id) === String(this.selectedCategory));
                    }
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(m => 
                            m.name.toLowerCase().includes(q) || 
                            (m.generic_name && m.generic_name.toLowerCase().includes(q)) ||
                            (m.category_name && m.category_name.toLowerCase().includes(q))
                        );
                    }
                    return list;
                },

                addToCart(med) {
                    if (med.stock_qty <= 0) {
                        alert('Warning: Cannot add "' + med.name + '": Product is Out of Stock!');
                        return;
                    }
                    const units = med.units && med.units.length ? med.units : [{
                        unit_name: med.base_unit || 'Tablet',
                        conversion_factor: 1.0,
                        price: parseFloat(med.selling_price),
                        is_base: true
                    }];

                    const selectedUnit = units[0];
                    const existingIdx = this.cart.findIndex(i => i.batch_id === med.batch_id && i.unit_name === selectedUnit.unit_name);
                    
                    if (existingIdx > -1) {
                        const currentItem = this.cart[existingIdx];
                        const reqBaseQty = (currentItem.quantity + 1) * currentItem.conversion_factor;
                        if (reqBaseQty <= med.stock_qty) {
                            currentItem.quantity++;
                        } else {
                            alert('Warning: Cannot add more than available stock (' + med.stock_qty + ')!');
                        }
                    } else {
                        this.cart.push({
                            medicine_id: med.medicine_id,
                            batch_id: med.batch_id,
                            batch_number: med.batch_number,
                            name: med.name,
                            generic_name: med.generic_name,
                            base_unit: med.base_unit || 'Tablet',
                            unit_name: selectedUnit.unit_name,
                            conversion_factor: parseFloat(selectedUnit.conversion_factor),
                            unit_price: parseFloat(selectedUnit.price),
                            quantity: 1,
                            stock_qty: med.stock_qty,
                            image_url: med.image_url,
                            units: units
                        });
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                changeUnit(idx, newUnitName) {
                    const item = this.cart[idx];
                    const foundUnit = item.units.find(u => u.unit_name === newUnitName);
                    if (foundUnit) {
                        item.unit_name = foundUnit.unit_name;
                        item.conversion_factor = parseFloat(foundUnit.conversion_factor);
                        item.unit_price = parseFloat(foundUnit.price);
                    }
                },

                incrementQty(idx) {
                    const item = this.cart[idx];
                    const reqBaseQty = (item.quantity + 1) * item.conversion_factor;
                    if (reqBaseQty <= item.stock_qty) {
                        item.quantity++;
                    } else {
                        alert('Warning: Cannot add more than available stock (' + item.stock_qty + ' base units)!');
                    }
                },

                decrementQty(idx) {
                    if (this.cart[idx].quantity > 1) {
                        this.cart[idx].quantity--;
                    } else {
                        this.removeFromCart(idx);
                    }
                },

                removeFromCart(idx) {
                    this.cart.splice(idx, 1);
                },

                clearCart() {
                    if (confirm('{{ __("Are you sure you want to clear the cart?") }}')) {
                        this.cart = [];
                    }
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },

                get grandTotal() {
                    const sub = this.subtotal;
                    const disc = parseFloat(this.discount) || 0;
                    const tx = parseFloat(this.tax) || 0;
                    return Math.max(0, sub - disc + tx);
                },

                submitSale() {
                    if (this.cart.length === 0) {
                        alert('{{ __("Please add at least one medicine to the cart.") }}');
                        return;
                    }
                    for (let item of this.cart) {
                        const totalBaseQtyNeeded = Math.ceil(item.quantity * item.conversion_factor);
                        if (totalBaseQtyNeeded > item.stock_qty) {
                            alert('Warning: Insufficient stock for ' + item.name + '. Required ' + totalBaseQtyNeeded + ' base units, but only ' + item.stock_qty + ' available.');
                            return;
                        }
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    const payload = {
                        customer_id: this.customer_id || null,
                        payment_method: this.payment_method,
                        subtotal: this.subtotal,
                        discount: this.discount,
                        tax: this.tax,
                        total: this.grandTotal,
                        items: this.cart.map(item => ({
                            medicine_id: item.medicine_id,
                            batch_id: item.batch_id,
                            unit_name: item.unit_name,
                            unit_quantity: item.quantity,
                            quantity: Math.ceil(item.quantity * item.conversion_factor),
                            unit_price: item.unit_price,
                        }))
                    };

                    fetch('{{ route("sales.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type');
                        let data = {};
                        if (contentType && contentType.includes('application/json')) {
                            data = await res.json();
                        } else {
                            const text = await res.text();
                            throw new Error('Server returned an unexpected response (Status ' + res.status + ').');
                        }
                        if (!res.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to process sale.'));
                        }
                        return data;
                    })
                    .then(data => {
                        this.isSubmitting = false;
                        this.completedSaleData = {
                            invoice_no: data.invoice_no,
                            total: data.total,
                            invoice_url: data.invoice_url
                        };
                        this.completedSaleModal = true;

                        // Update stock quantities in local popular medicines grid in real-time
                        if (data.updated_medicines && Array.isArray(data.updated_medicines)) {
                            data.updated_medicines.forEach(upd => {
                                const popMed = this.popularMedicines.find(pm => pm.medicine_id === upd.medicine_id);
                                if (popMed) {
                                    popMed.stock_qty = upd.stock_qty;
                                }
                            });
                        }

                        // Reset cart state smoothly
                        this.cart = [];
                        this.discount = 0;
                        this.tax = 0;
                    })
                    .catch(err => {
                        this.isSubmitting = false;
                        this.errorMessage = err.message || 'An error occurred while processing the sale.';
                    });
                }
            };
        }
    </script>
</x-app-layout>
