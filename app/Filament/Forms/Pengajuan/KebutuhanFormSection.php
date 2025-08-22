<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;

class KebutuhanFormSection
{
    public static function schema(): array
    {
        return [
            Section::make('Pengajuan RAB Marcomm Kebutuhan Pusat dan Sales')
                ->schema([
                    Repeater::make('pengajuan_marcomm_kebutuhans')
                        ->label('Form RAB Marcomm Kebutuhan Pusat dan Sales')
                        ->relationship('pengajuan_marcomm_kebutuhans') // relasi ke model
                        ->schema([
                            TextInput::make('deskripsi')
                                ->label('Deskripsi')
                                ->placeholder('Masukkan deskripsi kebutuhan')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('qty')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    $set('subtotal', $state && $harga ? $state * $harga : null);
                                }),
                            TextInput::make('tipe')
                                ->label('Tipe')
                                ->placeholder('Masukkan tipe kebutuhan')
                                ->maxLength(255)
                                ->nullable(),

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
                        ->columns(3)
                        ->addActionLabel('Tambah Kebutuhan')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Kebutuhan'),

                    TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                        ->columnSpanFull()
                        ->default(0),
                    Grid::make(4)->schema([
                        Toggle::make('kebutuhan_amplop')
                            ->label('Kebutuhan Amplop')
                            ->inline(false)
                            ->default(false)
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->reactive(),
                        Toggle::make('kebutuhan_kartu')
                            ->label('Kebutuhan Kartu/ID Card')
                            ->inline(false)
                            ->default(false)
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->reactive(),
                        Toggle::make('kebutuhan_kemeja')
                            ->label('Kebutuhan Kemeja')
                            ->inline(false)
                            ->default(false)
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->reactive(),
                        Toggle::make('lampiran_marcomm_kebutuhan')
                            ->label('Tambahkan Lampiran')
                            ->inline(false)
                            ->default(false)
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->reactive()
                            ->dehydrated(), // ⬅️ penting agar nilainya dikirim ke backend
                    ]),
                    Repeater::make('marcommKebutuhanAmplops')
                        ->label('Form Pengajuan Marcomm Kebutuhan Amplop')
                        ->relationship('marcommKebutuhanAmplops')
                        ->schema([
                            TextArea::make('cabang')
                                ->label('Cabang')
                                ->placeholder('Nama Cabang / Jika tidak tahu di isi - ')
                                ->required(),
                            TextArea::make('jumlah')
                                ->label('Jumlah')
                                ->placeholder('Jumlah')
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah Kebutuhan')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Kebutuhan Amplop')
                        ->visible(fn($get) => $get('kebutuhan_amplop') === true),
                    Repeater::make('marcommKebutuhanKartus')
                        ->label('Form Pengajuan Marcomm Kebutuhan Kartu Nama dan ID Card')
                        ->relationship('marcommKebutuhanKartus')
                        ->schema([
                            TextArea::make('kartu_nama')
                                ->label('Kartu Nama')
                                ->placeholder('Nama Sales / - ')
                                ->required(),
                            TextArea::make('id_card')
                                ->label('ID Card')
                                ->placeholder('Nama Sales / -')
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah Kebutuhan')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Kebutuhan Kartu')
                        ->visible(fn($get) => $get('kebutuhan_kartu') === true),
                    Repeater::make('marcommKebutuhanKemejas')
                        ->label('Form Pengajuan Marcomm Kebutuhan Kemeja')
                        ->relationship('marcommKebutuhanKemejas')
                        ->schema([
                            TextArea::make('nama')
                                ->label('Nama')
                                ->placeholder('Nama')
                                ->required(),
                            TextArea::make('ukuran')
                                ->label('Ukuran')
                                ->placeholder('S,M,L,XL / Jika tidak tahu di isi -')
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah Kebutuhan')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Kebutuhan Kemeja')
                        ->visible(fn($get) => $get('kebutuhan_kemeja') === true),
                    Repeater::make('lampiranKebutuhan')
                        ->label('Lampiran RAB Kebutuhan Pusat/Sales')
                        ->relationship('lampiranKebutuhan')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('File Lampiran (PDF/Gambar)')
                                ->disk('public')
                                ->directory('lampiran-kebutuhan')
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
                        ->visible(fn($get) => $get('lampiran_marcomm_kebutuhan') === true),
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->placeholder('Tuliskan keterangan tambahan di sini...')
                        ->rows(4)
                        ->maxLength(65535) // batas default untuk TEXT MySQL
                        ->columnSpanFull(),
                ])

                ->visible(fn(Get $get) => $get('tipe_rab_id') == 5),
        ];
    }
}
