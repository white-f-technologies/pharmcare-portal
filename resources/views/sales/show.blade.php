<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sale') }} - {{ $sale->invoice_no }}</h2>
            <div class="space-x-2">
                @if(Auth::user()->isAdmin() || Auth::user()->isPharmacist())
                    <a href="{{ route('sales.returns.create', $sale) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">{{ __('Process Return') }}</a>
                @endif
                <a href="{{ route('sales.invoice', $sale) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Print Invoice') }}</a>
                <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Invoice #') }}</h4>
                            <p class="mt-1 text-sm text-gray-800 font-bold">{{ $sale->invoice_no }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Customer') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $sale->customer?->name ?? __('Walk-in') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Date') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $sale->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Payment Method') }}</h4>
                            <p class="mt-1 text-sm text-gray-800 font-semibold">{{ ucfirst($sale->payment_method) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h4>
                            <p class="mt-1 text-sm">
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-bold rounded-full {{ $sale->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($sale->payment_status === 'refunded' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($sale->payment_status) }}</span>
                            </p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Sale Items') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($sale->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $item->medicine?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ number_format($item->quantity) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($item->unit_price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No items found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>{{ __('Subtotal') }}</span>
                                    <span class="font-semibold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->subtotal) }}</span>
                                </div>
                                @if($sale->tax > 0)
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>{{ __('Tax') }}</span>
                                        <span class="font-semibold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->tax) }}</span>
                                    </div>
                                @endif
                                @if($sale->discount > 0)
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>{{ __('Discount') }}</span>
                                        <span class="font-semibold text-red-600">-{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->discount) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-base font-extrabold text-emerald-700 border-t border-gray-200 pt-2">
                                    <span>{{ __('Total') }}</span>
                                    <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>