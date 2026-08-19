<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Prescription') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('prescriptions.store') }}" method="POST" x-data="prescriptionForm()">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <x-input-label for="customer_id" :value="__('Customer')" />
                                <select id="customer_id" name="customer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Select Customer') }}</option>
                                    @foreach($customers as $id => $name)
                                        <option value="{{ $id }}" {{ old('customer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="doctor_name" :value="__('Doctor Name')" />
                                <x-text-input id="doctor_name" name="doctor_name" type="text" class="mt-1 block w-full" :value="old('doctor_name')" required />
                                <x-input-error :messages="$errors->get('doctor_name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="hospital" :value="__('Hospital / Clinic')" />
                                <x-text-input id="hospital" name="hospital" type="text" class="mt-1 block w-full" :value="old('hospital')" />
                                <x-input-error :messages="$errors->get('hospital')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="diagnosis" :value="__('Diagnosis')" />
                            <textarea id="diagnosis" name="diagnosis" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('diagnosis') }}</textarea>
                            <x-input-error :messages="$errors->get('diagnosis')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('Prescription Items') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Medicine') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Dosage') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Duration') }}</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <select x-bind:name="'items['+index+'][medicine_id]'" x-model="item.medicine_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                        <option value="">{{ __('Select Medicine') }}</option>
                                                        @foreach($medicines as $id => $name)
                                                            <option value="{{ $id }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input x-bind:name="'items['+index+'][dosage]'" type="text" x-model="item.dosage" class="block w-full text-sm" placeholder="{{ __('e.g. 1 tablet 3x daily') }}" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <x-text-input x-bind:name="'items['+index+'][duration]'" type="text" x-model="item.duration" class="block w-full text-sm" placeholder="{{ __('e.g. 7 days') }}" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 text-sm">{{ __('Remove') }}</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" @click="addItem()" class="mt-2 inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ __('+ Add Medicine') }}
                            </button>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Prescription') }}</x-primary-button>
                            <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prescriptionForm() {
            return {
                items: {!! json_encode(old('items', [['medicine_id' => '', 'dosage' => '', 'duration' => '']])) !!},
                addItem() {
                    this.items.push({ medicine_id: '', dosage: '', duration: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items = [{ medicine_id: '', dosage: '', duration: '' }];
                    }
                }
            }
        }
    </script>
</x-app-layout>