<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-600 hover:text-gray-900 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Create Customer Account') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Register a new pharmacy customer and set their active status.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <form action="{{ route('customers.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Customer Full Name')" class="font-semibold text-sm" />
                            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('name')" required autofocus placeholder="e.g. John Doe" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('Phone Number')" class="font-semibold text-sm" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('phone')" required placeholder="e.g. +256 700 000 000" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="email" :value="__('Email Address (Optional)')" class="font-semibold text-sm" />
                            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full py-2.5 px-4 rounded-xl text-base" :value="old('email')" placeholder="customer@example.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Status Selection (Active / Inactive) -->
                        <div>
                            <x-input-label :value="__('Account Status')" class="font-semibold text-sm mb-2" />
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative flex items-center p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 transition">
                                    <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                    <div class="ms-3 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span class="font-bold text-sm text-gray-900">{{ __('Active') }}</span>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:border-red-500 has-[:checked]:border-red-600 has-[:checked]:bg-red-50/50 transition">
                                    <input type="radio" name="is_active" value="0" {{ old('is_active') === '0' ? 'checked' : '' }} class="w-4 h-4 text-red-600 focus:ring-red-500 border-gray-300">
                                    <div class="ms-3 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                                        <span class="font-bold text-sm text-gray-900">{{ __('Inactive') }}</span>
                                    </div>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('Physical Address (Optional)')" class="font-semibold text-sm" />
                        <textarea id="address" name="address" rows="3" class="mt-1.5 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl shadow-sm py-2.5 px-4 text-base focus:outline-none transition" placeholder="e.g. 123 Main Street, City">{{ old('address') }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button class="py-3 px-6 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700">
                            {{ __('Save Customer') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>