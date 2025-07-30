<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanAssetResource\Pages;
use App\Filament\Resources\PengajuanAssetResource\RelationManagers;
use App\Models\PengajuanAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanAssetResource extends Resource
{
    protected static ?string $model = PengajuanAsset::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $label = 'Pengajuan Asset/Inventaris';
    protected static ?string $pluralLabel = 'Pengajuan Asset/Inventaris';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('pengajuan_id')
                ->label('No RAB')
                ->relationship('pengajuan', 'no_rab')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('nama_barang')->required(),
            Forms\Components\TextInput::make('tipe_barang')->required(),
            Forms\Components\TextInput::make('jumlah')->numeric()->required(),
            Forms\Components\TextInput::make('harga_unit')->numeric()->required(),
            Forms\Components\TextInput::make('subtotal')->numeric()->required(),
            Forms\Components\TextInput::make('keperluan')->required(),
            Forms\Components\Textarea::make('keterangan')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->searchable(),
                Tables\Columns\TextColumn::make('nama_barang')->searchable(),
                Tables\Columns\TextColumn::make('tipe_barang'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('harga_unit')->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')->money('IDR'),
                Tables\Columns\TextColumn::make('keperluan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),
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
            'index' => Pages\ListPengajuanAssets::route('/'),
            'create' => Pages\CreatePengajuanAsset::route('/create'),
            'edit' => Pages\EditPengajuanAsset::route('/{record}/edit'),
        ];
    }
   public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Default saja, TIDAK pakai withTrashed()
        return parent::getEloquentQuery();
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
}
