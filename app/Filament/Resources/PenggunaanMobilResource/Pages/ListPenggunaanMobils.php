<?php

namespace App\Filament\Resources\PenggunaanMobilResource\Pages;

use App\Filament\Resources\PenggunaanMobilResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Actions;
use Illuminate\Support\Str;

class ListPenggunaanMobils extends ListRecords
{
    protected static string $resource = PenggunaanMobilResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // read-only

            Action::make('download_all_xlsx')
                ->label('Download Semua (XLSX)')
                ->color('success')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn() => route('exports.penggunaan_mobil.all'))
                ->openUrlInNewTab(false),

            Action::make('download_filtered_xlsx')
                ->label('Download (Sesuai Filter)')
                ->icon('heroicon-m-arrow-down-tray')
                ->modalHeading('Download (Sesuai Filter)')
                ->modalSubmitActionLabel('Download')
                ->modalCancelActionLabel('Batal')
                ->color('info')
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters ?? [];
                    $nonce   = Str::uuid()->toString();       // cache-buster unik

                    $url = route('exports.penggunaan_mobil.filtered', [
                        'filters' => json_encode($filters, JSON_UNESCAPED_UNICODE),
                        '_nonce'  => $nonce,
                    ]);

                    return redirect()->to($url);
                }),
        ];
    }
}
