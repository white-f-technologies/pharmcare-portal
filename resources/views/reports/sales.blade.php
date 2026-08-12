<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sales Report') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.sales') }}" class="flex gap-4 items-end">
                        <div>
                            <x-input-label for="from" :value="__('From Date')" />
                            <x-text-input id="from" name="from" type="date" class="mt-1 block" :value="request('from', now()->startOfMonth()->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-input-label for="to" :value="__('To Date')" />
                            <x-text-input id="to" name="to" type="date" class="mt-1 block" :value="request('to', now()->format('Y-m-d'))" />
                        </div>
                        <div>
                            <x-primary-button>{{ __('Filter') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Total Revenue') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($totalRevenue ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Total Transactions') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalTransactions ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">{{ __('Average per Transaction') }}</p>
                    <p class="text-2xl font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($averagePerTransaction ?? 0) }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Invoice #') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Customer') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Payment Method') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($sales as $sale)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $sale->invoice_no }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $sale->customer?->name ?? __('Walk-in') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->total) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($sale->payment_method) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No sales found for selected period') }}</td>
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