<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PharmCare Setup & Activation</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
    <div class="min-h-screen flex flex-col items-center justify-center p-4" x-data="setupWizard()">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-100 rounded-2xl mb-4 shadow-sm">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">PharmCare Setup & Activation</h1>
            <p class="text-gray-500 mt-1 text-sm">Follow the wizard to activate your license and configure your pharmacy</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center gap-2 mb-8">
            <template x-for="(label, i) in ['License Activation', 'Business Info', 'Admin Account']" :key="i">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold transition-colors"
                         :class="step > i + 1 ? 'bg-emerald-600 text-white' : step === i + 1 ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'">
                        <template x-if="step > i + 1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="step <= i + 1">
                            <span x-text="i + 1"></span>
                        </template>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold" :class="step === i + 1 ? 'text-emerald-600' : 'text-gray-400'" x-text="label"></span>
                    <template x-if="i < 2">
                        <div class="w-8 sm:w-12 h-0.5 bg-gray-200 mx-1"></div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Error Display -->
        @if ($errors->any())
            <div class="w-full max-w-xl mb-6 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" class="w-full max-w-xl">
            @csrf

            <!-- Step 1: License Activation -->
            <div x-show="step === 1" x-transition class="bg-white shadow-lg border border-gray-200/80 rounded-2xl p-8 space-y-6">
                <div>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[10px] font-extrabold uppercase rounded-full tracking-wider">Step 1 of 3</span>
                    <h2 class="text-xl font-bold text-gray-800 mt-2">Software License Activation</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Import your digitally signed vendor license or activate later</p>
                </div>

                @if($activeLicense)
                    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl text-emerald-800 text-xs font-semibold flex items-center gap-3">
                        <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <div>
                            <p class="font-bold text-sm text-emerald-950">Active License Detected: {{ $activeLicense->edition }} EDITION</p>
                            <p class="text-emerald-700">License Key: {{ $activeLicense->license_key }}</p>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Upload Signed License File (.json)</label>
                        <input type="file" name="license_file" accept=".json,.txt"
                               class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Or Paste License JSON Payload</label>
                        <textarea name="license_json" rows="3"
                                  class="w-full text-xs font-mono border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 placeholder-gray-400"
                                  placeholder='{"license_id": "PHC-LIC-...", "signature": "..."}'>{{ old('license_json') }}</textarea>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 flex items-center justify-between">
                    <span>Don't have your license file right now?</span>
                    <span class="font-bold text-slate-700">Will start in DEFAULT mode</span>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" @click="step = 2"
                            class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow">
                        Next: Pharmacy Details
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Step 2: Business Information -->
            <div x-show="step === 2" x-transition class="bg-white shadow-lg border border-gray-200/80 rounded-2xl p-8 space-y-6">
                <div>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase rounded-full tracking-wider">Step 2 of 3</span>
                    <h2 class="text-xl font-bold text-gray-800 mt-2">Pharmacy Profile</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Tell us about your pharmacy business</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pharmacy / Business Name *</label>
                    <input type="text" name="business_name" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                           placeholder="e.g. City Care Pharmacy"
                           value="{{ old('business_name', 'PharmCare Drug Shop') }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Currency Symbol / Code *</label>
                    <input type="text" name="currency_symbol" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4 font-bold text-emerald-700"
                           placeholder="e.g. UGX, $, KSh, NGN"
                           value="{{ old('currency_symbol', 'UGX') }}">
                    <p class="text-xs text-gray-400 mt-1">Currency used for sales receipts and inventory pricing</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Contact Email</label>
                        <input type="email" name="business_email"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                               placeholder="info@yourpharmacy.com"
                               value="{{ old('business_email') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="business_phone"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                               placeholder="+256 700 000 000"
                               value="{{ old('business_phone') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Physical Address</label>
                    <textarea name="business_address" rows="2"
                              class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                              placeholder="Plot 12, Main Street, Kampala">{{ old('business_address') }}</textarea>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" @click="step = 1"
                            class="inline-flex items-center px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                    <button type="button" @click="step = 3"
                            class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow">
                        Next: Admin Account
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Step 3: Admin Account -->
            <div x-show="step === 3" x-transition class="bg-white shadow-lg border border-gray-200/80 rounded-2xl p-8 space-y-6">
                <div>
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-[10px] font-extrabold uppercase rounded-full tracking-wider">Step 3 of 3</span>
                    <h2 class="text-xl font-bold text-gray-800 mt-2">Create Admin Account</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Set up your administrator login credentials</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Administrator Full Name *</label>
                    <input type="text" name="admin_name" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                           placeholder="e.g. John Manager"
                           value="{{ old('admin_name') }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Admin Email *</label>
                    <input type="email" name="admin_email" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                           placeholder="admin@yourpharmacy.com"
                           value="{{ old('admin_email') }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password *</label>
                    <input type="password" name="admin_password" required minlength="6"
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                           placeholder="Minimum 6 characters">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password *</label>
                    <input type="password" name="admin_password_confirmation" required minlength="6"
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-base py-2.5 px-4"
                           placeholder="Re-enter password">
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" @click="step = 2"
                            class="inline-flex items-center px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-sm rounded-xl transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Initialize & Launch PharmCare
                    </button>
                </div>
            </div>
        </form>

        <p class="text-xs text-gray-400 mt-8 text-center">PharmCare v{{ config('license.version', '2.1.0') }} &mdash; Offline Pharmacy Management System</p>
    </div>

    <script>
        function setupWizard() {
            return {
                step: 1,
            }
        }
    </script>
</body>
</html>
