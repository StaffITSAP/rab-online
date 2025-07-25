<x-filament::page>
    {{ $this->form }}

    <x-filament::button wire:click="submit" class="mt-4">
        Simpan
    </x-filament::button>

    @if (Auth::user()->status?->signature_path)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Tanda tangan saat ini:</p>
            <img src="{{ Storage::disk('public')->url(Auth::user()->status->signature_path) }}"
                 alt="Tanda Tangan"
                 class="h-32 mt-2 border rounded shadow-md">
        </div>
    @endif
</x-filament::page>
