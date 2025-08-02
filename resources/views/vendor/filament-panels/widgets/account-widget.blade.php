@php
    $user = filament()->auth()->user();
    $now = \Carbon\Carbon::now()->locale('id');
@endphp

<x-filament-widgets::widget class="fi-account-widget col-span-full">
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-3 items-center">
            {{-- Kolom 1: User --}}
            <div class="flex items-center gap-x-3">
                <x-filament-panels::avatar.user size="lg" :user="$user" />
                <div>
                    <h2 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ filament()->getUserName($user) }}
                    </p>
                </div>
            </div>

            {{-- Kolom 2: Jam Digital (di tengah) --}}
            <div class="flex justify-center md:justify-center mt-6 md:mt-0">
                <div class="text-center">
                    <div id="jam" class="text-3xl font-mono font-semibold text-primary-600 dark:text-primary-400"></div>
                    <div class="text-sm text-green-500 dark:text-green-300">
                        {{ $now->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            </div>

            {{-- Kolom 3: Tombol Logout --}}
            <div class="flex justify-center md:justify-end mt-6 md:mt-0">
                <form
                    action="{{ filament()->getLogoutUrl() }}"
                    method="post"
                >
                    @csrf
                    <x-filament::button
                        color="gray"
                        icon="heroicon-m-arrow-left-on-rectangle"
                        icon-alias="panels::widgets.account.logout-button"
                        labeled-from="sm"
                        tag="button"
                        type="submit"
                    >
                        {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                    </x-filament::button>
                </form>
            </div>
        </div>
    </x-filament::section>

    {{-- Jam Script --}}
    <script>
        function updateJam() {
            const now = new Date();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('jam').textContent = `${jam}:${menit}:${detik}`;
        }
        setInterval(updateJam, 1000);
        updateJam();
    </script>
</x-filament-widgets::widget>
