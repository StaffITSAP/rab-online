<div class="space-y-4">
    {{-- ✅ Daftar file lampiranAssets --}}
    @if ($record->lampiran?->lampiran_asset)
    <div class="border rounded-md p-4 bg-white shadow-sm">
        <h2 class="text-sm font-semibold mb-2">Daftar Lampiran:</h2>
        @forelse ($record->lampiranAssets as $lampiran)
        <div class="text-sm text-gray-800 flex items-center gap-2 mb-1">
            📎
            <a href="{{ asset('storage/' . $lampiran->file_path) }}"
                target="_blank"
                class="text-blue-600 hover:underline">
                {{ $lampiran->original_name }}
            </a>
        </div>
        @empty
        <p class="text-sm text-gray-500">Belum ada lampiran yang diunggah.</p>
        @endforelse
    </div>
    @endif

    {{-- ✅ Preview PDF --}}
    <div class="w-full" style="height: 75vh;">
        <object
            data="{{ $url }}"
            type="application/pdf"
            class="w-full h-full rounded-md shadow">
            PDF tidak bisa ditampilkan.
            <a href="{{ $url }}" class="text-blue-500 underline">Download PDF</a>
        </object>
    </div>
</div>