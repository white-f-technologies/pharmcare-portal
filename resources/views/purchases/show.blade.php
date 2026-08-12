<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Purchase') }} - {{ $purchase->invoice_no }}</h2>
            <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Invoice #') }}</h4>
                            <p class="mt-1 text-sm text-gray-800 font-bold">{{ $purchase->invoice_no }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Supplier') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $purchase->supplier?->name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Date') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $purchase->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h4>
                            <p class="mt-1 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $purchase->status === 'completed' ? 'green' : 'yellow' }}-100 text-{{ $purchase->status === 'completed' ? 'green' : 'yellow' }}-800">{{ ucfirst($purchase->status) }}</span>
                            </p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Purchase Items') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Batch') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($purchase->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $item->medicine?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->batch?->batch_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ number_format($item->quantity) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($item->unit_price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ setting('currency_symbol', 'UGX') }} {{ format_price($item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No items found') }}</td>
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
                                    <span class="font-semibold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($purchase->items->sum('total')) }}</span>
                                </div>
                                @if($purchase->tax > 0)
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>{{ __('Tax') }}</span>
                                        <span class="font-semibold">{{ setting('currency_symbol', 'UGX') }} {{ format_price($purchase->tax) }}</span>
                                    </div>
                                @endif
                                @if($purchase->discount > 0)
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>{{ __('Discount') }}</span>
                                        <span class="font-semibold text-red-600">-{{ setting('currency_symbol', 'UGX') }} {{ format_price($purchase->discount) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-base font-extrabold text-emerald-700 border-t border-gray-200 pt-2">
                                    <span>{{ __('Total') }}</span>
                                    <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($purchase->total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>