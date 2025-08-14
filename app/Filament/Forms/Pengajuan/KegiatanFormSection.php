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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;

class KegiatanFormSection
{
    public static function schema(): array
    {
        return [
            Section::make('Pengajuan RAB Marcomm Kegiatan')
                ->schema([
                    Grid::make(4)->schema([
                        DatePicker::make('tgl_realisasi')
                            ->label('Tanggal Kegiatan / Realisasi')
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
                            ->label('Jumlah Peserta')
                            ->default(1)
                            ->placeholder('Silahkan isi peserta'),

                        TextInput::make('lokasi')
                            ->label('Lokasi Kegiatan')
                            ->placeholder('Masukkan lokasi kegiatan'),
                    ]),

                    // RAB Kegiatan
                    Repeater::make('marcommKegiatans')
                        ->label('Form RAB Marcomm Kegiatan')
                        ->relationship('marcommKegiatans')
                        ->schema([
                            Select::make('deskripsi')
                                ->label('Deskripsi')
                                ->options([
                                    'Biaya Hotel' => 'Biaya Hotel',
                                    'Biaya Konsumsi' => 'Biaya Konsumsi',
                                    'Biaya Transportasi' => 'Biaya Transportasi',
                                    'Biaya Lain-lain' => 'Biaya Lain-lain',
                                ])
                                ->columnSpanFull()
                                ->required(),

                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->placeholder('Contoh: Sewa ruangan hotel...')
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
                                    $set('subtotal', $pic && $jmlHari && $harga ? ($pic * $jmlHari) * $harga : null);
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
                            // Hitung total_biaya dari semua item
                            $total = collect($state)->sum(fn($item) => (int) ($item['subtotal'] ?? 0));
                            $set('total_biaya', $total);
                        })
                        ->columns(3)
                        ->addActionLabel('Tambah Item Kegiatan')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Kegiatan'),

                    // Total biaya
                    Forms\Components\TextInput::make('total_biaya')
                        ->label('Total Biaya')
                        ->disabled()
                        ->dehydrated()
                        ->prefix('Rp ')
                        ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : null)
                        ->columnSpanFull()
                        ->default(0),

                    // Toggle lampiran
                    Grid::make(3)->schema([
                        Toggle::make('tim_pusat')
                            ->label('Pusat')
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                        Toggle::make('tim_cabang')
                            ->label('Cabang')
                            ->inline(false)
                            ->default(false)
                            ->reactive(),
                        Toggle::make('lampiran_kegiatan')
                            ->label('Tambahkan Lampiran Kegiatan')
                            ->default(false)
                            ->inline(false)
                            ->reactive()
                            ->dehydrated(), // ⬅️ penting agar nilainya dikirim ke backend
                    ]),
                    // Lampiran repeater
                    Repeater::make('marcommKegiatanPusats')
                        ->label('Form Lampiran Marcomm Kegiatan Pusat')
                        ->relationship('marcommKegiatanPusats')
                        ->schema([
                            Textarea::make('nama')
                                ->label('Nama')
                                ->placeholder('Nama peserta')
                                ->required(),

                            Select::make('gender')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ])
                                ->required(),
                        ])
                        ->columns(2)
                        ->addActionLabel('Tambah')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Peserta')
                        ->visible(fn($get) => $get('tim_pusat') === true),
                    Repeater::make('marcommKegiatanCabangs')
                        ->label('Form Pengajuan Marcomm Kegiatan Cabang')
                        ->relationship('marcommKegiatanCabangs')
                        ->schema([
                            TextArea::make('cabang')
                                ->label('Cabang')
                                ->placeholder('Nama Cabang / -')
                                ->required(),

                            TextArea::make('nama')
                                ->label('Nama')
                                ->placeholder('Nama Peserta / -')
                                ->required(),

                            Select::make('gender')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ])
                                ->required(),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Peserta Cabang')
                        ->columnSpanFull()
                        ->defaultItems(1)
                        ->itemLabel('Detail Peserta Cabang')
                        ->visible(fn($get) => $get('tim_cabang') === true),
                    Repeater::make('lampiranKegiatan')
                        ->label('Lampiran RAB Kegiatan')
                        ->relationship('lampiranKegiatan')
                        ->schema([
                            FileUpload::make('file_path')
                                ->label('File Lampiran (PDF/Gambar)')
                                ->disk('public')
                                ->directory('lampiran-kegiatan')
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
                        ->visible(fn($get) => $get('lampiran_kegiatan') === true),
                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->placeholder('Tuliskan keterangan tambahan di sini...')
                        ->rows(4)
                        ->maxLength(65535) // batas default untuk TEXT MySQL
                        ->columnSpanFull(),
                ])
                ->visible(fn(Get $get) => $get('tipe_rab_id') == 3),
        ];
    }
}
