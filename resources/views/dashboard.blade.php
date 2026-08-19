<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">{{ __('Dashboard') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Welcome back! Here\'s what\'s happening in your pharmacy today.') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center px-3.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 shadow-sm gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ __('Today, ') . date('d M Y') }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @session('success')
                <div class="p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-xl shadow-sm">{{ $value }}</div>
            @endsession

            {{-- Software Update Notification Banner --}}
            <div id="update-banner" class="hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-lg border border-indigo-500/30 p-5">
                    {{-- Decorative background pattern --}}
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="update-dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="white"/></pattern></defs><rect fill="url(#update-dots)" width="100%" height="100%"/></svg>
                    </div>

                    <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            {{-- Animated bell icon --}}
                            <div class="p-2.5 bg-white/15 rounded-xl backdrop-blur-sm shrink-0 mt-0.5">
                                <svg class="w-6 h-6 text-amber-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-extrabold text-sm flex items-center gap-2">
                                    <span>PharmCare <span id="update-version" class="text-amber-300"></span> is available!</span>
                                    <span class="px-2 py-0.5 bg-amber-400 text-amber-900 rounded-full text-[10px] font-black uppercase tracking-wide">New</span>
                                </h3>
                                <p id="update-notes" class="text-indigo-200 text-xs mt-1 max-w-lg leading-relaxed"></p>
                                <p id="update-date" class="text-indigo-300/70 text-[10px] mt-1 font-semibold"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a id="update-download-btn" href="#" target="_blank"
                               class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white text-indigo-700 rounded-xl text-xs font-extrabold shadow-md hover:bg-indigo-50 hover:shadow-lg transition-all duration-200 group">
                                <svg class="w-4 h-4 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Update
                            </a>
                            <button onclick="remindLater()" class="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition backdrop-blur-sm border border-white/10">
                                Later
                            </button>
                            <button onclick="dismissUpdate()" class="p-2 bg-white/10 hover:bg-white/20 text-white/70 hover:text-white rounded-xl transition backdrop-blur-sm border border-white/10" title="Dismiss this version">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Version comparison --}}
                    <div class="relative mt-3 pt-3 border-t border-white/10 flex items-center gap-4 text-[11px]">
                        <span class="text-indigo-300 font-semibold">Current: <span class="text-white font-bold">v{{ config('license.version', '2.2.0') }}</span></span>
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        <span class="text-indigo-300 font-semibold">Latest: <span id="update-version-compare" class="text-amber-300 font-bold"></span></span>
                    </div>
                </div>
            </div>


            <div class="grid grid-cols-2 md:grid-cols-3 {{ ($lowStock ?? 0) > 0 ? 'lg:grid-cols-6' : 'lg:grid-cols-5' }} gap-4">
                
                <!-- Medicines -->
                <a href="{{ route('medicines.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-emerald-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-emerald-700 transition-colors">{{ __('Medicines') }}</p>
                        <p class="text-lg font-extrabold text-gray-900 mt-0.5" id="stat-medicines">{{ $totalMedicines ?? 0 }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Total Items') }}</p>
                    </div>
                </a>

                <!-- Low Stock (Hidden when 0 to give Today's Sales extra space) -->
                @if(($lowStock ?? 0) > 0)
                <a href="{{ route('reports.inventory') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-rose-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-rose-50 text-rose-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.072 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-rose-700 transition-colors">{{ __('Low Stock') }}</p>
                        <p class="text-lg font-extrabold text-rose-600 mt-0.5" id="stat-lowstock">{{ $lowStock ?? 0 }}</p>
                        <p class="text-[10px] text-rose-500 mt-0.5 font-medium">{{ __('Need Attention') }}</p>
                    </div>
                </a>
                @endif

                <!-- Today's Sales -->
                <a href="{{ route('reports.sales') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-sky-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-sky-50 text-sky-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1 overflow-visible">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-sky-700 transition-colors">{{ __('Today\'s Sales') }}</p>
                        <p class="text-base font-extrabold text-gray-900 mt-0.5 whitespace-nowrap" id="stat-sales">{{ setting('currency_symbol', 'UGX') }} {{ format_price($todaySalesTotal ?? 0) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Total Sales') }}</p>
                    </div>
                </a>

                <!-- Total Sales -->
                <a href="{{ route('sales.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-purple-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-purple-50 text-purple-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-purple-700 transition-colors">{{ __('Total Sales') }}</p>
                        <p class="text-lg font-extrabold text-gray-900 mt-0.5" id="stat-total-sales">{{ $totalSales ?? 0 }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Transactions') }}</p>
                    </div>
                </a>

                <!-- Total Purchases -->
                <a href="{{ route('purchases.index') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-amber-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-amber-50 text-amber-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-amber-700 transition-colors">{{ __('Total Purchases') }}</p>
                        <p class="text-lg font-extrabold text-gray-900 mt-0.5" id="stat-total-purchases">{{ $totalPurchases ?? 0 }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Transactions') }}</p>
                    </div>
                </a>

                <!-- Today's Daily Net Profit -->
                <a href="{{ route('reports.sales') }}" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200/80 hover:border-emerald-400 hover:shadow-md transition cursor-pointer flex items-center gap-3 group">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1 overflow-visible">
                        <p class="text-xs text-gray-500 font-medium truncate group-hover:text-emerald-700 transition-colors">{{ __('Today\'s Net Profit') }}</p>
                        <p class="text-base font-extrabold text-emerald-600 mt-0.5 whitespace-nowrap" id="stat-profit">{{ setting('currency_symbol', 'UGX') }} {{ format_price($todayProfitTotal ?? 0) }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ __('Accumulated Daily Profit') }}</p>
                    </div>
                </a>

            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sales Overview Chart -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200/80 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-base">{{ __('Sales Overview') }} <span class="text-xs text-gray-400 font-normal">({{ __('This Week') }})</span></h3>
                        </div>
                        <a href="{{ route('reports.sales') }}" class="text-xs text-gray-500 hover:text-emerald-600 font-semibold border border-gray-200 rounded-lg px-2.5 py-1 transition">{{ __('View Report') }}</a>
                    </div>
                    <div class="relative h-72">
                        <canvas id="weeklyFlowChart"></canvas>
                    </div>
                </div>

                <!-- Top Categories Donut Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Top Categories') }}</h3>
                        <a href="{{ route('categories.index') }}" class="text-xs text-gray-500 hover:text-emerald-600 font-semibold">{{ __('View All') }}</a>
                    </div>
                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="categoryDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables Row 1: Recent Sales & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Sales -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Recent Sales') }}</h3>
                        <a href="{{ route('sales.index') }}" class="text-xs text-gray-500 hover:text-emerald-600 font-semibold">{{ __('View All') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Invoice') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Payment') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs" id="sales-table-body">
                                @forelse($recentSales ?? [] as $sale)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800 font-semibold">{{ $sale->invoice_no }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $sale->customer?->name ?? __('Walk-in') }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->total) }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ ucfirst($sale->payment_method) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2.5 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full {{ $sale->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($sale->payment_status) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $sale->created_at->format('d M H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-xs text-gray-400 text-center">{{ __('No recent sales recorded.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden flex flex-col justify-between">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Recent Activity') }}</h3>
                        <span class="text-xs text-gray-400">{{ __('Live feed') }}</span>
                    </div>
                    <div class="p-4 space-y-3 max-h-80 overflow-y-auto" id="activity-feed">
                        @forelse($recentActivities ?? [] as $activity)
                            <div class="flex items-start gap-3 text-xs border-b border-gray-50 pb-2.5 last:border-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    @if(str_contains($activity->action, 'sale')) bg-emerald-100 text-emerald-600
                                    @elseif(str_contains($activity->action, 'purchase')) bg-purple-100 text-purple-600
                                    @elseif(str_contains($activity->action, 'settings')) bg-sky-100 text-sky-600
                                    @elseif(str_contains($activity->action, 'batch')) bg-amber-100 text-amber-600
                                    @else bg-gray-100 text-gray-600 @endif">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-gray-800 font-medium leading-snug">{{ $activity->description }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-6">{{ __('No activity logs recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Tables Row 2: Recent Purchases & Expiring Soon -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Purchases -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Recent Purchases') }}</h3>
                        <a href="{{ route('purchases.index') }}" class="text-xs text-gray-500 hover:text-emerald-600 font-semibold">{{ __('View All') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-xs">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Invoice') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Supplier') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100" id="purchases-table-body">
                                @forelse($recentPurchases ?? [] as $purchase)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800 font-semibold">{{ $purchase->invoice_no }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $purchase->supplier?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($purchase->total) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2.5 py-0.5 inline-flex text-[10px] leading-4 font-bold rounded-full {{ $purchase->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($purchase->status) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $purchase->created_at->format('d M H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-xs text-gray-400 text-center space-y-2">
                                            <div class="w-10 h-10 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            </div>
                                            <p>{{ __('No purchases yet') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Expiring Soon') }}</h3>
                        <a href="{{ route('reports.expiry') }}" class="text-xs text-gray-500 hover:text-emerald-600 font-semibold">{{ __('View All') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-xs">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Batch') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Expiry Date') }}</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ __('QTY') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($expiringSoonBatches ?? [] as $batch)
                                    <tr class="hover:bg-gray-50/80 transition">
                                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $batch->medicine->name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $batch->batch_number }}</td>
                                        <td class="px-4 py-3 text-rose-600 font-bold">{{ $batch->expiry_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $batch->quantity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-xs text-gray-400 text-center space-y-2">
                                            <div class="w-10 h-10 mx-auto rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p>{{ __('No expiring medicines') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let flowChart = null;
        let categoryChart = null;

        document.addEventListener('DOMContentLoaded', function () {
            // Weekly Flow Chart
            const ctxFlow = document.getElementById('weeklyFlowChart').getContext('2d');
            flowChart = new Chart(ctxFlow, {
                type: 'line',
                data: {
                    labels: @json($chartLabels ?? []),
                    datasets: [
                        {
                            label: 'Sales ({{ setting("currency_symbol", "UGX") }})',
                            data: @json($chartSales ?? []),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointRadius: 4
                        },
                        {
                            label: 'Purchases ({{ setting("currency_symbol", "UGX") }})',
                            data: @json($chartPurchases ?? []),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ setting("currency_symbol", "UGX") }} ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Top Categories Doughnut Chart
            const ctxCategory = document.getElementById('categoryDistributionChart').getContext('2d');
            categoryChart = new Chart(ctxCategory, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryLabels ?? []),
                    datasets: [{
                        data: @json($categoryCounts ?? []),
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(139, 92, 246)',
                            'rgb(107, 114, 128)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12 } }
                    },
                    cutout: '70%'
                }
            });
        });

        // ─── Software Update Check ───────────────────────────────────
        let pendingUpdateVersion = null;

        function checkForUpdates() {
            fetch('{{ route("update.check") }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.available && data.download_url) {
                    pendingUpdateVersion = data.version;
                    document.getElementById('update-version').textContent = 'v' + data.version;
                    document.getElementById('update-version-compare').textContent = 'v' + data.version;
                    document.getElementById('update-notes').textContent = data.release_notes || 'A new version is available with improvements and bug fixes.';
                    document.getElementById('update-date').textContent = data.release_date ? 'Released: ' + data.release_date : '';
                    document.getElementById('update-download-btn').href = data.download_url;

                    // Slide in the banner
                    const banner = document.getElementById('update-banner');
                    banner.classList.remove('hidden');
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateY(-10px)';
                    requestAnimationFrame(() => {
                        banner.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        banner.style.opacity = '1';
                        banner.style.transform = 'translateY(0)';
                    });
                }
            })
            .catch(() => {
                // Silently fail — offline-first behavior
            });
        }

        function remindLater() {
            const banner = document.getElementById('update-banner');
            banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-10px)';
            setTimeout(() => banner.classList.add('hidden'), 300);
        }

        function dismissUpdate() {
            if (!pendingUpdateVersion) return;
            const banner = document.getElementById('update-banner');
            banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-10px)';
            setTimeout(() => banner.classList.add('hidden'), 300);

            // Tell the server to suppress this version
            fetch('{{ route("update.dismiss") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ version: pendingUpdateVersion })
            }).catch(() => {});
        }

        // Check for updates 2 seconds after page load (non-blocking)
        setTimeout(checkForUpdates, 2000);
    </script>
</x-app-layout>
