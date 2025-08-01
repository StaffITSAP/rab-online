@php
$filePath = $record->file_path;
$url = asset('storage/' . $filePath);
$isPdf = str_ends_with(strtolower($filePath), '.pdf');
@endphp


@if ($isPdf)
<a href="{{ $url }}" target="_blank" class="text-sm text-red-600 font-semibold">
    <div class="flex items-center gap-2 text-red-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 2C4.9 2 4 2.9 4 4v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm6 7V3.5L18.5 9H12z" />
        </svg>
        <span class="font-medium">Lampiran Dokumen/PDF</span>
    </div>
</a>
@else
<img src="{{ $url }}" alt="Lampiran" class="h-10 rounded" />
@endif