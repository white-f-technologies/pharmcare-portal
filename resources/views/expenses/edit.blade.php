<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('expenses.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Edit Expense') }} - {{ $expense->expense_number }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Update recorded expense information') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
            <!-- Available Profit Banner -->
            <div class="mb-6 p-4 rounded-2xl border {{ $availableProfit > 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }} flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl {{ $availableProfit > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider block">{{ __('Available Net Sales Profit For Date') }}</span>
                        <span class="text-lg font-extrabold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($availableProfit) }}</span>
                    </div>
                </div>
                @if($availableProfit <= 0)
                    <span class="px-3 py-1 bg-rose-200 text-rose-900 font-extrabold text-xs rounded-xl">{{ __('No Profit Available') }}</span>
                @else
                    <span class="px-3 py-1 bg-emerald-200 text-emerald-900 font-extrabold text-xs rounded-xl">{{ __('Expenses Allowed Up To UGX ' . format_price($availableProfit)) }}</span>
                @endif
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6 md:p-8">
                <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Expense Category') }} *</label>
                            <select name="expense_category_id" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Expense Title / Reason') }} *</label>
                            <input type="text" name="title" value="{{ old('title', $expense->title) }}" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Amount (' . setting('currency_symbol', 'UGX') . ')') }} *</label>
                            <input type="number" step="1" name="amount" value="{{ old('amount', (int)$expense->amount) }}" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 font-extrabold text-gray-900">
                            @error('amount') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Payment Method') }} *</label>
                            <select name="payment_method" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                <option value="mobile_money" {{ old('payment_method', $expense->payment_method) == 'mobile_money' ? 'selected' : '' }}>{{ __('Mobile Money') }}</option>
                                <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) == 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                            </select>
                            @error('payment_method') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Expense Date -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Expense Date') }} *</label>
                            <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('expense_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Reference No -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Receipt / Voucher #') }}</label>
                            <input type="text" name="reference_no" value="{{ old('reference_no', $expense->reference_no) }}" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('reference_no') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Notes / Additional Details') }}</label>
                        <textarea name="notes" rows="3" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Attachment Upload -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">{{ __('Update Receipt / Voucher Attachment') }}</label>
                        <input type="file" name="attachment" accept="image/*,application/pdf" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @if($expense->attachment)
                            <p class="text-[11px] text-emerald-600 font-bold mt-1">{{ __('Current attachment uploaded') }}</p>
                        @endif
                        @error('attachment') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                            {{ __('Update Expense') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
