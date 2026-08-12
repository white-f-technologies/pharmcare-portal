<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('New Sale') }}</h2>
                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                </div>
            </div>
            <div class="flex items-center space-x-2 text-sm">
                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen" x-data="posForm()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Error Banner -->
            <div x-show="errorMessage" x-transition class="p-4 bg-rose-100 border border-rose-400 text-rose-800 rounded-xl shadow-sm flex items-center justify-between" style="display: none;">
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-rose-600 hover:text-rose-800 font-bold text-sm">&times;</button>
            </div>

            <form @submit.prevent="submitSale" id="sale-form">
                @csrf
                <input type="hidden" name="customer_id" x-model="customer_id">
                <input type="hidden" name="payment_method" x-model="payment_method">
                <input type="hidden" name="subtotal" x-model="subtotal">
                <input type="hidden" name="discount" x-model="discount">
                <input type="hidden" name="tax" x-model="tax">
                <input type="hidden" name="total" x-model="grandTotal">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <!-- Left Column: Search & Popular Medicines (7 Columns) -->
                    <div class="lg:col-span-7 space-y-5">
                        
                        <!-- Search & Barcode Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                            <div class="relative" @click.outside="searchResults = []">
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" x-model="searchQuery" @input="searchMedicines" placeholder="{{ __('Search medicine by name or scan barcode (type 1 letter...)') }}" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition" autofocus />
                                    </div>
                                    <button type="button" class="p-2.5 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-600 transition" title="{{ __('Barcode Scanner') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m12 6H8m12-12H8"/></svg>
                                    </button>
                                </div>

                                <!-- Live Search Results Dropdown -->
                                <div x-show="searchResults.length > 0" class="absolute z-30 mt-2 w-full bg-white shadow-xl rounded-xl border border-gray-100 max-h-72 overflow-y-auto divide-y divide-gray-100" style="display: none;">
                                    <template x-for="med in searchResults" :key="med.batch_id">
                                        <div @click="addToCart(med)" :class="med.stock_qty <= 0 ? 'opacity-60 bg-gray-50 cursor-not-allowed' : 'hover:bg-emerald-50 cursor-pointer'" class="p-3 flex items-center justify-between transition">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gray-100 rounded-lg p-1 flex items-center justify-center shrink-0 border border-gray-200">
                                                    <template x-if="med.image_url">
                                                        <img :src="med.image_url" class="w-full h-full object-contain">
                                                    </template>
                                                    <template x-if="!med.image_url">
                                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                                                        <span x-text="med.name"></span>
                                                        <template x-if="med.stock_qty <= 0">
                                                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded text-[10px] font-bold">{{ __('Out of Stock') }}</span>
                                                        </template>
                                                    </div>
                                                    <div class="text-xs text-gray-500" x-text="med.generic_name"></div>
                                                    <div class="text-xs" :class="med.stock_qty <= 0 ? 'text-rose-600 font-bold' : 'text-gray-400'" x-text="'Batch: ' + med.batch_number + ' • Stock: ' + med.stock_qty"></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-emerald-600 text-sm" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(med.selling_price).toLocaleString()"></div>
                                                <button type="button" :disabled="med.stock_qty <= 0" :class="med.stock_qty <= 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-600 text-white'" class="mt-1 px-2.5 py-1 rounded text-xs font-semibold transition">
                                                    <span x-text="med.stock_qty <= 0 ? 'Out of Stock' : '+ Add'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Popular Medicines Grid Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="font-bold text-gray-800 text-base mb-4 flex items-center justify-between">
                                <span>{{ __('Popular Medicines') }}</span>
                                <span class="text-xs text-gray-400 font-normal" x-show="searchQuery.trim().length > 0" x-text="'Showing matches for &quot;' + searchQuery + '&quot;'"></span>
                            </h3>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="med in filteredPopularMedicines" :key="med.batch_id">
                                    <div class="border border-gray-200 rounded-xl p-3 bg-white hover:border-emerald-300 hover:shadow-md transition flex flex-col justify-between text-center group relative overflow-hidden">
                                        
                                        <!-- Out of Stock Badge -->
                                        <template x-if="med.stock_qty <= 0">
                                            <div class="absolute top-2 right-2 px-2 py-0.5 bg-rose-500 text-white rounded text-[10px] font-bold z-10 shadow-sm">
                                                {{ __('Out of Stock') }}
                                            </div>
                                        </template>

                                        <div class="h-28 bg-slate-50 rounded-lg p-2 mb-3 flex items-center justify-center border border-gray-100 group-hover:bg-emerald-50/50 transition">
                                            <template x-if="med.image_url">
                                                <img :src="med.image_url" :alt="med.name" class="max-h-full max-w-full object-contain" :class="med.stock_qty <= 0 ? 'grayscale opacity-50' : ''">
                                            </template>
                                            <template x-if="!med.image_url">
                                                <svg class="w-12 h-12 text-emerald-500" :class="med.stock_qty <= 0 ? 'text-gray-300' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                            </template>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-xs line-clamp-1" x-text="med.name"></h4>
                                            <div class="text-xs text-emerald-600 font-semibold mt-1" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + parseFloat(med.selling_price).toLocaleString()"></div>
                                            <div class="text-[10px] mt-0.5" :class="med.stock_qty <= 0 ? 'text-rose-600 font-bold' : 'text-gray-400'" x-text="'Stock: ' + med.stock_qty"></div>
                                        </div>

                                        <button type="button" @click="addToCart(med)" :disabled="med.stock_qty <= 0" :class="med.stock_qty <= 0 ? 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white'" class="mt-3 w-full py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1">
                                            <span x-text="med.stock_qty <= 0 ? '🚫 Out of Stock' : '+ Add'"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div x-show="filteredPopularMedicines.length === 0" class="py-8 text-center text-gray-400 text-xs">
                                {{ __('No medicines match your search.') }}
                            </div>

                            <div class="mt-5 text-center">
                                <a href="{{ route('medicines.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-gray-700 text-xs font-semibold rounded-lg transition gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    {{ __('View All Medicines') }}
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Cart & Checkout (5 Columns) -->
                    <div class="lg:col-span-5 space-y-5">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
                            
                            <!-- Cart Header -->
                            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                                    <span>{{ __('Cart') }}</span>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold" x-text="cart.length"></span>
                                </h3>
                                <button type="button" @click="clearCart" x-show="cart.length > 0" class="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    {{ __('Clear Cart') }}
                                </button>
                            </div>

                            <!-- Cart Table Header -->
                            <div x-show="cart.length > 0">
                                <div class="grid grid-cols-12 text-[11px] font-bold text-gray-400 uppercase tracking-wider pb-2 border-b border-gray-100">
                                    <div class="col-span-5">{{ __('Medicine') }}</div>
                                    <div class="col-span-3 text-center">{{ __('QTY') }}</div>
                                    <div class="col-span-2 text-right">{{ __('Price') }}</div>
                                    <div class="col-span-2 text-right">{{ __('Total') }}</div>
                                </div>

                                 <!-- Cart Items List -->
                                <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto py-2">
                                    <template x-for="(item, idx) in cart" :key="idx">
                                        <div class="py-3 space-y-1 text-xs">
                                            <div class="grid grid-cols-12 items-center">
                                                <div class="col-span-5 flex items-center gap-2 pr-1">
                                                    <div class="w-8 h-8 bg-gray-50 border border-gray-200 rounded p-0.5 shrink-0 flex items-center justify-center">
                                                        <template x-if="item.image_url">
                                                            <img :src="item.image_url" class="max-h-full max-w-full object-contain">
                                                        </template>
                                                        <template x-if="!item.image_url">
                                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                                        </template>
                                                    </div>
                                                    <div class="truncate">
                                                        <div class="font-bold text-gray-800 truncate" x-text="item.name"></div>
                                                        <div class="text-[10px] text-gray-400 font-semibold truncate" x-text="item.batch_number ? 'Batch: ' + item.batch_number : ''"></div>
                                                    </div>
                                                </div>

                                                <!-- Qty stepper controls -->
                                                <div class="col-span-3 flex items-center justify-center">
                                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                                        <button type="button" @click="decrementQty(idx)" class="px-2 py-1 text-gray-600 hover:bg-gray-200 transition font-bold">-</button>
                                                        <span class="px-2 py-1 font-bold text-gray-800 text-xs" x-text="item.quantity"></span>
                                                        <button type="button" @click="incrementQty(idx)" class="px-2 py-1 text-gray-600 hover:bg-gray-200 transition font-bold">+</button>
                                                    </div>
                                                </div>

                                                <div class="col-span-2 text-right font-medium text-gray-700 text-[11px]" x-text="item.unit_price.toLocaleString()"></div>

                                                <div class="col-span-2 flex items-center justify-end gap-1.5">
                                                    <span class="font-bold text-gray-900 text-xs" x-text="(item.quantity * item.unit_price).toLocaleString()"></span>
                                                    <button type="button" @click="removeFromCart(idx)" class="text-red-400 hover:text-red-600 transition p-0.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Packaging Unit Selector -->
                                            <template x-if="item.units && item.units.length > 0">
                                                <div class="flex items-center justify-between pl-10 pr-2 pt-1">
                                                    <span class="text-[10px] text-gray-400 font-semibold">Unit:</span>
                                                    <select x-model="item.unit_name" @change="changeUnit(idx, $event.target.value)" class="text-[10px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 rounded py-0.5 px-1.5 focus:ring-emerald-500">
                                                        <template x-for="u in item.units" :key="u.unit_name">
                                                            <option :value="u.unit_name" x-text="u.unit_name + (u.conversion_factor > 1 ? ' (' + u.conversion_factor + ' ' + item.base_unit + 's)' : '')"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div x-show="cart.length === 0" class="py-12 text-center text-gray-400 space-y-2">
                                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                <p class="text-sm font-medium">{{ __('Your cart is empty') }}</p>
                                <p class="text-xs text-gray-400">{{ __('Select a medicine or search above to start adding items.') }}</p>
                            </div>

                            <!-- Customer Selection -->
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <label class="text-xs font-bold text-gray-600 block mb-1">{{ __('Customer') }}</label>
                                <select x-model="customer_id" class="w-full text-xs border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">{{ __('Walk-in Customer') }}</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->name }} ({{ $cust->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cart Summary Lines -->
                            <div class="mt-4 pt-3 border-t border-gray-100 space-y-2 text-xs">
                                <div class="flex justify-between text-gray-600 font-medium">
                                    <span>{{ __('Subtotal') }}</span>
                                    <span class="font-bold text-gray-800" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + subtotal.toLocaleString()"></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-600">
                                    <span>{{ __('Discount') }}</span>
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="discount" min="0" class="w-24 text-right py-1 px-2 border-gray-200 rounded text-xs focus:ring-emerald-500 focus:border-emerald-500" placeholder="0">
                                        <span class="text-gray-400 font-semibold">{{ setting('currency_symbol', 'UGX') }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-gray-600">
                                    <span>{{ __('Tax') }}</span>
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model.number="tax" min="0" class="w-24 text-right py-1 px-2 border-gray-200 rounded text-xs focus:ring-emerald-500 focus:border-emerald-500" placeholder="0">
                                        <span class="text-gray-400 font-semibold">{{ setting('currency_symbol', 'UGX') }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-3 border-t border-gray-200 text-lg font-bold text-emerald-600">
                                    <span>{{ __('Total') }}</span>
                                    <span x-text="'{{ setting('currency_symbol', 'UGX') }} ' + grandTotal.toLocaleString()"></span>
                                </div>
                            </div>

                            <!-- Payment Method Toggles -->
                            <div class="mt-5 space-y-2">
                                <label class="text-xs font-bold text-gray-600 block">{{ __('Payment Method') }}</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="payment_method = 'cash'" :class="payment_method === 'cash' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'" class="py-2.5 px-2 border rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                        💵 {{ __('Cash') }}
                                    </button>
                                    <button type="button" @click="payment_method = 'card'" :class="payment_method === 'card' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'" class="py-2.5 px-2 border rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                        💳 {{ __('Card') }}
                                    </button>
                                    <button type="button" @click="payment_method = 'mobile_money'" :class="payment_method === 'mobile_money' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 font-bold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'" class="py-2.5 px-2 border rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                        📱 {{ __('Mobile Money') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Complete Sale Action Button -->
                            <div class="mt-6">
                                <button type="submit" :disabled="cart.length === 0 || isSubmitting" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl font-bold text-base shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                                    <template x-if="!isSubmitting">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('Complete Sale') }}
                                        </span>
                                    </template>
                                    <template x-if="isSubmitting">
                                        <span class="flex items-center gap-2">
                                            <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            {{ __('Processing......') }}
                                        </span>
                                    </template>
                                </button>
                            </div>

                        </div>

                    </div>

                </div>
            </form>

        </div>

        <!-- AJAX Success Modal Overlay (No Page Reload!) -->
        <div x-show="completedSaleModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
            <div @click.outside="completedSaleModal = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-md w-full p-6 text-center space-y-5 animate-in fade-in zoom-in duration-200">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-xl text-gray-900">{{ __('Sale Completed Successfully!') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('Transaction processed seamlessly without page reload.') }}</p>
                </div>
                
                <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 text-left space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">{{ __('Invoice No') }}</span>
                        <span class="font-bold text-gray-900" x-text="completedSaleData.invoice_no"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">{{ __('Total Amount') }}</span>
                        <span class="font-extrabold text-emerald-600" x-text="'{{ setting('currency_symbol', 'UGX') }} ' + completedSaleData.total.toLocaleString()"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <a :href="completedSaleData.invoice_url" target="_blank" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                        🖨️ {{ __('Print Receipt / Invoice') }}
                    </a>
                    <button type="button" @click="completedSaleModal = false" class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl text-xs font-bold transition">
                        ➕ {{ __('Start New Sale') }}
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function posForm() {
            return {
                searchQuery: '',
                searchResults: [],
                popularMedicines: @json($popularMedicines ?? []),
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
                    if (!this.searchQuery.trim()) {
                        return this.popularMedicines;
                    }
                    const q = this.searchQuery.toLowerCase().trim();
                    return this.popularMedicines.filter(m => 
                        m.name.toLowerCase().includes(q) || 
                        (m.generic_name && m.generic_name.toLowerCase().includes(q))
                    );
                },

                addToCart(med) {
                    if (med.stock_qty <= 0) {
                        alert('⚠️ Cannot add "' + med.name + '": Product is Out of Stock!');
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
                            alert('⚠️ Cannot add more than available stock (' + med.stock_qty + ')!');
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
                        alert('⚠️ Cannot add more than available stock (' + item.stock_qty + ' base units)!');
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
                            alert('⚠️ Insufficient stock for ' + item.name + '. Required ' + totalBaseQtyNeeded + ' base units, but only ' + item.stock_qty + ' available.');
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
