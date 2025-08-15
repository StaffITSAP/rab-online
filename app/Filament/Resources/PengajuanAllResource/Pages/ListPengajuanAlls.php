<?php

namespace App\Filament\Resources\PengajuanAllResource\Pages;

use App\Filament\Resources\PengajuanAllResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Illuminate\Support\Str;


class ListPengajuanAlls extends ListRecords
{
    protected static string $resource = PengajuanAllResource::class;

    // Simpan filter & sort di URL
    #[Url] public ?array $tableFilters = null;
    #[Url] public ?string $tableSortColumn = null;
    #[Url] public ?string $tableSortDirection = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_all_xlsx')
                ->label('Download Semua (XLSX)')
                ->color('success')
                ->modalSubmitActionLabel('Download')      // ⬅️ ubah "Kirim" jadi "Download"
                ->modalCancelActionLabel('Batal')         // ⬅️ opsional
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn() => route('exports.pengajuans.all'))
                ->openUrlInNewTab(false),

            Action::make('download_filtered_xlsx')
                ->label('Download (Sesuai Filter)')
                ->color('info')
                ->icon('heroicon-m-arrow-down-tray')
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters ?? [];
                    $nonce   = Str::uuid()->toString(); // cache-buster unik setiap klik

                    $url = route('exports.pengajuans.filtered', [
                        'filters' => json_encode($filters, JSON_UNESCAPED_UNICODE),
                        '_nonce'  => $nonce,
                    ]);

                    return redirect()->to($url);
                }),
        ];
    }
}
