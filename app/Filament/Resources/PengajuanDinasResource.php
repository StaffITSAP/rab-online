<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanDinasResource\Pages;
use App\Filament\Resources\PengajuanDinasResource\RelationManagers;
use App\Models\PengajuanDinas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanDinasResource extends Resource
{
    protected static ?string $model = PengajuanDinas::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $label = 'Pengajuan Perjalanan Dinas';
    protected static ?string $pluralLabel = 'Pengajuan Perjalanan Dinas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab')
                    ->required(),

                Forms\Components\Select::make('deskripsi')
                    ->options([
                        'Transportasi' => 'Transportasi',
                        'Makan' => 'Makan',
                        'Lain-lain' => 'Lain-lain',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\TextInput::make('pic'),
                Forms\Components\TextInput::make('jml_hari')->numeric(),
                Forms\Components\TextInput::make('harga_satuan')->numeric(),
                Forms\Components\TextInput::make('subtotal')->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->searchable(),
                Tables\Columns\TextColumn::make('deskripsi'),
                Tables\Columns\TextColumn::make('keterangan')->limit(30),
                Tables\Columns\TextColumn::make('pic'),
                Tables\Columns\TextColumn::make('jml_hari'),
                Tables\Columns\TextColumn::make('harga_satuan')->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')->money('IDR'),
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
            'index' => Pages\ListPengajuanDinas::route('/'),
            'create' => Pages\CreatePengajuanDinas::route('/create'),
            'edit' => Pages\EditPengajuanDinas::route('/{record}/edit'),
        ];
    }
}
