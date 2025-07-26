<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
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
            TextInput::make('name')->required()->label('Nama'),
            TextInput::make('username')->required(),
            TextInput::make('email')->email(),
            TextInput::make('no_hp')->label('No HP')->tel(),
            Select::make('company')->options([
                'sap' => 'CV Solusi Arya Prima',
                'dinatek' => 'CV Dinatek Jaya Lestari',
                'ssm' => 'PT Sinergi Subur Makmur',
            ])->label('Perusahaan'),
            TextInput::make('password')
                ->password()
                ->label('Password')
                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                ->required()
                ->maxLength(255),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->label('Role'),
            FileUpload::make('userStatus.signature_path')
                ->label('Tanda Tangan')
                ->directory('signatures')
                ->image()
                ->imagePreviewHeight('100')
                ->preserveFilenames(),
            Select::make('userStatus.is_active')
                ->options([
                    true => 'Aktif',
                    false => 'Nonaktif',
                ])
                ->label('Status Akun'),
            Select::make('userStatus.cabang_id')
                ->relationship('userStatus.cabang', 'kode')
                ->label('Cabang'),
            Select::make('userStatus.divisi_id')
                ->relationship('userStatus.divisi', 'nama')
                ->label('Divisi'),
            Select::make('userStatus.atasan_id')
                ->relationship('userStatus.atasan', 'name')
                ->label('Atasan Langsung'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('username'),
                TextColumn::make('email'),
                TextColumn::make('no_hp')->label('No HP'),
                TextColumn::make('userStatus.atasan.name')->label('Atasan'),
                TextColumn::make('company')
                    ->label('Perusahaan')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'sap' => 'CV Solusi Arya Prima',
                        'dinatek' => 'CV Dinatek Jaya Lestari',
                        'ssm' => 'PT Sinergi Subur Makmur',
                        default => '-',
                    }),
                TextColumn::make('userStatus.signature_path')
                    ->label('Tanda Tangan')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $url = asset('storage/' . $state);
                        return "<img src='$url' alt='Tanda Tangan' height='30'>";
                    })
                    ->html(),
                TextColumn::make('userStatus.cabang.kode')->label('Cabang'),
                TextColumn::make('userStatus.divisi.nama')->label('Divisi'),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(', ')
                    ->color(fn($state) => match ($state) {
                        'superadmin' => 'danger',
                        'manager' => 'primary',
                        'user' => 'gray',
                        default => 'info',
                    }),

                TextColumn::make('userStatus.is_active')
                    ->label('Status Akun')
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
