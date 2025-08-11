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

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Detail RAB Marcomm';
    protected static ?string $label = 'Kartu Nama dan ID Card';
    protected static ?string $pluralLabel = 'Kartu Nama dan ID Card';
    protected static ?string $slug = 'detail-kartu';
    protected static ?int $navigationSort = 111;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListPengajuanMarcommKebutuhanKartus::route('/'),
            'create' => Pages\CreatePengajuanMarcommKebutuhanKartu::route('/create'),
            'edit' => Pages\EditPengajuanMarcommKebutuhanKartu::route('/{record}/edit'),
        ];
    }
}
