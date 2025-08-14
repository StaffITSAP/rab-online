<?php

namespace App\Filament\Forms\Pengajuan;

use Closure;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                            Forms\Components\Textarea::make('nama_barang')
                                ->label('Nama Barang')
                                ->required(),

                            Forms\Components\Textarea::make('keperluan')
                                ->label('Keperluan')
                                ->required(),

                            Forms\Components\Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->required(),

                            TextInput::make('tipe_barang')
                                ->label('Tipe / Satuan')
                                ->required(),

                            TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->dehydrated()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $jumlah = (int) $state;
                                    $harga = (int) str_replace('.', '', $get('harga_unit'));
                                    $set('subtotal', $jumlah && $harga ? $jumlah * $harga : null);
                                }),

                            TextInput::make('harga_unit')
                                ->label('Harga Unit')
                                ->required()
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    $component->state($state ? number_format((int) $state, 0, ',', '.') : null);
                                })
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $jumlah = (int) $get('jumlah');
                                    $harga = (int) str_replace('.', '', $state);
                                    $set('subtotal', $jumlah && $harga ? $jumlah * $harga : null);
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->required()
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(function (Get $get) {
                                    $jumlah = (int) $get('jumlah');
                                    $harga = (int) str_replace('.', '', $get('harga_unit'));
                                    return $jumlah && $harga ? $jumlah * $harga : null;
                                })
                                ->columnSpanFull(),
                        ])
                        ->afterStateUpdated(function ($state, callable $set) {
                            $total = collect($state)->sum(fn($item) => (int) ($item['subtotal'] ?? 0));
                            $set('total_biaya', $total);
                        })
                        ->columns(3)
                        ->addActionLabel('Tambah Barang')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Asset/Inventaris'),

                    TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->default(0),
                    Grid::make(2)->schema([
                        Toggle::make('asset_teknisi')
                            ->label('Keperluan Teknisi')
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                        Toggle::make('lampiran_asset')
                            ->label('Tambahkan Lampiran Asset/Inventaris')
                            ->default(false)
                            ->reactive()
                            ->dehydrated(), // ⬅️ penting agar nilainya dikirim ke backend
                    ]),

                    Repeater::make('lampiranAssets')

                        ->label('Lampiran RAB Asset/Inventaris')
                        ->relationship('lampiranAssets')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('File Lampiran (PDF/Gambar)')
                                ->disk('public')
                                ->directory('lampiran-assets')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                ->maxSize(10240)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if (is_array($state) && count($state) > 0) {
                                        $file = array_key_first($state);
                                        if ($file) {
                                            $filename = pathinfo($file, PATHINFO_BASENAME);
                                            $set('original_name', $filename);
                                        }
                                    }
                                }),

                            TextInput::make('original_name')
                                ->label('Nama Lampiran')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->defaultItems(1)
                        ->visible(fn($get) => $get('lampiran_asset') === true),
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->placeholder('Tuliskan keterangan tambahan di sini...')
                        ->rows(4)
                        ->maxLength(65535) // batas default untuk TEXT MySQL
                        ->columnSpanFull(),

                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 1),
        ];
    }
}
