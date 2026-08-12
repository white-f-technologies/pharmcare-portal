<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                    {{ __('Database Backups') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">{{ __('Generate, download, and restore system database snapshots securely.') }}</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ showExport: false }">
                <button @click="showExport = !showExport" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Create New Backup') }}</span>
                </button>

                {{-- Backup creation panel with export path --}}
                <div x-show="showExport" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7"/></svg>
                            {{ __('Create Backup') }}
                        </h3>

                        <form action="{{ route('backups.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="include_media" value="1" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-xs font-semibold text-gray-700">{{ __('Include uploaded images/media in backup') }}</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">{{ __('Export Copy to External Path (Optional)') }}</label>
                                <input type="text" name="custom_export_path" placeholder="e.g. E:\\Backups or D:\\PharmCare_Backups" class="w-full text-xs border-gray-200 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 placeholder-gray-400 font-mono">
                                <p class="text-[10px] text-gray-400 mt-1">{{ __('Enter a USB drive or external folder path. Leave empty to save only to internal storage.') }}</p>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <button type="button" @click="showExport = false" class="w-1/3 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" class="w-2/3 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Create Backup Now') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ restoreFile: null, isUploading: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success Alert -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Alert -->
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Top Notice & Upload Card -->
            <div class="bg-gradient-to-r from-emerald-900 to-teal-800 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                    <div class="lg:col-span-8 space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-700/60 rounded-full text-xs font-bold uppercase tracking-wider text-emerald-200">
                            🛡️ {{ __('Admin System Security') }}
                        </div>
                        <h3 class="text-xl font-extrabold">{{ __('Automated Database Disaster Recovery') }}</h3>
                        <p class="text-xs text-emerald-100/90 leading-relaxed">
                            {{ __('Creating regular database backups ensures your pharmacy records, stock inventory, and sales history are safe. You can instantly restore any snapshot or upload an external .sql file if needed.') }}
                        </p>
                    </div>

                    <!-- Upload External SQL Form -->
                    <div class="lg:col-span-4 bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/20">
                        <form action="{{ route('backups.upload-restore') }}" method="POST" enctype="multipart/form-data" @submit="isUploading = true">
                            @csrf
                            <label class="block text-xs font-bold text-emerald-100 mb-2">{{ __('Upload & Restore .SQL File') }}</label>
                            <input type="file" name="backup_file" accept=".sql" class="block w-full text-xs text-emerald-100 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-white file:text-emerald-900 hover:file:bg-emerald-50 cursor-pointer" required />
                            <button type="submit" :disabled="isUploading" class="mt-3 w-full py-2 px-3 bg-emerald-500 hover:bg-emerald-400 text-emerald-950 font-bold text-xs rounded-lg transition flex items-center justify-center gap-1.5">
                                <span x-show="isUploading">🌀 {{ __('Restoring...') }}</span>
                                <span x-show="!isUploading">📥 {{ __('Upload & Restore') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Backups List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">{{ __('Stored Database Backups') }}</h3>
                        <p class="text-xs text-gray-400">{{ __('Backups saved in system storage.') }}</p>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full">
                        {{ count($backups) }} {{ __('File(s)') }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="py-3.5 px-6">{{ __('File Name') }}</th>
                                <th class="py-3.5 px-6">{{ __('Date Created') }}</th>
                                <th class="py-3.5 px-6">{{ __('File Size') }}</th>
                                <th class="py-3.5 px-6">{{ __('Integrity') }}</th>
                                <th class="py-3.5 px-6 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($backups as $backup)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="py-4 px-6 font-bold text-gray-800 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                        <span>{{ $backup['name'] }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-gray-600 font-medium">
                                        {{ $backup['created_at'] }}
                                    </td>
                                    <td class="py-4 px-6 font-bold text-gray-700">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($backup['has_checksum'] ?? false)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                SHA256
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Download -->
                                            <a href="{{ route('backups.download', $backup['name']) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>{{ __('Download') }}</span>
                                            </a>

                                            <!-- Restore Trigger Modal -->
                                            <button type="button" @click="restoreFile = '{{ $backup['name'] }}'" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>{{ __('Restore') }}</span>
                                            </button>

                                            <!-- Delete -->
                                            <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg text-xs font-semibold transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-700">{{ __('No backup snapshots found') }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ __('Click "Create New Backup" above to generate your first snapshot.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Restore Confirmation Modal -->
        <template x-if="restoreFile">
            <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-gray-100">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.072 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>

                    <div class="text-center space-y-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Confirm Database Restore') }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ __('Are you sure you want to restore the database from file:') }}
                        </p>
                        <p class="text-xs font-bold text-amber-700 bg-amber-50 p-2 rounded-lg break-all" x-text="restoreFile"></p>
                        <p class="text-[11px] text-rose-600 font-semibold mt-2">
                            ⚠️ {{ __('Warning: This will overwrite all current system data with the contents of the backup snapshot. A safety backup of the current state will be created automatically before restoring.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="restoreFile = null" class="w-1/2 py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                            {{ __('Cancel') }}
                        </button>
                        <form x-bind:action="'{{ url('/backups') }}/' + encodeURIComponent(restoreFile) + '/restore'" method="POST" class="w-1/2">
                            @csrf
                            <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                                <span>{{ __('Yes, Restore Now') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
