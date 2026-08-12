<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expiry Report') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.expiry') }}" class="flex gap-4 items-end">
                        <div>
                            <x-input-label for="from" :value="__('From Date')" />
                            <x-text-input id="from" name="from" type="date" class="mt-1 block" :value="request('from', now()->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="to" :value="__('To Date')" />
                            <x-text-input id="to" name="to" type="date" class="mt-1 block" :value="request('to', now()->addMonths(3)->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Expiring Batches') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalBatches ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Total Quantity Expiring') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalQuantity ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Total Value at Risk') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ number_format($totalValue ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Batch #') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Supplier') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Expiry Date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Days Left') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($batches as $batch)
                                    @php
                                        $daysLeft = now()->diffInDays($batch->expiry_date, false);
                                        $isExpired = $daysLeft < 0;
                                    @endphp
                                    <tr class="{{ $isExpired ? 'bg-red-50' : ($daysLeft <= 30 ? 'bg-yellow-50' : '') }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $batch->medicine->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $batch->batch_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $batch->supplier?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $isExpired ? 'text-red-600 font-bold' : 'text-gray-600' }}">{{ $batch->expiry_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $batch->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $isExpired ? 'text-red-600 font-bold' : ($daysLeft <= 30 ? 'text-yellow-600 font-medium' : 'text-gray-600') }}">
                                            @if($isExpired)
                                                {{ __('Expired') }}
                                            @else
                                                {{ $daysLeft }} {{ __('days') }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No batches expiring in the selected period') }}</td>
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