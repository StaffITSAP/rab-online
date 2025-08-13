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

class DinasFormSection
{
    public static function schema(): array
    {
        return [
            Section::make('Pengajuan RAB Perjalanan Dinas')
                ->schema([
                    Grid::make(4)->schema([
                        DatePicker::make('tgl_realisasi')
                            ->label('Tanggal Berangkat/ Realisasi')
                            ->dehydrated()
                            ->default(now())
                            ->displayFormat('d F Y')
                            ->locale('id'),
                        DatePicker::make('tgl_pulang')
                            ->label('Tanggal Pulang')
                            ->dehydrated()
                            ->default(now())
                            ->displayFormat('d F Y')
                            ->locale('id'),
                        TextInput::make('jam')
                            ->label('Jam')
                            ->placeholder('Masukkan Jam (contoh: 13:30)')
                            ->required()
                            ->default('08:30')
                            ->extraAttributes(['id' => 'jamPicker']),
                        TextInput::make('jml_personil')
                            ->label('Jumlah Personil')
                            ->default(1)
                            ->placeholder('Silahkan isi personil'),
                        Toggle::make('use_car')
                            ->label('Request Mobil')
                            ->inline(false)
                            ->default(false)
                            ->reactive()
                            ->helperText('Digunakan untuk request penggunaan Mobil.'),
                        Toggle::make('use_pengiriman')
                            ->label('Pengiriman Barang/Gudang')
                            ->inline(false)
                            ->default(false)
                            ->reactive()
                            ->helperText('Bisa juga di gunakan untuk request penggunaan Mobil dan Sopir.'),
                        Toggle::make('menggunakan_teknisi')
                            ->label('Menggunakan Teknisi/Survey')
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                    ]),
                    Repeater::make('pengajuan_dinas')
                        ->label('Form RAB Perjalanan Dinas')
                        ->relationship('pengajuan_dinas')
                        ->schema([
                            Select::make('deskripsi')
                                ->label('Deskripsi')
                                ->options([
                                    'Transportasi' => 'Transportasi',
                                    'Makan' => 'Makan',
                                    'Lain-lain' => 'Lain-lain',
                                ])
                                ->columnSpanFull()
                                ->required(),

                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Contoh: Uang makan harian...')
                                ->nullable()
                                ->columnSpanFull(),

                            TextInput::make('pic')
                                ->label('PIC')
                                ->default(1)
                                ->placeholder('Contoh: Jumlah PIC 1,2,3...')
                                ->nullable(),

                            TextInput::make('jml_hari')
                                ->label('QTY/Hari')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->nullable()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $pic = (int) $get('pic');
                                    $jmlHari = (int) $state;
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    $set('subtotal', $jmlHari && $harga ? ($pic * $jmlHari) * $harga : null);
                                }),

                            TextInput::make('harga_satuan')
                                ->label('Harga')
                                ->placeholder('Contoh: 500000')
                                ->required()
                                ->default(0)
                                ->dehydrated()
                                ->prefix('Rp ')
                                ->extraAttributes(['class' => 'currency-input'])
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    $component->state($state ? number_format((int) $state, 0, ',', '.') : null);
                                })
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $pic = (int) $get('pic');
                                    $jmlHari = (int) $get('jml_hari');
                                    $harga = (int) str_replace('.', '', $state);
                                    $set('subtotal', $pic && $jmlHari && $harga ? ($pic * $jmlHari) * $harga : null);
                                }),

                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->disabled()
                                ->required()
                                ->dehydrated()
                                ->default(0)
                                ->prefix('Rp ')
                                ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(function (Get $get) {
                                    $pic = (int) $get('pic');
                                    $jmlHari = (int) $get('jml_hari');
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    return $pic && $jmlHari && $harga ? ($pic * $jmlHari) * $harga : null;
                                })
                                ->columnSpanFull(),
                        ])
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Hitung ulang total_biaya dari semua item
                            $total = collect($state)->sum(fn($item) => (int) ($item['subtotal'] ?? 0));
                            $set('total_biaya', $total);
                        })
                        ->columns(3)
                        ->addActionLabel('Tambah Perjalanan Dinas')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Perjalanan Dinas'),
                    Repeater::make('dinasActivities')
                        ->label('Form Activity Perjalanan Dinas')
                        ->relationship('dinasActivities')
                        ->schema([
                            TextArea::make('no_activity')
                                ->label('No Activity')
                                ->placeholder('2505-000001 / Jika tidak tahu di isi - ')
                                ->required(),
                            TextArea::make('nama_dinas')
                                ->label('Nama Dinas')
                                ->placeholder('Nama Dinas')
                                ->required(),
                            TextArea::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Vist/Follow Up/Dll')
                                ->required(),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Activity')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Activity Perjalanan Dinas'),

                    Repeater::make('dinasPersonils')
                        ->label('Form Personil Perjalanan Dinas')
                        ->relationship('dinasPersonils')
                        ->schema([
                            TextInput::make('nama_personil')
                                ->label('Nama Personil')
                                ->placeholder('Masukkan Nama Personil')
                                ->required()
                                ->maxLength(250),
                        ])
                        ->addActionLabel('Tambah Personil')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Personil Perjalanan Dinas'),

                    Forms\Components\TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                        ->columnSpanFull()
                        ->default(0),
                    Toggle::make('lampiran_dinas')
                        ->label('Tambahkan Lampiran Perjalanan Dinas')
                        ->default(false)
                        ->reactive()
                        ->dehydrated(), // ⬅️ penting agar nilainya dikirim ke backend

                    Repeater::make('lampiranDinas')

                        ->label('Lampiran RAB Perjalanan Dinas')
                        ->relationship('lampiranDinas')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('File Lampiran (PDF/Gambar)')
                                ->disk('public')
                                ->directory('lampiran-dinas')
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
                        ->visible(fn($get) => $get('lampiran_dinas') === true),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 2),
        ];
    }
}
