<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommPromosiResource\Pages;
use App\Models\PengajuanMarcommPromosi;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class PengajuanMarcommPromosiResource extends Resource
{
    protected static ?string $model = PengajuanMarcommPromosi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rss';
    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $label = 'Pengajuan Marcomm Promosi';
    protected static ?string $pluralLabel = 'Pengajuan Marcomm Promosi';
    protected static ?string $slug = 'marcomm-promosi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('deskripsi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('harga_satuan')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')->searchable(),
                Tables\Columns\TextColumn::make('qty'),
                Tables\Columns\TextColumn::make('harga_satuan')->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')->money('IDR'),
                Tables\Columns\TextColumn::make('keterangan')->limit(30),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanMarcommPromosis::route('/'),
            'create' => Pages\CreatePengajuanMarcommPromosi::route('/create'),
            'edit' => Pages\EditPengajuanMarcommPromosi::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        return true; // Semua user bisa lihat list
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

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
