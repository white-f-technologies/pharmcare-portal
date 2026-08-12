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

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 md:p-8 text-gray-900">
                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        <!-- Section: System Logo -->
                        <div class="border-b border-gray-100 pb-8">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('Application Branding & Logo') }}</h3>
                            </div>
                            <p class="text-xs text-gray-500 mb-6">{{ __('Upload your custom pharmacy logo. Recommended format: PNG, SVG, or WEBP (Max 2MB).') }}</p>
                            
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                                <div class="shrink-0 flex items-center justify-center w-28 h-28 p-3 bg-gray-50 border border-gray-200 rounded-2xl shadow-sm">
                                    <x-application-logo class="w-full h-full text-emerald-600" />
                                </div>
                                <div class="space-y-3 flex-1 w-full">
                                    <div>
                                        <x-input-label for="app_logo" :value="__('Upload New Logo')" class="font-semibold text-sm" />
                                        <input id="app_logo" name="app_logo" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer transition" />
                                        <x-input-error class="mt-2" :messages="$errors->get('app_logo')" />
                                    </div>

                                    @if(isset($settings['app_logo']) && $settings['app_logo'] && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['app_logo']))
                                        <div class="pt-1">
                                            <button type="submit" form="remove-logo-form" class="inline-flex items-center text-xs text-red-600 hover:text-red-800 font-semibold transition">
                                                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                {{ __('Remove Custom Logo & Restore Default') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Section: General Settings -->
                        <div class="border-b border-gray-100 pb-8 space-y-6">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('General System Information') }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="app_name" :value="__('Application / Pharmacy Name')" class="font-semibold text-sm" />
                                    <x-text-input id="app_name" name="app_name" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('app_name', $settings['app_name'] ?? 'PharmCare')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('app_name')" />
                                </div>

                                <div>
                                    <x-input-label for="currency_symbol" :value="__('Currency Symbol / Code')" class="font-semibold text-sm" />
                                    <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base font-bold text-emerald-700" :value="old('currency_symbol', $settings['currency_symbol'] ?? '$')" required />
                                    <p class="text-xs text-gray-500 mt-1">{{ __('Example: $, €, £, UGX, KSh, NGN') }}</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('currency_symbol')" />
                                </div>
                            </div>
                        </div>

                        <!-- Section: Contact Details -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('Pharmacy Contact Information') }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="system_email" :value="__('System / Support Email')" class="font-semibold text-sm" />
                                    <x-text-input id="system_email" name="system_email" type="email" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('system_email', $settings['system_email'] ?? '')" placeholder="support@pharmcare.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('system_email')" />
                                </div>

                                <div>
                                    <x-input-label for="system_phone" :value="__('System Phone Number')" class="font-semibold text-sm" />
                                    <x-text-input id="system_phone" name="system_phone" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('system_phone', $settings['system_phone'] ?? '')" placeholder="+1 234 567 890" />
                                    <x-input-error class="mt-2" :messages="$errors->get('system_phone')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="system_address" :value="__('System / Store Physical Address')" class="font-semibold text-sm" />
                                <textarea id="system_address" name="system_address" rows="3" class="mt-1.5 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm py-2.5 px-4 text-base focus:outline-none transition" placeholder="123 Main Street, City, Country">{{ old('system_address', $settings['system_address'] ?? '') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('system_address')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-6 border-t border-gray-100 gap-3">
                            <x-primary-button class="py-3 px-6 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700">
                                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                {{ __('Save System Settings') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Form for logo removal -->
                    <form id="remove-logo-form" method="POST" action="{{ route('settings.logo.remove') }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
