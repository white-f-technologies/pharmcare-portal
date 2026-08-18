<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                    {{ __('License & System Edition') }}
                </h2>
                <p class="text-xs text-gray-500">{{ __('View active software license tier, modules, and activation tools') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- License Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-slate-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active System Status</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase {{ $isPremium ? 'bg-amber-400 text-amber-950' : 'bg-slate-700 text-slate-200' }}">
                                {{ $edition }} EDITION
                            </span>
                        </div>
                        <h1 class="text-2xl font-black mt-1 tracking-tight">
                            {{ $activeLicense->business_name ?? setting('app_name', 'PharmCare Drug Shop') }}
                        </h1>
                        <p class="text-xs text-slate-400 mt-1">
                            License ID: <span class="font-mono text-slate-200">{{ $activeLicense->license_key ?? 'DEFAULT-COMMERCIAL-UNLICENSED' }}</span>
                        </p>
                    </div>

                    <div class="bg-slate-800 border border-slate-700 px-4 py-3 rounded-xl text-right min-w-[200px]">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase">License Validity</span>
                        <div class="text-sm font-bold text-emerald-400 mt-0.5">
                            @if(!$activeLicense || !$activeLicense->expiry_date)
                                PERPETUAL (NO EXPIRY)
                            @else
                                Valid until {{ $activeLicense->expiry_date->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">Activated Feature Modules</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @php
                            $modules = [
                                'pos' => ['name' => 'Point of Sale (POS)', 'desc' => 'Cashier checkout & receipts'],
                                'medicines' => ['name' => 'Medicine Catalogue', 'desc' => 'Medicine structure & pricing'],
                                'unit_packaging' => ['name' => 'Flexible Packaging', 'desc' => 'Box / Strip / Tablet conversions'],
                                'stock_ledger' => ['name' => 'Stock Audit Ledger', 'desc' => 'Full movement audit history'],
                                'medicine_images' => ['name' => 'Medicine Visuals', 'desc' => 'Local image upload & POS cards'],
                                'inventory' => ['name' => 'Batch Inventory', 'desc' => 'Expiry & batch tracking'],
                                'purchases' => ['name' => 'Supplier Receiving', 'desc' => 'Purchasing & invoices'],
                                'customers' => ['name' => 'Customer Credit', 'desc' => 'Customer profiles & debt'],
                                'reports' => ['name' => 'Sales Reports', 'desc' => 'Revenue & inventory analysis'],
                                'backup' => ['name' => 'ZIP Backup & Restore', 'desc' => 'Offline database & image backups'],
                                'prescriptions' => ['name' => 'Prescription Management', 'desc' => 'Doctor & patient prescriptions'],
                                'medicine_images' => ['name' => 'Medicine Images', 'desc' => 'Photo uploads for medicine catalog'],
                                'advanced_reports' => ['name' => 'Sales Report Excel Export', 'desc' => 'Download sales data as styled Excel spreadsheet', 'prem' => true],
                                'advanced_inventory' => ['name' => 'Inventory Report Excel Export', 'desc' => 'Download stock valuation as styled Excel spreadsheet', 'prem' => true],
                                'stock_ledger' => ['name' => 'Stock Ledger Audit Trail', 'desc' => 'Full item-by-item stock movement log', 'prem' => true],
                            ];
                        @endphp

                        @foreach($modules as $key => $mod)
                            @php
                                $enabled = feature_enabled($key);
                            @endphp
                            <div class="p-3.5 rounded-xl border {{ $enabled ? 'bg-emerald-50/50 border-emerald-200/80 text-emerald-950' : 'bg-gray-50 border-gray-200 opacity-60' }} transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold">{{ $mod['name'] }}</span>
                                    @if($enabled)
                                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @else
                                        <span class="text-[10px] bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded font-semibold">LOCKED</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1 leading-snug">{{ $mod['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Offline Activation Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Import Signed Offline License</span>
                </h3>
                <p class="text-xs text-gray-500 mt-1">Upload a vendor-provided digitally signed license file (<code class="bg-gray-100 px-1 py-0.5 rounded">.json</code>) or paste the payload below to activate system modules without an internet connection.</p>

                <form action="{{ route('settings.license.activate') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Upload License File (.json)</label>
                            <input type="file" name="license_file" accept=".json,.txt" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Or Paste License JSON Payload</label>
                            <textarea name="license_json" rows="3" class="w-full text-xs font-mono border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 placeholder-gray-400" placeholder='{"license_id": "PC-UG-...", "signature": "..."}'></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Verify & Activate License</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
