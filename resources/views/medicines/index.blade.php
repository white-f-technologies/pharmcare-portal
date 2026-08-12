<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Medicines') }}</h2>
            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                <a href="{{ route('medicines.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Add Medicine') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @session('success')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">{{ $value }}</div>
            @endsession

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <form method="GET" action="{{ route('medicines.index') }}">
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full max-w-md" :value="request('search')" placeholder="{{ __('Search by name...') }}" />
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Stock Qty') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Selling Price') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Profit / Unit') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($medicines as $medicine)
                                    @php
                                        $batch = $medicine->batches->first();
                                        $sellPrice = $batch ? (float)$batch->selling_price : 0;
                                        $buyPrice = $batch ? (float)$batch->purchase_price : 0;
                                        $unitProfit = max(0, $sellPrice - $buyPrice);
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 flex items-center gap-3">
                                            <div class="w-10 h-10 shrink-0 bg-slate-50 border border-slate-200 rounded-xl p-1 flex items-center justify-center text-slate-400">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                            </div>
                                            <div>
                                                <a href="{{ route('medicines.show', $medicine) }}" class="text-gray-900 hover:text-emerald-600 font-bold block">{{ $medicine->name }}</a>
                                                <span class="text-xs text-gray-400 block">{{ $medicine->generic_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-semibold">{{ $medicine->category?->name ?? 'Uncategorized' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-extrabold text-gray-900">{{ number_format($medicine->batches_sum_quantity ?? 0) }} <span class="text-xs font-normal text-gray-500">{{ $medicine->base_unit ?? 'units' }}</span></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">{{ setting('currency_symbol', 'UGX') }} {{ format_price($sellPrice) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-extrabold text-emerald-600">+{{ setting('currency_symbol', 'UGX') }} {{ format_price($unitProfit) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium space-x-2">
                                            <a href="{{ route('medicines.show', $medicine) }}" class="text-emerald-600 hover:text-emerald-900 font-bold">{{ __('Details & Profit') }}</a>
                                            @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                                                <a href="{{ route('medicines.edit', $medicine) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">{{ __('Edit') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No medicines found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $medicines->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>