<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use App\Models\Service;
use App\Models\ServiceItem;

class BiayaFormSection
{
    public static function schema(): array
    {
        return [
            Section::make('Pengajuan RAB Biaya Service')
                ->schema([

                    Repeater::make('pengajuan_biaya_services')
                        ->label('Form RAB Biaya Service')
                        ->relationship('pengajuan_biaya_services')
                        ->schema([

                            // Relasi service & item
                            Select::make('service_id')
                                ->label('Service')
                                ->relationship('service', 'nomer_so')
                                ->searchable()
                                ->required()
                                ->live()
                                ->options(function (Get $get, $state) {
                                    // Ambil semua service_id yang sudah dipilih di repeater
                                    $selectedServiceIds = self::getAllSelectedServiceIds($get);

                                    // Jika ada state (sedang edit), kecualikan service_id yang sedang diedit
                                    if ($state) {
                                        $selectedServiceIds = array_diff($selectedServiceIds, [(int) $state]);
                                    }

                                    // Filter service yang belum dipilih
                                    return Service::whereNotIn('id', $selectedServiceIds)
                                        ->pluck('nomer_so', 'id');
                                })
                                ->afterStateUpdated(function ($state, Set $set) {
                                    // Reset service_item_id ketika service berubah
                                    $set('service_item_id', null);
                                }),

                            Select::make('service_item_id')
                                ->label('Item / Sparepart')
                                ->options(function (Get $get) {
                                    $serviceId = $get('service_id');

                                    if (!$serviceId) {
                                        return [];
                                    }

                                    return ServiceItem::where('service_id', $serviceId)
                                        ->pluck('nama_barang', 'id');
                                })
                                ->searchable()
                                ->nullable()
                                ->disabled(fn(Get $get) => !$get('service_id')),

                            Textarea::make('deskripsi')
                                ->label('Deskripsi Biaya')
                                ->placeholder('Tuliskan detail biaya di sini...')
                                ->rows(2)
                                ->maxLength(65535)
                                ->columnSpanFull(),

                            // Jumlah
                            TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    self::calculateItemTotals($state, $get, $set);
                                    self::updateParentTotalBiaya($get, $set);
                                }),

                            // Harga Satuan
                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->prefix('Rp ')
                                ->placeholder('Contoh: 250000')
                                ->required()
                                ->default(0)
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(
                                    fn(TextInput $c, $state) =>
                                    $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                                )
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $harga = (int) str_replace('.', '', $state ?? 0);
                                    $set('harga_satuan', $harga);
                                    self::calculateItemTotals($get('jumlah'), $get, $set);
                                    self::updateParentTotalBiaya($get, $set);
                                }),

                            // Subtotal
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->prefix('Rp ')
                                ->disabled()
                                ->dehydrated()
                                ->default(0)
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(
                                    fn(TextInput $c, $state) =>
                                    $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                                )
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0),

                            // Pajak %
                            TextInput::make('pph_persen')
                                ->label('PPh %')
                                ->numeric()
                                ->nullable()
                                ->suffix('%')
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    self::calculateItemTotals($get('jumlah'), $get, $set);
                                    self::updateParentTotalBiaya($get, $set);
                                }),

                            // PPh Nominal
                            TextInput::make('pph_nominal')
                                ->label('PPh Nominal')
                                ->prefix('Rp ')
                                ->disabled()
                                ->dehydrated()
                                ->default(0)
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(
                                    fn(TextInput $c, $state) =>
                                    $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                                )
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0),

                            // DPP Jual
                            TextInput::make('dpp_jual')
                                ->label('DPP Jual')
                                ->prefix('Rp ')
                                ->disabled()
                                ->dehydrated()
                                ->default(0)
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(
                                    fn(TextInput $c, $state) =>
                                    $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                                )
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0),

                            // Total
                            TextInput::make('total')
                                ->label('Total')
                                ->prefix('Rp ')
                                ->disabled()
                                ->dehydrated()
                                ->default(0)
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(
                                    fn(TextInput $c, $state) =>
                                    $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                                )
                                ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Biaya Service')
                        ->itemLabel('Detail Biaya Service')
                        ->defaultItems(1)
                        ->columnSpanFull()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateParentTotalBiaya($get, $set);
                        })
                        ->deleteAction(function (Get $get, Set $set) {
                            self::updateParentTotalBiaya($get, $set);
                        }),

                    // Total semua biaya
                    TextInput::make('total_biaya')
                        ->label('Total Semua Biaya Service')
                        ->disabled()
                        ->dehydrated()
                        ->default(0)
                        ->prefix('Rp ')
                        ->extraAttributes(['class' => 'currency-input', 'id' => 'total_biaya_field'])
                        ->afterStateHydrated(
                            fn(TextInput $c, $state) =>
                            $c->state($state ? number_format((int) $state, 0, ',', '.') : null)
                        )
                        ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace('.', '', $state) : 0)
                        ->afterStateUpdated(function ($state, Set $set) {
                            // Simpan nilai numerik untuk database
                            $set('total_biaya', $state ? (int) str_replace('.', '', $state) : 0);
                        }),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 6),
        ];
    }

    /**
     * Menghitung total untuk setiap item
     */
    private static function calculateItemTotals($jumlah, Get $get, Set $set): void
    {
        $jumlah = (int) $jumlah;
        $harga = (int) str_replace('.', '', $get('harga_satuan') ?? 0);
        $subtotal = $jumlah * $harga;

        $pph = (float) ($get('pph_persen') ?? 0);
        $pphNominal = $pph > 0 ? (int) ($subtotal * ($pph / 100)) : 0;

        $set('subtotal', $subtotal);
        $set('pph_nominal', $pphNominal);
        $set('dpp_jual', $subtotal - $pphNominal);
        $set('total', $subtotal);
    }

    /**
     * Update total biaya di parent form
     */
    private static function updateParentTotalBiaya(Get $get, Set $set): void
    {
        $items = $get('pengajuan_biaya_services') ?? [];

        $grandTotal = 0;
        foreach ($items as $item) {
            $total = $item['total'] ?? 0;
            // Pastikan kita menggunakan nilai numerik, bukan yang sudah diformat
            if (is_string($total) && str_contains($total, '.')) {
                $total = (int) str_replace('.', '', $total);
            }
            $grandTotal += (int) $total;
        }

        // Update field total_biaya di parent form
        $set('total_biaya', $grandTotal);
    }

    /**
     * Mendapatkan semua service_id yang sudah dipilih di repeater
     */
    private static function getAllSelectedServiceIds(Get $get): array
    {
        $items = $get('pengajuan_biaya_services') ?? [];
        $selectedServiceIds = [];

        foreach ($items as $item) {
            if (!empty($item['service_id'])) {
                $selectedServiceIds[] = (int) $item['service_id'];
            }
        }

        return array_unique($selectedServiceIds);
    }
}
