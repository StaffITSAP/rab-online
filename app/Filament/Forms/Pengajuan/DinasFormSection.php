<?php

namespace App\Filament\Forms\Pengajuan;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
                    Grid::make(3)->schema([
                        DatePicker::make('tgl_realisasi')
                            ->label('Tanggal Berangkat/ Realisasi')
                            ->dehydrated()
                            ->displayFormat('d F Y')
                            ->locale('id'),
                        DatePicker::make('tgl_pulang')
                            ->label('Tanggal Pulang')
                            ->dehydrated()
                            ->displayFormat('d F Y')
                            ->locale('id'),
                        TextInput::make('jam')
                            ->label('Jam')
                            ->placeholder('Masukkan Jam (contoh: 13:30)')
                            ->required()
                            ->extraAttributes(['id' => 'jamPicker']),
                        TextInput::make('jml_personil')
                            ->label('Jumlah Personil')
                            ->placeholder('Silahkan isi personil'),
                        Toggle::make('menggunakan_teknisi')
                            ->label('Menggunakan Teknisi')
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                        Toggle::make('lampiran')
                            ->label('Lampirkan File/Gambar')
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
                                ->placeholder('Contoh: Jumlah PIC 1,2,3...')
                                ->nullable(),

                            TextInput::make('jml_hari')
                                ->label('QTY/Hari')
                                ->numeric()
                                ->minValue(1)
                                ->nullable()
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $pic = (int) $get('pic');
                                    $jmlHari = (int) $state;
                                    $harga = (int) str_replace('.', '', $get('harga_satuan'));
                                    $set('subtotal', $jmlHari && $harga ? ($pic * $jmlHari) * $harga : null);
                                }),

                            TextInput::make('harga_satuan')
                                ->label('Harga')
                                ->placeholder('Contoh: 500.000')
                                ->required()
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
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 2),
        ];
    }
}
