<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Support\Str;


class ListPengajuans extends ListRecords
{
    protected static string $resource = PengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Action::make('download_all_xlsx')
                ->label('Download Semua (XLSX)')
                ->color('success')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn() => route('exports.pengajuans.all'))
                ->openUrlInNewTab(false),

            Action::make('download_filtered_xlsx')
                ->label('Download (Sesuai Filter)')
                ->color('info')
                ->icon('heroicon-m-arrow-down-tray')
                ->modalSubmitActionLabel('Download')      // ⬅️ ubah "Kirim" jadi "Download"
                ->modalCancelActionLabel('Batal')         // ⬅️ opsional
                ->action(function ($livewire) {
                    $filters = $livewire->tableFilters ?? [];
                    $nonce   = Str::uuid()->toString(); // cache-buster

                    $url = route('exports.pengajuans.filtered', [
                        'filters' => json_encode($filters, JSON_UNESCAPED_UNICODE),
                        '_nonce'  => $nonce,
                    ]);

                    return redirect()->to($url);
                }),
        ];
    }
}
