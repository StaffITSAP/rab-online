<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Number;

class AssetFormSection
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Pengajuan RAB Asset/Inventaris')
                ->schema([
                    Forms\Components\Repeater::make('pengajuan_assets')
                        ->label('Form RAB Asset/Inventaris')
                        ->relationship('pengajuan_assets')
                        ->schema([
                            Forms\Components\TextArea::make('nama_barang')
                                ->label('Nama Barang')
                                ->placeholder('Contoh: Laptop, Proyektor, Meja, Kursi')
                                ->required(),
                            Forms\Components\Textarea::make('keperluan')
                                ->label('Keperluan')
                                ->placeholder('Contoh: Untuk kegiatan seminar, pelatihan, atau keperluan kantor')
                                ->required(),
                            Forms\Components\Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Contoh: Barang baru, bekas, atau kondisi khusus lainnya')
                                ->required(),
                            Forms\Components\TextInput::make('tipe_barang')
                                ->label('Tipe / Satuan')
                                ->placeholder('Contoh: pcs, unit, set')
                                ->required(),
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->placeholder('Contoh: 1, 2, 3')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->dehydrated()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $set('subtotal', ($state ?? 0) * ($get('harga_unit') ?? 0));
                                }),
                            Forms\Components\TextInput::make('harga_unit')
    ->label('Harga Unit')
    ->required()
    ->numeric()
    ->dehydrated()
    ->extraAttributes([
        'class' => 'currency-input',
        'inputmode' => 'numeric',
    ])
    ->formatStateUsing(fn ($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
    ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', $state) : 0),
                            Forms\Components\TextInput::make('subtotal')
    ->label('Subtotal')
    ->placeholder('Auto-hitung dari Jumlah x Harga Unit')
    ->disabled()
    ->required()
    ->numeric()
    ->dehydrated()
    ->formatStateUsing(function ($state) {
        return $state ? number_format((int) $state, 0, ',', '.') : null;
    })
    ->dehydrateStateUsing(function (Get $get) {
        $jumlah = (int) str_replace('.', '', $get('jumlah'));
        $harga = (int) str_replace('.', '', $get('harga_unit'));
        return $jumlah * $harga;
    })
    ->columnSpanFull(),
                        ])
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Hitung ulang total_biaya dari semua item
                            $total = collect($state)->sum(fn($item) => (int) ($item['subtotal'] ?? 0));
                            $set('total_biaya', $total);
                        })
                        ->columns(3)
                        ->addActionLabel('Tambah Barang')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Asset/Inventaris'),
                    Forms\Components\Hidden::make('total_biaya')
                        ->dehydrated()
                        ->default(0),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 1),
        ];
    }
}
