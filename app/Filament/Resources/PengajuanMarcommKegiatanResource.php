<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommKegiatanResource\Pages;
use App\Models\PengajuanMarcommKegiatan;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengajuanMarcommKegiatanResource extends Resource
{
    protected static ?string $model = PengajuanMarcommKegiatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $label = 'Event/Kegiatan';
    protected static ?string $pluralLabel = 'Event/Kegiatan';
    protected static ?string $slug = 'marcomm-kegiatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab') // ganti no_rab sesuai kolom yang ada di tabel pengajuans
                    ->required(),

                Forms\Components\Select::make('deskripsi')
                    ->options([
                        'Biaya Inap Hotel' => 'Biaya Inap Hotel',
                        'Biaya Konsumsi' => 'Biaya Konsumsi',
                        'Biaya Transportasi' => 'Biaya Transportasi',
                        'Biaya Lain-lain' => 'Biaya Lain-lain',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\TextInput::make('pic'),
                Forms\Components\TextInput::make('jml_hari')->numeric(),
                Forms\Components\TextInput::make('harga_satuan')->numeric(),
                Forms\Components\TextInput::make('subtotal')->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')
                    ->label('No RAB')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deskripsi'),
                Tables\Columns\TextColumn::make('keterangan')->limit(30),
                Tables\Columns\TextColumn::make('pic'),
                Tables\Columns\TextColumn::make('jml_hari'),
                Tables\Columns\TextColumn::make('harga_satuan')->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')->money('IDR'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanMarcommKegiatans::route('/'),
            'create' => Pages\CreatePengajuanMarcommKegiatan::route('/create'),
            'edit' => Pages\EditPengajuanMarcommKegiatan::route('/{record}/edit'),
        ];
    }

    // Semua user bisa lihat list
    public static function canViewAny(): bool
    {
        return true;
    }

    // Tidak bisa create dari resource ini
    public static function canCreate(): bool
    {
        return false;
    }

    // Tidak bisa edit langsung dari resource ini
    public static function canEdit($record): bool
    {
        return false;
    }

    // Tidak bisa delete
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Superadmin lihat semua
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

        // Cari ID pengajuan di mana user adalah approver
        $pengajuanIdsSebagaiApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return parent::getEloquentQuery()
            ->whereHas('pengajuan', function ($query) use ($user, $pengajuanIdsSebagaiApprover) {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereIn('id', $pengajuanIdsSebagaiApprover);
            });
    }
}
