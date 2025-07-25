<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisiResource\Pages;
use App\Models\Divisi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class DivisiResource extends Resource
{
    protected static ?string $model = Divisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Divisi';
    protected static ?string $modelLabel = 'Divisi';
    protected static ?string $slug = 'divisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDivisis::route('/'),
            'create' => Pages\CreateDivisi::route('/create'),
            'edit' => Pages\EditDivisi::route('/{record}/edit'),
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
