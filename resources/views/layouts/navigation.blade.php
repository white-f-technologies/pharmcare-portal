<nav x-data="{ open: false }" class="bg-white border-b border-gray-200/80 shadow-sm sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-12 w-auto max-h-12 fill-current text-emerald-600" />
                    </a>
                </div>

                <!-- Main Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-sm font-semibold">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Direct POS Link -->
                    <a href="{{ route('sales.create') }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <span>{{ __('POS (New Sale)') }}</span>
                    </a>

                    <!-- Medicines Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-2 py-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
                            {{ __('Medicines') }}
                            <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg bg-white border border-gray-100 py-1" style="display: none;">
                            <a href="{{ route('medicines.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('All Medicines') }}</a>
                            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                                <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Categories') }}</a>
                                <a href="{{ route('stock.adjustments.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Stock Adjustments & Damage') }}</a>
                            @endif
                        </div>
                    </div>

                    <!-- Sales Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-2 py-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
                            {{ __('Sales') }}
                            <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg bg-white border border-gray-100 py-1" style="display: none;">
                            <a href="{{ route('sales.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('New Sale (POS)') }}</a>
                            <a href="{{ route('sales.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Sales History') }}</a>
                            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                                <a href="{{ route('sales.returns.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Customer Returns') }}</a>
                            @endif
                        </div>
                    </div>

                    @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                        <x-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')" class="text-sm font-semibold">
                            {{ __('Purchases') }}
                        </x-nav-link>
                        <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*')" class="text-sm font-semibold">
                            {{ __('Expenses') }}
                        </x-nav-link>
                    @endif

                    <!-- People Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-2 py-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
                            {{ __('People') }}
                            <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg bg-white border border-gray-100 py-1" style="display: none;">
                            <a href="{{ route('customers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Customers') }}</a>
                            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                                <a href="{{ route('suppliers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Suppliers') }}</a>
                            @endif
                        </div>
                    </div>

                    <x-nav-link :href="route('prescriptions.index')" :active="request()->routeIs('prescriptions.*')" class="text-sm font-semibold">
                        {{ __('Prescriptions') }}
                    </x-nav-link>

                    @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                        <!-- Reports Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-2 py-2 text-sm font-semibold text-gray-600 hover:text-emerald-600 transition">
                                {{ __('Reports') }}
                                <svg class="ms-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg bg-white border border-gray-100 py-1" style="display: none;">
                                <a href="{{ route('reports.sales') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Sales Report') }}</a>
                                <a href="{{ route('reports.inventory') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Inventory Report') }}</a>
                                <a href="{{ route('reports.expiry') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium">{{ __('Expiry Report') }}</a>
                                <a href="{{ route('reports.ledger') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium border-t border-gray-100">{{ __('Stock Ledger Audit') }}</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settings & Profile Dropdown (Only Avatar Icon) -->
            <div class="hidden sm:flex sm:items-center sm:ms-4">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="p-0.5 rounded-full text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition border border-gray-200 shadow-sm group">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-extrabold text-sm shadow-inner group-hover:scale-105 transition-transform">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Profile Header inside Dropdown -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-slate-50/80">
                            <p class="text-sm font-extrabold text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">{{ Auth::user()->email }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-emerald-100 text-emerald-800 tracking-wider">
                                    {{ Auth::user()->role }}
                                </span>
                                @php
                                    $edition = app_edition();
                                    $isPrem = feature_enabled('advanced_inventory');
                                @endphp
                                <a href="{{ route('settings.license') }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold tracking-wider uppercase border transition {{ $isPrem ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white border-amber-400 hover:from-amber-600 hover:to-amber-700' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200' }}">
                                    <span>{{ $edition }}</span>
                                </a>
                            </div>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ __('Profile') }}</span>
                        </x-dropdown-link>

                        @if(Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('users.index')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>{{ __('User Management') }}</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('settings.edit')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ __('System Settings') }}</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('settings.license')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>{{ __('License & Edition') }}</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('backups.index')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                <span>{{ __('Database Backups') }}</span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('admin.diagnostics')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>{{ __('System Diagnostics') }}</span>
                            </x-dropdown-link>

                            @if(file_exists(storage_path('keys/private.key')))
                                <x-dropdown-link :href="route('vendor.dashboard')" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>{{ __('Vendor Control Center') }}</span>
                                </x-dropdown-link>
                            @endif
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-red-600 hover:text-red-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>{{ __('Log Out') }}</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-b border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>{{ __('Dashboard') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sales.create')" :active="request()->routeIs('sales.create')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <span>{{ __('POS (New Sale)') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('medicines.index')" :active="request()->routeIs('medicines.*')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>{{ __('Medicines') }}</span>
            </x-responsive-nav-link>
            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>{{ __('Categories') }}</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>{{ __('Purchases') }}</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>{{ __('Suppliers') }}</span>
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.index')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>{{ __('Sales History') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>{{ __('Customers') }}</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('prescriptions.index')" :active="request()->routeIs('prescriptions.*')" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                <span>{{ __('Prescriptions') }}</span>
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4">
                <div class="font-bold text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-emerald-600 uppercase font-semibold">{{ Auth::user()->role }} • {{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>{{ __('Profile') }}</span>
                </x-responsive-nav-link>

                @if(Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('users.index')" class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>{{ __('User Management') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('settings.edit')" class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('System Settings') }}</span>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('backups.index')" class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        <span>{{ __('Database Backups') }}</span>
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();" class="flex items-center gap-2 text-red-600">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ __('Log Out') }}</span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
