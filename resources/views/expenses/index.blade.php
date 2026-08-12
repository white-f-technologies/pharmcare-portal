<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Pharmacy Expenses') }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Track operating expenses: rent, utilities, food, transport, salaries, maintenance & repairs') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('expense-categories.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10"/></svg>
                    {{ __('Categories') }}
                </a>
                <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Record Expense') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Expense Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-rose-50 text-rose-600 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Today\'s Expenses') }}</span>
                        <span class="text-xl font-extrabold text-rose-600">{{ setting('currency_symbol', 'UGX') }} {{ format_price($todayTotalExpenses) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Deducted from today\'s net profit') }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('This Month\'s Expenses') }}</span>
                        <span class="text-xl font-extrabold text-amber-600">{{ setting('currency_symbol', 'UGX') }} {{ format_price($monthTotalExpenses) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('Accumulated monthly expenses') }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-sky-50 text-sky-600 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">{{ __('Total Expense Records') }}</span>
                        <span class="text-xl font-extrabold text-gray-900">{{ number_format($expenses->total()) }}</span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">{{ __('All expense entries') }}</span>
                    </div>
                </div>
            </div>

            <!-- Filters Form -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search expense title or #...') }}" class="w-full text-xs rounded-xl border-gray-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <select name="category_id" class="w-full text-xs rounded-xl border-gray-200 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full text-xs rounded-xl border-gray-200 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full text-xs rounded-xl border-gray-200 focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white font-bold text-xs rounded-xl hover:bg-gray-900 transition">{{ __('Filter') }}</button>
                    </div>
                </form>
            </div>

            <!-- Expenses Table -->
            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-slate-50 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">{{ __('Expense #') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Title & Notes') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Category') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Amount') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Payment Method') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Date') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Recorded By') }}</th>
                                <th class="px-6 py-3.5 text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($expenses as $exp)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-bold font-mono text-gray-900">{{ $exp->expense_number }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-extrabold text-gray-900 block">{{ $exp->title }}</span>
                                        @if($exp->reference_no)
                                            <span class="text-[11px] text-gray-500 block">Ref: {{ $exp->reference_no }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-800">
                                            {{ $exp->category?->name ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-rose-600 text-sm">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($exp->amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase rounded-full bg-emerald-50 text-emerald-700">
                                            {{ str_replace('_', ' ', $exp->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 font-semibold whitespace-nowrap">
                                        {{ $exp->expense_date->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-medium">
                                        {{ $exp->user?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-center space-x-2 whitespace-nowrap">
                                        <a href="{{ route('expenses.edit', $exp) }}" class="text-emerald-600 hover:text-emerald-900 font-bold">{{ __('Edit') }}</a>
                                        <form action="{{ route('expenses.destroy', $exp) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this expense?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                                        {{ __('No expense entries found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $expenses->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
