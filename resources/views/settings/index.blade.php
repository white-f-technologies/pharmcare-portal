<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-100 text-emerald-800 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('System Settings') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Configure pharmacy branding, currency, and business contact information.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 md:p-8">
                <div class="max-w-3xl">
                    <section>
                        <header class="border-b border-gray-100 pb-4 mb-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ __('General Application Settings') }}
                                </h3>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Update your pharmacy brand identity, logo, currency settings, and contact information.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <!-- System Name -->
                            <div>
                                <x-input-label for="system_name" :value="__('System / Pharmacy Name')" class="font-semibold text-sm" />
                                <x-text-input id="system_name" name="system_name" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('system_name', $settingsMap['system_name'] ?? $systemSettings['system_name'] ?? 'PharmCare')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('system_name')" />
                            </div>

                            <!-- System Logo -->
                            <div>
                                <x-input-label for="system_logo" :value="__('Application Logo')" class="font-semibold text-sm" />
                                <div class="mt-2 flex items-center gap-4">
                                    <div class="w-24 h-24 border rounded-2xl overflow-hidden bg-gray-50 flex items-center justify-center p-3 shadow-sm">
                                        @if(!empty($systemSettings['system_logo']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($systemSettings['system_logo']))
                                            <img src="{{ asset('media/' . $systemSettings['system_logo']) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                        @else
                                            <x-application-logo class="w-12 h-12 text-emerald-600" />
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <input id="system_logo" name="system_logo" type="file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer transition" />
                                        <p class="mt-1.5 text-xs text-gray-500">{{ __('PNG, JPG, SVG or WEBP (Max 2MB). Recommended square or landscape logo.') }}</p>
                                    </div>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('system_logo')" />
                            </div>

                            <!-- Currency Symbol -->
                            <div>
                                <x-input-label for="currency" :value="__('Default Currency Symbol / Code')" class="font-semibold text-sm" />
                                <div class="mt-1.5">
                                    <x-text-input id="currency" name="currency" type="text" class="block w-full py-2.5 px-4 rounded-xl text-base font-bold text-emerald-700" :value="old('currency', $settingsMap['currency'] ?? $systemSettings['currency'] ?? '$')" required placeholder="e.g. $, €, £, UGX, KSh" />
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500">{{ __('This symbol will be automatically displayed across all pricing tables, invoices, and reports.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('currency')" />
                            </div>

                            <!-- Contact Phone -->
                            <div>
                                <x-input-label for="contact_phone" :value="__('Contact Phone Number')" class="font-semibold text-sm" />
                                <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('contact_phone', $settingsMap['contact_phone'] ?? '')" placeholder="+1 234 567 890" />
                                <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
                            </div>

                            <!-- Contact Email -->
                            <div>
                                <x-input-label for="contact_email" :value="__('Support / Business Email')" class="font-semibold text-sm" />
                                <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('contact_email', $settingsMap['contact_email'] ?? '')" placeholder="support@pharmcare.com" />
                                <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
                            </div>

                            <!-- Business Address -->
                            <div>
                                <x-input-label for="address" :value="__('Physical Address')" class="font-semibold text-sm" />
                                <textarea id="address" name="address" rows="2" class="mt-1.5 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm py-2.5 px-4 text-sm focus:outline-none transition" placeholder="123 Main Street, City, Country">{{ old('address', $settingsMap['address'] ?? '') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>

                            <!-- Tax / TIN Number -->
                            <div>
                                <x-input-label for="tax_number" :value="__('Tax ID / TIN Number')" class="font-semibold text-sm" />
                                <x-text-input id="tax_number" name="tax_number" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-sm" :value="old('tax_number', $settingsMap['tax_number'] ?? '')" placeholder="e.g. TIN-12345678" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Printed on official receipts and invoices for tax compliance.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('tax_number')" />
                            </div>

                            <!-- Receipt Footer Note -->
                            <div>
                                <x-input-label for="receipt_footer" :value="__('Receipt Footer Message')" class="font-semibold text-sm" />
                                <textarea id="receipt_footer" name="receipt_footer" rows="2" class="mt-1.5 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm py-2.5 px-4 text-sm focus:outline-none transition" placeholder="Thank you for shopping with us! Get well soon.">{{ old('receipt_footer', $settingsMap['receipt_footer'] ?? 'Thank you for shopping with us! Get well soon.') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('receipt_footer')" />
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <x-primary-button class="py-3 px-6 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700">
                                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    {{ __('Save Settings') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
