<?php

namespace App\Filament\Resources\PenggunaanTeknisiResource\Pages;

use App\Filament\Resources\PenggunaanTeknisiResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Actions;
use Illuminate\Support\Str;

class ListPenggunaanTeknisis extends ListRecords
{
    protected static string $resource = PenggunaanTeknisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->hidden(), // read-only

            Action::make('download_all_xlsx')
                ->label('Download Semua (XLSX)')
                ->color('success')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn() => route('exports.penggunaan_teknisi.all'))
                ->openUrlInNewTab(false),

            Action::make('download_filtered_xlsx')
                ->label('Download (Sesuai Filter)')
                ->icon('heroicon-m-arrow-down-tray')
                ->modalHeading('Download (Sesuai Filter)')
                ->modalSubmitActionLabel('Download')
                ->modalCancelActionLabel('Batal')
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters ?? [];
                    $nonce   = Str::uuid()->toString(); // cache-buster setiap klik

                    $url = route('exports.penggunaan_teknisi.filtered', [
                        'filters' => json_encode($filters, JSON_UNESCAPED_UNICODE),
                        '_nonce'  => $nonce,
                    ]);

                    return redirect()->to($url);
                }),
        ];
    }
}
