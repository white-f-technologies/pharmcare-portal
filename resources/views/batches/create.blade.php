<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Batch for') }} {{ $medicine->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('medicines.batches.store', $medicine) }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <x-input-label for="batch_number" :value="__('Batch Number')" />
                                <x-text-input id="batch_number" name="batch_number" type="text" class="mt-1 block w-full" :value="old('batch_number')" required />
                                <x-input-error :messages="$errors->get('batch_number')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="supplier_id" :value="__('Supplier')" />
                                <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Select Supplier') }}</option>
                                    @foreach($suppliers as $id => $name)
                                        <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="expiry_date" :value="__('Expiry Date')" />
                                <x-text-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full" :value="old('expiry_date')" required />
                                <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="mfg_date" :value="__('Manufacturing Date')" />
                                <x-text-input id="mfg_date" name="mfg_date" type="date" class="mt-1 block w-full" :value="old('mfg_date')" />
                                <x-input-error :messages="$errors->get('mfg_date')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="purchase_price" :value="__('Purchase Price')" />
                                <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('purchase_price')" oninput="document.getElementById('selling_price').min = this.value" required />
                                <x-input-error :messages="$errors->get('purchase_price')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="selling_price" :value="__('Selling Price')" />
                                <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('selling_price')" required />
                                <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="quantity" :value="__('Quantity')" />
                                <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" :value="old('quantity', 0)" required />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('medicines.show', $medicine) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>