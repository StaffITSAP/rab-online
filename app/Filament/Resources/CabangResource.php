<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CabangResource\Pages;
use App\Filament\Resources\CabangResource\RelationManagers;
use App\Models\Cabang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CabangResource extends Resource
{
    protected static ?string $model = Cabang::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Cabang';
    protected static ?string $modelLabel = 'Cabang';
    protected static ?string $slug = 'cabang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode')->required()->unique()->label('Nama Cabang'),
                Forms\Components\TextInput::make('nama')->required()->label('Nama Kota'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kode')
            ->columns([
                Tables\Columns\TextColumn::make('kode')->sortable()->searchable()->label('Nama Cabang'),

            ])
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
            'index' => Pages\ListCabangs::route('/'),
            'create' => Pages\CreateCabang::route('/create'),
            'edit' => Pages\EditCabang::route('/{record}/edit'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->roles->contains('name', 'superadmin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->roles->contains('name', 'superadmin');
    }
}
