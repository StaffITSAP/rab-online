<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranMarcommKegiatanPusatResource\Pages;
use App\Models\LampiranMarcommKegiatanPusat;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class LampiranMarcommKegiatanPusatResource extends Resource
{
    protected static ?string $model = LampiranMarcommKegiatanPusat::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Lampiran Marcomm Kegiatan Pusat';
    protected static ?string $pluralLabel = 'Lampiran Marcomm Kegiatan Pusat';
    protected static ?string $slug = 'lampiran-marcomm-kegiatan-pusat';
    protected static ?int $navigationSort = 112;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'id')
                    ->required(),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('gender')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLampiranMarcommKegiatanPusats::route('/'),
            'create' => Pages\CreateLampiranMarcommKegiatanPusat::route('/create'),
            'edit' => Pages\EditLampiranMarcommKegiatanPusat::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

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
