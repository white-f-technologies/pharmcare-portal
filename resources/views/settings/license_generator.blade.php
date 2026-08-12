<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center font-bold shadow-md">
                    🔑
                </div>
                <div>
                    <h2 class="font-extrabold text-xl text-gray-900 leading-tight">{{ __('Offline License Key Generator') }}</h2>
                    <p class="text-xs text-gray-500">{{ __('Vendor cryptographic RSA-SHA256 signature generator for software licensing') }}</p>
                </div>
            </div>
            <a href="{{ route('settings.license') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition border border-slate-300">
                &larr; {{ __('Back to License & Edition') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ licenseType: 'PERPETUAL', edition: 'PREMIUM', action: 'json_view' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert Banner -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <p class="font-bold text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Alert Banner -->
            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-bold text-sm">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(!$hasPrivateKey)
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-900 rounded-xl shadow-sm space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔒</span>
                        <p class="font-extrabold text-sm">{{ __('Client Installation Security Notice: Key Generation Disabled') }}</p>
                    </div>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        {{ __('This client installation contains only the verification Public Key. Generating digitally signed licenses requires the Vendor Private Key (keys/private.key), which is securely held on your developer machine to prevent buyers from self-upgrading.') }}
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Column: Generator Form -->
                <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-gray-200/80 p-6 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h3 class="font-extrabold text-gray-900 text-lg flex items-center gap-2">
                            <span>⚡ Generate Signed License Key</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Fill in client details to issue a digitally signed, tamper-proof license file.') }}</p>
                    </div>

                    <form action="{{ route('settings.license.generate') }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="hidden" name="action" :value="action">

                        <!-- Business Name -->
                        <div>
                            <x-input-label for="business_name" :value="__('Pharmacy / Client Name')" class="font-bold text-xs uppercase text-gray-700" />
                            <x-text-input id="business_name" name="business_name" type="text" class="mt-1 block w-full text-sm font-semibold rounded-xl" placeholder="e.g. City Care Pharmacy Ltd" value="{{ old('business_name', 'PharmCare Drug Shop') }}" required />
                        </div>

                        <!-- Business ID / Client Reference -->
                        <div>
                            <x-input-label for="business_id" :value="__('Client Reference / Business ID')" class="font-bold text-xs uppercase text-gray-700" />
                            <x-text-input id="business_id" name="business_id" type="text" class="mt-1 block w-full text-sm font-mono rounded-xl" placeholder="e.g. CC-UG-2026" value="{{ old('business_id', 'PHARM-UG-' . date('Y')) }}" />
                        </div>

                        <!-- Edition Selector -->
                        <div>
                            <label class="font-bold text-xs uppercase text-gray-700 block mb-2">{{ __('Target Software Edition') }}</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label @click="edition = 'PREMIUM'" :class="edition === 'PREMIUM' ? 'bg-amber-50 border-amber-500 ring-2 ring-amber-500/20' : 'bg-slate-50 border-gray-200'" class="p-3.5 border rounded-xl cursor-pointer transition flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="edition" value="PREMIUM" x-model="edition" class="text-amber-600 focus:ring-amber-500">
                                        <div>
                                            <span class="font-extrabold text-sm text-gray-900 block">{{ __('PRO EDITION') }}</span>
                                            <span class="text-[11px] text-amber-700 font-semibold">{{ __('All Features Unlocked') }}</span>
                                        </div>
                                    </div>
                                    <span class="text-amber-500 text-lg">⭐</span>
                                </label>

                                <label @click="edition = 'DEFAULT'" :class="edition === 'DEFAULT' ? 'bg-slate-100 border-slate-400 ring-2 ring-slate-400/20' : 'bg-slate-50 border-gray-200'" class="p-3.5 border rounded-xl cursor-pointer transition flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="edition" value="DEFAULT" x-model="edition" class="text-slate-600 focus:ring-slate-500">
                                        <div>
                                            <span class="font-extrabold text-sm text-gray-900 block">{{ __('STANDARD') }}</span>
                                            <span class="text-[11px] text-gray-500">{{ __('Basic Features Only') }}</span>
                                        </div>
                                    </div>
                                    <span class="text-gray-400 text-lg">📦</span>
                                </label>
                            </div>
                        </div>

                        <!-- License Type -->
                        <div>
                            <label class="font-bold text-xs uppercase text-gray-700 block mb-2">{{ __('License Term & Duration') }}</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label @click="licenseType = 'PERPETUAL'" :class="licenseType === 'PERPETUAL' ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-slate-50 border-gray-200'" class="p-3.5 border rounded-xl cursor-pointer transition flex items-center gap-2.5">
                                    <input type="radio" name="license_type" value="PERPETUAL" x-model="licenseType" class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block">{{ __('PERPETUAL') }}</span>
                                        <span class="text-[11px] text-emerald-700 font-semibold">{{ __('Lifetime / No Expiry') }}</span>
                                    </div>
                                </label>

                                <label @click="licenseType = 'SUBSCRIPTION'" :class="licenseType === 'SUBSCRIPTION' ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500/20' : 'bg-slate-50 border-gray-200'" class="p-3.5 border rounded-xl cursor-pointer transition flex items-center gap-2.5">
                                    <input type="radio" name="license_type" value="SUBSCRIPTION" x-model="licenseType" class="text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="font-extrabold text-sm text-gray-900 block">{{ __('SUBSCRIPTION') }}</span>
                                        <span class="text-[11px] text-blue-700 font-semibold">{{ __('Time-Bound Expiry') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Expiry Days Options (visible only if Subscription) -->
                        <div x-show="licenseType === 'SUBSCRIPTION'" x-transition class="p-4 bg-blue-50/50 border border-blue-100 rounded-xl space-y-3">
                            <x-input-label for="expiry_days" :value="__('Subscription Duration (Days)')" class="font-bold text-xs uppercase text-blue-900" />
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" onclick="document.getElementById('expiry_days').value = 30" class="py-1.5 bg-white hover:bg-blue-100 text-blue-800 text-xs font-bold rounded-lg border border-blue-200 shadow-sm transition">30 Days</button>
                                <button type="button" onclick="document.getElementById('expiry_days').value = 90" class="py-1.5 bg-white hover:bg-blue-100 text-blue-800 text-xs font-bold rounded-lg border border-blue-200 shadow-sm transition">90 Days</button>
                                <button type="button" onclick="document.getElementById('expiry_days').value = 365" class="py-1.5 bg-white hover:bg-blue-100 text-blue-800 text-xs font-bold rounded-lg border border-blue-200 shadow-sm transition">1 Year (365d)</button>
                                <button type="button" onclick="document.getElementById('expiry_days').value = 1095" class="py-1.5 bg-white hover:bg-blue-100 text-blue-800 text-xs font-bold rounded-lg border border-blue-200 shadow-sm transition">3 Years</button>
                            </div>
                            <x-text-input id="expiry_days" name="expiry_days" type="number" min="1" class="mt-2 block w-full text-sm font-semibold rounded-xl" placeholder="Enter number of days (e.g. 365)" value="365" />
                        </div>

                        <!-- Submit Action Buttons -->
                        <div class="pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                            <button type="submit" @click="action = 'download'" class="flex-1 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                                📥 {{ __('Generate & Download .JSON') }}
                            </button>

                            <button type="submit" @click="action = 'activate_now'" class="flex-1 py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs transition shadow-sm flex items-center justify-center gap-2">
                                ⚡ {{ __('Generate & Activate Now') }}
                            </button>

                            <button type="submit" @click="action = 'json_view'" class="py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs transition shadow-sm flex items-center justify-center gap-1.5">
                                📋 {{ __('Generate Payload') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Result Output & Public Key -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Generated Payload View -->
                    <div class="bg-slate-900 text-slate-100 rounded-2xl shadow-md p-6 space-y-4 border border-slate-800">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h4 class="font-extrabold text-sm text-emerald-400 flex items-center gap-2">
                                <span>📄 RSA Signed License Payload</span>
                            </h4>
                            @if(session('generated_payload'))
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('payload-box').innerText); alert('License Payload copied to clipboard!');" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-bold transition">
                                    📋 Copy JSON
                                </button>
                            @endif
                        </div>

                        @if(session('generated_payload'))
                            <div class="overflow-x-auto max-h-96 text-xs font-mono bg-slate-950 p-4 rounded-xl border border-slate-800 text-emerald-300 leading-relaxed" id="payload-box">
{{ session('generated_payload') }}
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>Status: <strong class="text-emerald-400">Digitally Signed (SHA-256)</strong></span>
                                <a href="{{ route('settings.license') }}" class="text-amber-400 hover:underline font-bold">Paste in License Settings &rarr;</a>
                            </div>
                        @else
                            <div class="p-8 text-center text-xs text-slate-500 space-y-2">
                                <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-xl">
                                    🔐
                                </div>
                                <p>{{ __('Click "Generate Payload" or "Generate & Download" to produce a cryptographically signed license JSON.') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- RSA Key Information Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/80 p-5 space-y-3">
                        <h4 class="font-bold text-xs uppercase text-gray-700 tracking-wider flex items-center justify-between">
                            <span>🔑 System Verification Key</span>
                            <span class="text-[10px] text-emerald-700 font-extrabold bg-emerald-100 px-2 py-0.5 rounded-full">RSA 2048-BIT</span>
                        </h4>
                        <p class="text-xs text-gray-500">{{ __('Below is the active RSA Public Key used by this installation to verify offline signatures:') }}</p>
                        <div class="p-3 bg-slate-50 text-[10px] font-mono text-slate-700 rounded-xl border border-slate-200 overflow-x-auto max-h-32">
                            {{ $publicKeyPem ?? 'No key pair loaded' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
