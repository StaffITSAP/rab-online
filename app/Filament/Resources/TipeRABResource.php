<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipeRABResource\Pages;
use App\Models\TipeRab;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TipeRABResource extends Resource
{
    protected static ?string $model = TipeRab::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Tipe RAB';
    protected static ?string $modelLabel = 'Tipe RAB';
    protected static ?string $slug = 'tipe-rab';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')->searchable()->sortable(),
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
            'index' => Pages\ListTipeRABS::route('/'),
            'create' => Pages\CreateTipeRAB::route('/create'),
            'edit' => Pages\EditTipeRAB::route('/{record}/edit'),
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
