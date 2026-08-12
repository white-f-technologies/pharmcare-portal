<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('expenses.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-gray-700 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ __('Expense Categories') }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Manage expense categories (Rent, Utilities, Food, Transport, Salaries, etc.)') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Create Category Form -->
                <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 p-6">
                    <h3 class="font-extrabold text-gray-900 text-base mb-4">{{ __('Add New Category') }}</h3>
                    <form action="{{ route('expense-categories.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">{{ __('Category Name') }} *</label>
                            <input type="text" name="name" required placeholder="e.g. Rent, Water & Electricity, Food" class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">{{ __('Description') }}</label>
                            <textarea name="description" rows="2" placeholder="Brief description of this expense category..." class="w-full text-xs rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                            {{ __('Save Category') }}
                        </button>
                    </form>
                </div>

                <!-- Categories List Table -->
                <div class="md:col-span-2 bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="font-extrabold text-gray-900 text-base">{{ __('Category Directory') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-slate-50 text-gray-500 font-bold uppercase">
                                <tr>
                                    <th class="px-6 py-3.5 text-left">{{ __('Category Name') }}</th>
                                    <th class="px-6 py-3.5 text-left">{{ __('Description') }}</th>
                                    <th class="px-6 py-3.5 text-center">{{ __('Expenses Count') }}</th>
                                    <th class="px-6 py-3.5 text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($categories as $cat)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 font-extrabold text-gray-900">{{ $cat->name }}</td>
                                        <td class="px-6 py-4 text-gray-500">{{ $cat->description ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $cat->expenses_count }}</td>
                                        <td class="px-6 py-4 text-center">
                                            @if($cat->expenses_count == 0)
                                                <form action="{{ route('expense-categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete category?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold">{{ __('Delete') }}</button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 font-semibold">{{ __('In Use') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">{{ __('No expense categories found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
