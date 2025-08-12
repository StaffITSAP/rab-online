<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommKebutuhanKartuResource\Pages;
use App\Filament\Resources\PengajuanMarcommKebutuhanKartuResource\RelationManagers;
use App\Models\PengajuanMarcommKebutuhanKartu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanMarcommKebutuhanKartuResource extends Resource
{
    protected static ?string $model = PengajuanMarcommKebutuhanKartu::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Detail RAB Marcomm';
    protected static ?string $label = 'Kartu Nama dan ID Card';
    protected static ?string $pluralLabel = 'Kartu Nama dan ID Card';
    protected static ?string $slug = 'detail-kartu';
    protected static ?int $navigationSort = 111;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('kartu_nama')
                    ->label('Kartu Nama')
                    ->inline(false)
                    ->default(false)
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('id_card')
                    ->required()
                    ->label('ID Card')
                    ->inline(false)
                    ->default(false)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kartu_nama')->label('Kartu Nama'),
                Tables\Columns\TextColumn::make('id_card')->label('ID Card'),
            ])
            ->filters([
                //
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                ]),
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
            'index' => Pages\ListPengajuanMarcommKebutuhanKartus::route('/'),
            'create' => Pages\CreatePengajuanMarcommKebutuhanKartu::route('/create'),
            'edit' => Pages\EditPengajuanMarcommKebutuhanKartu::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false; // Tidak bisa membuat data baru
    }
    public static function canEdit($record): bool
    {
        return false; // Matikan tombol Edit
    }

    public static function canDelete($record): bool
    {
        return false; // Matikan tombol Delete
    }
}
