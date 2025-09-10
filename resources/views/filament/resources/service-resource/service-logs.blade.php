{{-- Include helpers --}}
@include('filament.resources.service-resource.helpers.log-helpers')

<div class="fi-modal-content">
    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4">
            <h3 class="text-lg font-medium text-gray-950 dark:text-white">
                Log Perubahan Service
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                History semua perubahan untuk service ini
            </p>
        </div>

        <!-- Tabel -->
        <div class="fi-ta fi-ta-grid overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <!-- Header Tabel -->
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr class="divide-x divide-gray-200 dark:divide-white/5">
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    User
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Field
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Tipe
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Nilai Lama
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Nilai Baru
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Keterangan
                                </span>
                            </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-ta-header-cell-wrapper flex items-center gap-x-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Waktu
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>

                <!-- Body Tabel -->
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($logs as $log)
                    <tr class="divide-x divide-gray-200 dark:divide-white/5">
                        <!-- User -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $log->user_name }}
                                </span>
                            </div>
                        </td>
                        <!-- Field -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium px-2 py-1 bg-info-50 text-info-700 ring-1 ring-info-600/10 dark:bg-info-400/10 dark:text-info-400">
                                {{ getFieldLabel($log->field_changed) }}
                            </span>
                        </td>

                        <!-- Tipe -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium px-2 py-1 
                                    @if($log->change_type === 'create') bg-success-50 text-success-700 ring-1 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400
                                    @elseif($log->change_type === 'update') bg-primary-50 text-primary-700 ring-1 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400
                                    @elseif($log->change_type === 'delete') bg-danger-50 text-danger-700 ring-1 ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400
                                    @elseif($log->change_type === 'staging_change') bg-purple-50 text-purple-700 ring-1 ring-purple-600/10 dark:bg-purple-400/10 dark:text-purple-400
                                    @elseif($log->change_type === 'restore') bg-warning-50 text-warning-700 ring-1 ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400
                                    @else bg-gray-50 text-gray-700 ring-1 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->change_type)) }}
                            </span>
                        </td>

                        <!-- Nilai Lama -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ formatLogValue($log->field_changed, $log->old_value) }}
                            </div>
                        </td>

                        <!-- Nilai Baru -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ formatLogValue($log->field_changed, $log->new_value) }}
                            </div>
                        </td>

                        <!-- Keterangan -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->keterangan ?? '-' }}
                            </div>
                        </td>

                        <!-- Waktu -->
                        <td class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="fi-ta-cell px-3 py-4 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="flex flex-col items-center justify-center py-8">
                                <div class="fi-icon-btn-icon fi-color-gray fi-icon-btn-icon-size-lg h-12 w-12 text-gray-400 dark:text-gray-500">
                                    <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                    Belum ada log perubahan untuk service ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer dengan Pagination -->
        @if($logs->hasPages())
        <div class="fi-ta-pagination flex items-center justify-between px-6 py-3">
            <div class="flex items-center gap-x-2">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} hasil
                </span>
            </div>
            <div class="flex items-center gap-x-2">
                {{ $logs->links() }}
            </div>
        </div>
        @else
        <div class="fi-ta-pagination flex items-center justify-between px-6 py-3">
            <span class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan {{ $logs->count() }} hasil
            </span>
        </div>
        @endif
    </div>
</div>