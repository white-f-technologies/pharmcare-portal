<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2 bg-white border border-gray-200 rounded-xl text-gray-600 hover:text-gray-900 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Create New User') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('Add a new staff member and configure their access role.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                    @csrf

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Maria Santos" class="w-full rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Email Address') }} *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="e.g. maria@pharmcare.test" class="w-full rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="e.g. 09171234567" class="w-full rounded-xl border border-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @error('phone')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Assign Role') }} *</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative flex flex-col p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="admin" {{ old('role') === 'admin' ? 'checked' : '' }} class="sr-only">
                                <span class="font-bold text-sm text-gray-900 flex items-center gap-1.5 mb-1">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ __('Admin') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ __('Full control over all system settings, user management, reports & data.') }}</span>
                            </label>

                            <label class="relative flex flex-col p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="pharmacist" {{ old('role', 'pharmacist') === 'pharmacist' ? 'checked' : '' }} class="sr-only">
                                <span class="font-bold text-sm text-gray-900 flex items-center gap-1.5 mb-1">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    {{ __('Pharmacist') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ __('Manages inventory, purchases, prescriptions, batches, and reports.') }}</span>
                            </label>

                            <label class="relative flex flex-col p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-emerald-500 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="cashier" {{ old('role') === 'cashier' ? 'checked' : '' }} class="sr-only">
                                <span class="font-bold text-sm text-gray-900 flex items-center gap-1.5 mb-1">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    {{ __('Cashier') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ __('Processes sales at POS, views history, and registers customers.') }}</span>
                            </label>
                        </div>
                        @error('role')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Password') }} *</label>
                            <x-password-input name="password" id="password" required />
                            @error('password')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('Confirm Password') }} *</label>
                            <x-password-input name="password_confirmation" id="password_confirmation" required />
                        </div>
                    </div>

                    <!-- Account Active Status -->
                    <div class="pt-2 border-t border-gray-100">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 w-4 h-4">
                            <span class="ms-2 text-sm font-semibold text-gray-700">{{ __('Activate user account immediately') }}</span>
                        </label>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                            {{ __('Save User Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
