<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-sky-100 text-sky-800 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('Customer Sales Returns') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Audit log of all returned medicines, stock restorations, and customer refunds.') }}</p>
                </div>
            </div>
            <a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Select Sale to Return') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-base">{{ __('Customer Returns History') }}</h3>
                    <a href="{{ route('reports.ledger') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                        {{ __('View Audit Ledger') }} &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-slate-50 text-gray-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5 text-left">{{ __('Date & Time') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Invoice #') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Medicine') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Batch #') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Returned Unit Qty') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Base Units Restored') }}</th>
                                <th class="px-6 py-3.5 text-right">{{ __('Refund Amount') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Processed By') }}</th>
                                <th class="px-6 py-3.5 text-left">{{ __('Reason / Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($returns as $ret)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-600 whitespace-nowrap">
                                        {{ $ret->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                        <a href="{{ route('sales.show', $ret->sale_id) }}" class="text-emerald-600 hover:underline">
                                            {{ $ret->sale?->invoice_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $ret->medicine?->name }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-mono whitespace-nowrap">
                                        {{ $ret->batch?->batch_number ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">
                                        {{ number_format($ret->returned_unit_quantity) }} {{ $ret->returned_unit_name }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-emerald-600 whitespace-nowrap">
                                        +{{ number_format($ret->returned_base_quantity) }} {{ $ret->medicine?->base_unit ?? 'base' }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-gray-900 whitespace-nowrap">
                                        {{ setting('currency_symbol', 'UGX') }} {{ format_price($ret->refund_amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                        {{ $ret->user?->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">
                                        {{ $ret->reason }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <p class="font-bold text-gray-600">{{ __('No customer returns recorded yet.') }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ __('To process a return, view any invoice in Sales History and click "Process Return".') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $returns->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
