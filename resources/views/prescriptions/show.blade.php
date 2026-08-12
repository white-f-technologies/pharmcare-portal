<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Prescription') }} - #{{ $prescription->id }}</h2>
            <a href="{{ route('prescriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Customer') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->customer?->name }}</p>
                            @if($prescription->customer)
                                <p class="text-sm text-gray-600">{{ $prescription->customer->phone }}</p>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Doctor') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->doctor_name }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Hospital / Clinic') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->hospital ?? __('N/A') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Date') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Diagnosis') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->diagnosis ?? __('No diagnosis') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Notes') }}</h4>
                            <p class="mt-1 text-sm text-gray-800">{{ $prescription->notes ?? __('No notes') }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Prescribed Medicines') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Medicine') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Dosage') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Duration') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($prescription->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $item->medicine?->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->dosage }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->duration }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-sm text-gray-500 text-center">{{ __('No medicines prescribed') }}</td>
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