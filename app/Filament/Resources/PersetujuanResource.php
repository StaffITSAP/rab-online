<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Filament\Resources\PersetujuanResource\RelationManagers;
use App\Models\Persetujuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;


class PersetujuanResource extends Resource
{
    protected static ?string $model = Persetujuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Persetujuan';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $label = 'Persetujuan';
    protected static ?string $slug = 'persetujuan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('User yang Diajukan')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            Select::make('approver_id')
                ->label('Yang Menyetujui')
                ->relationship('approver', 'name')
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('approver.name')->label('Approver'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPersetujuans::route('/'),
            'create' => Pages\CreatePersetujuan::route('/create'),
            'edit' => Pages\EditPersetujuan::route('/{record}/edit'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('superadmin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('superadmin');
    }
}
