<div class="space-y-4">

    {{-- ✅ Daftar file lampiranAssets --}}
    @if ($record->lampiran?->lampiran_asset)
    <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
        <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">
            Daftar Lampiran:
        </h2>
        @forelse ($record->lampiranAssets as $lampiran)
        <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
            📎
            <a href="{{ asset('storage/' . $lampiran->file_path) }}"
                target="_blank"
                class="text-blue-600 hover:underline dark:text-blue-400">
                {{ $lampiran->original_name }}
            </a>
        </div>
        @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada lampiran yang diunggah.</p>
        @endforelse
    </div>
    @endif

    {{-- ✅ Daftar file lampiranDinas --}}
    @if ($record->lampiran?->lampiran_dinas)
    <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
        <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">
            Daftar Lampiran:
        </h2>
        @forelse ($record->lampiranDinas as $lampiran)
        <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
            📎
            <a href="{{ asset('storage/' . $lampiran->file_path) }}"
                target="_blank"
                class="text-blue-600 hover:underline dark:text-blue-400">
                {{ $lampiran->original_name }}
            </a>
        </div>
        @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada lampiran yang diunggah.</p>
        @endforelse
    </div>
    @endif

    {{-- ✅ Preview PDF --}}
    <div class="w-full bg-white dark:bg-gray-900 rounded-md shadow dark:shadow-md" style="height: 75vh;">
        <object
            data="{{ $url }}"
            type="application/pdf"
            class="w-full h-full rounded-md">
            <div class="p-4 text-center text-sm text-gray-700 dark:text-gray-200">
                PDF tidak bisa ditampilkan.
                <a href="{{ $url }}" class="text-blue-600 underline dark:text-blue-400">
                    Download PDF
                </a>
            </div>
        </object>
    </div>
</div>