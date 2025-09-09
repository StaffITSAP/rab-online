<div class="fi-section-content-ctn">
    @if($logs->count() > 0)
    <!-- Main Table Container -->
    <div class="fi-table-ctn overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10">
        <div class="fi-table-content overflow-x-auto">
            <table class="fi-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <!-- Table Header -->
                <thead class="fi-table-header bg-gray-50/50 dark:bg-white/5">
                    <tr class="fi-table-header-row">
                        <th scope="col" class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-start">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-table-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    User
                                </span>
                            </span>
                        </th>
                        <th scope="col" class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-start">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-table-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Dari
                                </span>
                            </span>
                        </th>
                        <th scope="col" class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-start">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-table-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Ke
                                </span>
                            </span>
                        </th>
                        <th scope="col" class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-start">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-table-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Keterangan
                                </span>
                            </span>
                        </th>
                        <th scope="col" class="fi-table-header-cell px-3 py-3.5 sm:px-6 text-start">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-table-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Waktu
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="fi-table-body divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                    @foreach($logs as $log)
                    @php
                    // Clean user name from "NA" prefix
                    $cleanedUserName = preg_replace('/^NA\s*/', '', $log->user_name);

                    // Define status colors following Filament's color system
                    $statusColors = [
                    'request' => [
                    'bg' => 'bg-gray-50 dark:bg-gray-400/10',
                    'text' => 'text-gray-600 dark:text-gray-400',
                    'ring' => 'ring-gray-500/20 dark:ring-gray-400/30',
                    'dot' => 'bg-gray-400 dark:bg-gray-400'
                    ],
                    'cek_kerusakan' => [
                    'bg' => 'bg-info-50 dark:bg-info-400/10',
                    'text' => 'text-info-600 dark:text-info-400',
                    'ring' => 'ring-info-500/20 dark:ring-info-400/30',
                    'dot' => 'bg-info-500 dark:bg-info-400'
                    ],
                    'ada_biaya' => [
                    'bg' => 'bg-warning-50 dark:bg-warning-400/10',
                    'text' => 'text-warning-600 dark:text-warning-400',
                    'ring' => 'ring-warning-500/20 dark:ring-warning-400/30',
                    'dot' => 'bg-warning-500 dark:bg-warning-400'
                    ],
                    'close' => [
                    'bg' => 'bg-success-50 dark:bg-success-400/10',
                    'text' => 'text-success-600 dark:text-success-400',
                    'ring' => 'ring-success-500/20 dark:ring-success-400/30',
                    'dot' => 'bg-success-500 dark:bg-success-400'
                    ],
                    'approve' => [
                    'bg' => 'bg-primary-50 dark:bg-primary-400/10',
                    'text' => 'text-primary-600 dark:text-primary-400',
                    'ring' => 'ring-primary-500/20 dark:ring-primary-400/30',
                    'dot' => 'bg-primary-500 dark:bg-primary-400'
                    ]
                    ];
                    @endphp

                    <tr class="fi-table-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <!-- User Column -->
                        <td class="fi-table-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                            <div class="fi-table-cell-content px-3 py-4">
                                <div class="flex items-center gap-x-3">
                                    <div class="fi-avatar flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                        <span class="text-xs font-medium uppercase">
                                            {{ strtoupper(substr($cleanedUserName, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="grid flex-1">
                                        <div class="flex items-center gap-x-2.5">
                                            <div class="fi-table-cell-content-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                {{ $cleanedUserName }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- From Status Column -->
                        <td class="fi-table-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                            <div class="fi-table-cell-content px-3 py-4">
                                @if($log->old_staging)
                                @php $colors = $statusColors[$log->old_staging] ?? $statusColors['request']; @endphp
                                <div class="fi-badge flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['ring'] }}">
                                    <span class="fi-badge-icon h-1.5 w-1.5 rounded-full {{ $colors['dot'] }}"></span>
                                    {{ \App\Enums\StagingEnum::from($log->old_staging)->label() }}
                                </div>
                                @else
                                <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </div>
                        </td>

                        <!-- To Status Column -->
                        <td class="fi-table-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                            <div class="fi-table-cell-content px-3 py-4">
                                @php $colors = $statusColors[$log->new_staging] ?? $statusColors['request']; @endphp
                                <div class="fi-badge flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['ring'] }}">
                                    <span class="fi-badge-icon h-1.5 w-1.5 rounded-full {{ $colors['dot'] }}"></span>
                                    {{ \App\Enums\StagingEnum::from($log->new_staging)->label() }}
                                </div>
                            </div>
                        </td>

                        <!-- Description Column -->
                        <td class="fi-table-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                            <div class="fi-table-cell-content px-3 py-4">
                                <div class="max-w-xs">
                                    <p class="text-sm leading-6 text-gray-950 dark:text-white truncate"
                                        @if($log->keterangan && strlen($log->keterangan) > 50)
                                        x-data="{ tooltip: false }"
                                        x-on:mouseenter="tooltip = true"
                                        x-on:mouseleave="tooltip = false"
                                        @endif>
                                        {{ $log->keterangan ?: 'Tidak ada keterangan' }}
                                    </p>
                                    @if($log->keterangan && strlen($log->keterangan) > 50)
                                    <div x-show="tooltip"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-10 mt-2 p-2 text-xs text-white bg-gray-900 rounded shadow-lg max-w-sm dark:bg-gray-700">
                                        {{ $log->keterangan }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Time Column -->
                        <td class="fi-table-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                            <div class="fi-table-cell-content px-3 py-4">
                                <div class="grid">
                                    <div class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                        {{ $log->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->format('H:i') }} WIB
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Footer -->
    <div class="fi-section-content mt-6">
        <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-white/5 dark:ring-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-x-3">
                <div class="fi-badge flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-gray-50 text-gray-600 ring-gray-500/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/30">
                    <span class="fi-badge-icon h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    Total {{ $logs->count() }} perubahan
                </div>
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Terakhir diperbarui:
                <span class="font-medium text-gray-500 dark:text-gray-400">
                    {{ now()->format('d M Y H:i') }} WIB
                </span>
            </div>
        </div>
    </div>

    @else
    <!-- Empty State -->
    <div class="fi-section-content">
        <div class="fi-empty-state flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 p-6 dark:border-white/10">
            <div class="fi-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-white/5">
                <svg class="fi-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="fi-empty-state-content text-center">
                <h3 class="fi-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Tidak ada log perubahan
                </h3>
                <p class="fi-empty-state-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada perubahan status staging yang tercatat untuk service ini.
                </p>
                <div class="fi-empty-state-actions mt-4">
                    <div class="fi-badge flex items-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-info-50 text-info-600 ring-info-500/20 dark:bg-info-400/10 dark:text-info-400 dark:ring-info-400/30">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853L15.75 12M12 9.75h.008v.008H12V9.75zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Perubahan akan tercatat otomatis
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Filament-compatible CSS -->
<style>
    /* Enhanced scrollbar for Filament compatibility */
    .fi-table-content::-webkit-scrollbar {
        height: 8px;
    }

    .fi-table-content::-webkit-scrollbar-track {
        background: theme('colors.gray.100');
        border-radius: 4px;
    }

    .fi-table-content::-webkit-scrollbar-thumb {
        background: theme('colors.gray.300');
        border-radius: 4px;
        border: 1px solid theme('colors.gray.200');
    }

    .dark .fi-table-content::-webkit-scrollbar-track {
        background: theme('colors.white/0.05');
    }

    .dark .fi-table-content::-webkit-scrollbar-thumb {
        background: theme('colors.white/0.1');
        border-color: theme('colors.white/0.05');
    }

    .fi-table-content::-webkit-scrollbar-thumb:hover {
        background: theme('colors.gray.400');
    }

    .dark .fi-table-content::-webkit-scrollbar-thumb:hover {
        background: theme('colors.white/0.2');
    }

    /* Mobile responsive table */
    @media (max-width: 640px) {
        .fi-table {
            display: block;
            width: 100%;
        }

        .fi-table-header {
            display: none;
        }

        .fi-table-body {
            display: block;
            width: 100%;
        }

        .fi-table-row {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid theme('colors.gray.200');
            border-radius: 0.75rem;
            padding: 1rem;
            background: white;
        }

        .dark .fi-table-row {
            border-color: theme('colors.white/0.1');
            background: theme('colors.white/0.05');
        }

        .fi-table-cell {
            display: block;
            padding: 0.5rem 0 !important;
            border: none;
        }

        .fi-table-cell:before {
            content: attr(data-label);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: theme('colors.gray.500');
            display: block;
            margin-bottom: 0.25rem;
        }

        .dark .fi-table-cell:before {
            color: theme('colors.gray.400');
        }

        .fi-table-cell-content {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .max-w-xs {
            max-width: none;
        }
    }

    /* Tooltip positioning fix for mobile */
    @media (max-width: 640px) {
        [x-show="tooltip"] {
            position: fixed;
            left: 1rem;
            right: 1rem;
            width: auto;
            max-width: none;
        }
    }
</style>

<!-- Mobile responsive JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add data labels for mobile responsive
        const headers = ['User', 'Dari', 'Ke', 'Keterangan', 'Waktu'];
        const cells = document.querySelectorAll('.fi-table-cell');

        cells.forEach((cell, index) => {
            const row = cell.closest('tr');
            if (row && !row.classList.contains('fi-table-header-row')) {
                const cellIndex = Array.from(row.querySelectorAll('.fi-table-cell')).indexOf(cell);
                if (cellIndex >= 0 && cellIndex < headers.length) {
                    cell.setAttribute('data-label', headers[cellIndex]);
                }
            }
        });

        // Handle tooltip positioning on mobile
        function adjustTooltips() {
            const tooltips = document.querySelectorAll('[x-show="tooltip"]');
            tooltips.forEach(tooltip => {
                if (window.innerWidth <= 640) {
                    tooltip.style.position = 'fixed';
                    tooltip.style.left = '1rem';
                    tooltip.style.right = '1rem';
                    tooltip.style.width = 'auto';
                    tooltip.style.maxWidth = 'none';
                } else {
                    tooltip.style.position = '';
                    tooltip.style.left = '';
                    tooltip.style.right = '';
                    tooltip.style.width = '';
                    tooltip.style.maxWidth = '';
                }
            });
        }

        adjustTooltips();
        window.addEventListener('resize', adjustTooltips);
    });
</script>