<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PromosiFormSection
{
    public static function schema(): array
    {
        return [
            Section::make('Pengajuan RAB Marcomm Promosi')
                ->schema([
                    Repeater::make('pengajuan_marcomm_promosis')
                        ->label('Form RAB Marcomm Promosi')
                        ->relationship('pengajuan_marcomm_promosis') // relasi hasMany di model Pengajuan
                        ->schema([

                            TextInput::make('deskripsi')
                                ->label('Deskripsi')
                                ->placeholder('Masukkan deskripsi item')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $qty = (int) $state;
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    $set('subtotal', $qty && $harga ? $qty * $harga : null);
                                }),

                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->placeholder('Contoh: 500000')
                                ->required()
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    $component->state($state ? number_format((int) $state, 0, ',', '.') : null);
                                })
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $qty = (int) $get('qty');
                                    $harga = (int) str_replace('.', '', $state);
                                    $set('subtotal', $qty && $harga ? $qty * $harga : null);
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->required()
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(function (Get $get) {
                                    $qty = (int) $get('qty');
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    return $qty && $harga ? $qty * $harga : null;
                                })
                                ->columnSpanFull(),

                        ])
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Hitung ulang total_biaya dari semua item
                            $total = collect($state)->sum(fn($item) => (int) ($item['subtotal'] ?? 0));
                            $set('total_biaya', $total);
                        })
                        ->columns(2)
                        ->addActionLabel('Tambah Item Marcomm Promosi')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Marcomm Promosi'),

                    Forms\Components\TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                        ->columnSpanFull()
                        ->default(0),
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->placeholder('Tuliskan keterangan tambahan di sini...')
                        ->rows(4)
                        ->maxLength(65535) // batas default untuk TEXT MySQL
                        ->columnSpanFull(),
                    Toggle::make('lampiran_marcomm_promosi')
                        ->label('Tambahkan Lampiran Marcomm Promosi')
                        ->default(false)
                        ->onIcon('heroicon-s-check')
                        ->offIcon('heroicon-s-x-mark')
                        ->onColor('success')
                        ->offColor('danger')
                        ->reactive()
                        ->dehydrated(), // ⬅️ penting agar nilainya dikirim ke backend

                    Repeater::make('lampiranPromosi')

                        ->label('Lampiran RAB Marcomm Promosi')
                        ->relationship('lampiranPromosi')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('File Lampiran (PDF/Gambar)')
                                ->disk('public')
                                ->directory('lampiran-promosi')
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
                        ->visible(fn($get) => $get('lampiran_marcomm_promosi') === true),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 4),
        ];
    }
}
