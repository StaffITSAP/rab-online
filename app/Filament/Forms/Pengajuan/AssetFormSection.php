<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
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
                            TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->placeholder('Contoh: 1, 2, 3')
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
                                ->placeholder('Contoh: 1.000.000')
                                ->required()
                                ->dehydrated() // supaya disimpan
                                ->prefix('Rp ')
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    // Format angka saat edit (jika ada)
                                    $component->state($state ? number_format((int) $state, 0, ',', '.') : null);
                                })
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $jumlah = (int) $get('jumlah');
                                    $harga = (int) str_replace('.', '', $state);
                                    $set('subtotal', $jumlah && $harga ? $jumlah * $harga : null);
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled() // Tidak bisa diedit manual
                                ->required()
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->formatStateUsing(function ($state) {
                                    return $state ? number_format((int) $state, 0, ',', '.') : null;
                                })
                                ->dehydrateStateUsing(function (Get $get) {
                                    $jumlah = (int) $get('jumlah');
                                    $harga = (int) str_replace('.', '', $get('harga_unit'));
                                    return $jumlah && $harga ? $jumlah * $harga : null;
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
                    Forms\Components\TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->default(0),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 1),
        ];
    }
}
