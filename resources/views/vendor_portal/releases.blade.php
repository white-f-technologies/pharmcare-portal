<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-xl text-gray-900 leading-tight">
                    {{ __('Software Release & Update Management') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Publish desktop software releases, update ZIP links, and release notes') }}</p>
            </div>
            <a href="{{ route('vendor.dashboard') }}" class="px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition">
                ← Back to Control Center
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-bold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Publish Release Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">+ Publish New Software Release</h3>

                <form action="{{ route('vendor.releases.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Version Number *</label>
                        <input type="text" name="version" required placeholder="e.g. 2.1.0" value="2.1.0" class="w-full text-xs font-mono font-bold border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Release Date *</label>
                        <input type="date" name="release_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Download Package URL *</label>
                        <input type="url" name="download_url" required placeholder="https://portal.yourdomain.com/downloads/v2.1.0.zip" class="w-full text-xs border-gray-200 rounded-xl">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Release Notes</label>
                        <textarea name="release_notes" rows="2" placeholder="Describe changes, bug fixes, and new features..." class="w-full text-xs border-gray-200 rounded-xl"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Migration Requirement</label>
                        <label class="flex items-center gap-2 mt-2">
                            <input type="checkbox" name="requires_db_migration" value="1" class="rounded border-gray-300 text-indigo-600">
                            <span class="text-xs font-semibold text-gray-700">Requires Database Migration</span>
                        </label>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Publish Release v2.1.0</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Published Releases Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-900 text-white flex justify-between items-center">
                    <h3 class="text-sm font-bold">Published Software Releases</h3>
                    <span class="text-xs text-slate-400 font-semibold">{{ count($releases) }} Releases</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3 px-6">Version</th>
                                <th class="py-3 px-6">Release Date</th>
                                <th class="py-3 px-6">Download Link</th>
                                <th class="py-3 px-6">Migration</th>
                                <th class="py-3 px-6">Release Notes</th>
                                <th class="py-3 px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($releases as $rel)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="py-3.5 px-6 font-mono font-bold text-amber-600 text-sm">v{{ $rel->version }}</td>
                                    <td class="py-3.5 px-6 text-gray-600">{{ $rel->release_date->format('M d, Y') }}</td>
                                    <td class="py-3.5 px-6"><a href="{{ $rel->download_url }}" target="_blank" class="text-indigo-600 font-bold hover:underline">Download ZIP</a></td>
                                    <td class="py-3.5 px-6">
                                        @if($rel->requires_db_migration)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">YES</span>
                                        @else
                                            <span class="text-gray-400">NO</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-gray-700 max-w-xs truncate">{{ $rel->release_notes ?? 'No notes' }}</td>
                                    <td class="py-3.5 px-6"><span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">{{ $rel->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">No releases published yet. Use the form above to publish your first release.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
