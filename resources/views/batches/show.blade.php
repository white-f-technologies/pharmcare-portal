<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Batch') }} - {{ $batch->batch_number }}</h2>
            <div class="space-x-2">
                <a href="{{ route('batches.edit', $batch) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Edit') }}</a>
                <a href="{{ route('medicines.show', $batch->medicine) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Medicine') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $batch->medicine->name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Batch Number') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $batch->batch_number }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Supplier') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $batch->supplier?->name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Expiry Date') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $batch->expiry_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Quantity') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $batch->quantity }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Purchase Price') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">${{ number_format($batch->purchase_price, 2) }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Selling Price') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">${{ number_format($batch->selling_price, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
