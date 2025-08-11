<div class="space-y-4">
    {{-- ====================== --}}
    {{--  Lampiran: Assets     --}}
    {{-- ====================== --}}
    @if ($record->lampiran?->lampiran_asset && $record->lampiranAssets->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">Daftar Lampiran Assets</h2>
            @foreach ($record->lampiranAssets as $lampiran)
                <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
                    📎
                    <a href="{{ asset('storage/' . $lampiran->file_path) }}" target="_blank"
                       class="text-blue-600 hover:underline dark:text-blue-400">
                        {{ $lampiran->original_name }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ====================== --}}
    {{--  Lampiran: Dinas      --}}
    {{-- ====================== --}}
    @if ($record->lampiran?->lampiran_dinas && $record->lampiranDinas->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">Daftar Lampiran Dinas</h2>
            @foreach ($record->lampiranDinas as $lampiran)
                <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
                    📎
                    <a href="{{ asset('storage/' . $lampiran->file_path) }}" target="_blank"
                       class="text-blue-600 hover:underline dark:text-blue-400">
                        {{ $lampiran->original_name }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ====================== --}}
    {{--  Lampiran: Promosi    --}}
    {{-- ====================== --}}
    @if ($record->lampiran?->lampiran_marcomm_promosi && $record->lampiranPromosi->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">Daftar Lampiran Promosi</h2>
            @foreach ($record->lampiranPromosi as $lampiran)
                <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
                    📎
                    <a href="{{ asset('storage/' . $lampiran->file_path) }}" target="_blank"
                       class="text-blue-600 hover:underline dark:text-blue-400">
                        {{ $lampiran->original_name }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ====================== --}}
    {{--  Lampiran: Kebutuhan  --}}
    {{-- ====================== --}}
    @if ($record->lampiran?->lampiran_marcomm_kebutuhan && $record->lampiranKebutuhan->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <h2 class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-100">Daftar Lampiran Kebutuhan</h2>
            @foreach ($record->lampiranKebutuhan as $lampiran)
                <div class="text-sm flex items-center gap-2 mb-1 text-gray-800 dark:text-gray-200">
                    📎
                    <a href="{{ asset('storage/' . $lampiran->file_path) }}" target="_blank"
                       class="text-blue-600 hover:underline dark:text-blue-400">
                        {{ $lampiran->original_name }}
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ===================================== --}}
    {{--  Detail Kebutuhan Amplop (AMPLOP)     --}}
    {{-- ===================================== --}}
    @php
        // Toggle kebutuhan_amplop dari baris pertama kebutuhan
        $needAmplop = (bool) optional(
            $record->pengajuan_marcomm_kebutuhans()->orderBy('id')->first()
        )->kebutuhan_amplop;

        // Detail amplop
        $amplopRows  = $record->marcommKebutuhanAmplops ?? collect();
        $totalAmplop = $amplopRows->sum('jumlah');
    @endphp

    @if ($needAmplop || $amplopRows->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Detail Kebutuhan Amplop</h2>
                <div class="text-xs px-2 py-1 rounded-full
                    {{ $needAmplop ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $needAmplop ? 'Aktif' : 'Tidak Aktif' }}
                </div>
            </div>

            @if ($amplopRows->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data kebutuhan amplop yang diisi.</p>
            @else
                <div class="overflow-hidden rounded-md border dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Cabang</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 dark:text-gray-300">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($amplopRows as $i => $row)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $row->cabang }}</td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-800 dark:text-gray-200">
                                        {{ number_format($row->jumlah, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th colspan="2" class="px-3 py-2 text-right text-xs font-semibold text-gray-700 dark:text-gray-200">
                                    Total Amplop
                                </th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($totalAmplop, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ===================================== --}}
    {{--  Detail Kebutuhan Kartu (KARTU)       --}}
    {{-- ===================================== --}}
    @php
        // Toggle kebutuhan_kartu dari baris pertama kebutuhan (boolean)
        $needKartu = (bool) optional(
            $record->pengajuan_marcomm_kebutuhans()->orderBy('id')->first()
        )->kebutuhan_kartu;

        // Detail kartu dari relasi pengajuan_marcomm_kebutuhan_kartus
        $kartuRows = $record->marcommKebutuhanKartus ?? collect();
    @endphp

    @if ($needKartu || $kartuRows->isNotEmpty())
        <div class="border rounded-md p-4 bg-white shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Detail Kebutuhan Kartu Nama dan ID Card</h2>
                <div class="text-xs px-2 py-1 rounded-full
                    {{ $needKartu ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $needKartu ? 'Aktif' : 'Tidak Aktif' }}
                </div>
            </div>

            @if ($kartuRows->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data kebutuhan kartu yang diisi.</p>
            @else
                <div class="overflow-hidden rounded-md border dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">Kartu Nama</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300">ID Card</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($kartuRows as $i => $row)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $row->kartu_nama }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $row->id_card }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- =============== --}}
    {{--  Preview PDF    --}}
    {{-- =============== --}}
    <div class="w-full bg-white dark:bg-gray-900 rounded-md shadow dark:shadow-md" style="height: 75vh;">
        <object data="{{ $url }}" type="application/pdf" class="w-full h-full rounded-md">
            <div class="p-4 text-center text-sm text-gray-700 dark:text-gray-200">
                PDF tidak bisa ditampilkan.
                <a href="{{ $url }}" class="text-blue-600 underline dark:text-blue-400">Download PDF</a>
            </div>
        </object>
    </div>
</div>
