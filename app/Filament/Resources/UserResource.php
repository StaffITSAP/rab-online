<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'User';
    protected static ?string $modelLabel = 'User';
    protected static ?string $slug = 'user';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Fieldset::make('Data Pengguna')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('username')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('email')->email()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                        ->required(fn(string $context) => $context === 'create')
                        ->label('Password'),
                    Toggle::make('is_active')->label('Aktif')->default(true),
                ]),

            Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->required()
                ->preload()
                ->options(fn () => Role::all()->pluck('name', 'id'))
                ->default(fn ($record) => $record?->roles->pluck('id')->toArray())
                ->afterStateHydrated(function ($component, $state) {
                    $component->state($state);
                })
                ->dehydrateStateUsing(fn ($state) => $state ?? [])
                ->saveRelationshipsUsing(function ($record, $state) {
                    $record->roles()->sync($state);
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('username')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn($state) => $state ? 'success' : 'danger'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
