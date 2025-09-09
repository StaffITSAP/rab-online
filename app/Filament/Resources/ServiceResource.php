<?php

namespace App\Filament\Resources;

use App\Enums\StagingEnum;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Paket')
                    ->schema([
                        Forms\Components\TextInput::make('id_paket')
                            ->label('ID Paket')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nama_dinas')
                            ->label('Nama Dinas')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Kontak Informasi')
                    ->schema([
                        Forms\Components\TextInput::make('kontak')
                            ->label('Nama Kontak')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('no_telepon')
                            ->label('No. Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Barang')
                    ->schema([
                        Forms\Components\Textarea::make('kerusakan')
                            ->label('Deskripsi Kerusakan')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('noserial')
                            ->label('No. Serial')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('masih_garansi')
                            ->label('Status Garansi')
                            ->options([
                                'Y' => 'Masih Garansi',
                                'T' => 'Tidak Garansi',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Servis')
                    ->schema([
                        Forms\Components\TextInput::make('nomer_so')
                            ->label('No. Service Order')
                            ->maxLength(255),
                        Forms\Components\Select::make('staging')
                            ->label('Status Staging')
                            ->options(StagingEnum::options())
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('keterangan_staging')
                            ->label('Keterangan Staging')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('id_paket')
                    ->label('ID Paket')
                    ->searchable()
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('nama_dinas')
                    ->label('Nama Dinas')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('kontak')
                    ->label('Kontak')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('no_telepon')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('noserial')
                    ->label('No. Serial')
                    ->searchable()
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('masih_garansi')
                    ->label('Garansi')
                    ->formatStateUsing(fn(string $state): string => $state === 'Y' ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Y' ? 'success' : 'danger')
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('nomer_so')
                    ->label('No. SO')
                    ->searchable()
                    ->toggleable(true),

                // Tampilkan staging dengan badge warna
                Tables\Columns\TextColumn::make('staging_value')
                    ->label('Staging')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $enum = $state instanceof StagingEnum ? $state : StagingEnum::tryFrom((string) $state);
                        return $enum?->label() ?? '-';
                    })
                    ->color(function ($state): string {
                        $val = $state instanceof StagingEnum ? $state->value : (string) $state;
                        return match ($val) {
                            StagingEnum::REQUEST->value        => 'gray',
                            StagingEnum::CEK_KERUSAKAN->value  => 'info',
                            StagingEnum::ADA_BIAYA->value      => 'warning',
                            StagingEnum::CLOSE->value          => 'danger',
                            StagingEnum::APPROVE->value        => 'success',
                            default                            => 'secondary',
                        };
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan_staging')
                    ->label('Keterangan')
                    ->wrap() // ini built-in Filament v3, ganti truncate jadi wrap
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'whitespace-normal break-words text-left max-w-xs mx-auto',
                        'style' => 'min-width: 200px; max-width: 300px;',
                    ])
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y H:i')
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('staging')
                    ->label('Status Staging')
                    ->options(StagingEnum::options()),

                Tables\Filters\SelectFilter::make('masih_garansi')
                    ->label('Status Garansi')
                    ->options([
                        'Y' => 'Masih Garansi',
                        'T' => 'Tidak Garansi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Action custom untuk ubah staging
                    Tables\Actions\Action::make('updateStaging')
                        ->label('Ubah Staging')
                        ->color('warning')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            \Filament\Forms\Components\Select::make('staging')
                                ->label('Status Staging')
                                ->options(fn() => self::allowedStagingOptionsForCurrentUser())
                                ->required()
                                ->native(false),
                            \Filament\Forms\Components\Textarea::make('keterangan_staging')
                                ->label('Keterangan')
                                ->placeholder('Tambahkan keterangan perubahan status...')
                        ])
                        ->action(function (Service $record, array $data): void {
                            $record->staging = $data['staging'];
                            $record->keterangan_staging = $data['keterangan_staging'] ?? $record->keterangan_staging;

                            // Hook untuk status ada_biaya
                            if ($data['staging'] === StagingEnum::ADA_BIAYA->value) {
                                // TODO: kirim notifikasi ke pihak terkait
                            }

                            $record->save();

                            \Filament\Notifications\Notification::make()
                                ->title('Status Staging Diperbarui')
                                ->body('Status staging berhasil diubah menjadi: ' . StagingEnum::from($data['staging'])->label())
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Service $record): bool => self::canUpdateStaging()),

                    // Edit hanya untuk servis & superadmin (manager tidak bisa)
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->visible(fn(Service $record) => self::canEditRecord($record)),

                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->modalWidth('screen')
                        ->slideOver()
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->closeModalByClickingAway(false),

                    // Hapus: user biasa & superadmin (servis/manager tidak)
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->visible(fn(Service $record) => self::canDeleteRecord($record)),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk delete biarkan hanya superadmin (mengikuti canDeleteRecord di policy real,
                    // atau batasi di sini jika ingin)
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->hasRole('superadmin') === true),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->deferLoading()
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }

    /**
     * Data scope:
     * - servis/manager/superadmin: semua data
     * - lainnya: hanya data milik user login (user_id)
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user?->hasAnyRole(['servis', 'manager', 'superadmin'])) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()
            ->where('user_id', $user?->id ?? 0);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('staging', 'request')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    // =======================
    // Helpers (akses & opsi)
    // =======================

    /**
     * Opsi staging yang boleh dipilih oleh user saat ini.
     */
    private static function allowedStagingOptionsForCurrentUser(): array
    {
        $user = Auth::user();

        $all = StagingEnum::options();

        if ($user?->hasRole('superadmin')) {
            return $all; // semua opsi
        }

        if ($user?->hasRole('manager')) {
            // manager boleh termasuk approve
            return array_filter($all, fn($label, $value) => in_array($value, [
                StagingEnum::REQUEST->value,
                StagingEnum::CEK_KERUSAKAN->value,
                StagingEnum::ADA_BIAYA->value,
                StagingEnum::CLOSE->value,
                StagingEnum::APPROVE->value,
            ], true), ARRAY_FILTER_USE_BOTH);
        }

        if ($user?->hasRole('servis')) {
            // servis TIDAK boleh approve
            return array_filter($all, fn($label, $value) => in_array($value, [
                StagingEnum::REQUEST->value,
                StagingEnum::CEK_KERUSAKAN->value,
                StagingEnum::ADA_BIAYA->value,
                StagingEnum::CLOSE->value,
            ], true), ARRAY_FILTER_USE_BOTH);
        }

        // user biasa: tidak ada opsi (kolom juga disembunyikan)
        return [];
    }

    /**
     * Siapa yang boleh mengubah staging di tabel.
     */
    private static function canUpdateStaging(): bool
    {
        $user = Auth::user();
        return $user?->hasAnyRole(['servis', 'manager', 'superadmin']) === true;
    }

    /**
     * Aturan siapa yang boleh mengedit record (form):
     * - servis & superadmin: boleh
     * - manager & user biasa: tidak
     */
    private static function canEditRecord(Service $record): bool
    {
        $user = Auth::user();

        if ($user?->hasRole('superadmin')) {
            return true;
        }

        if ($user?->hasRole('servis')) {
            return true;
        }

        // manager & user biasa tidak boleh edit
        return false;
    }

    /**
     * Siapa yang boleh hapus:
     * - user biasa & superadmin
     * - servis & manager: tidak
     * (Kalau mau batasi user hanya bisa hapus miliknya sendiri, aktifkan pengecekan owner)
     */
    private static function canDeleteRecord(Service $record): bool
    {
        $user = Auth::user();

        if ($user?->hasRole('superadmin')) {
            return true;
        }

        // "user tidak bisa edit hanya bisa hapus"
        if (! $user?->hasAnyRole(['servis', 'manager', 'superadmin'])) {
            // opsional: pastikan hanya bisa hapus miliknya sendiri
            return (int) $record->user_id === (int) $user->id;
        }

        return false;
    }
}
