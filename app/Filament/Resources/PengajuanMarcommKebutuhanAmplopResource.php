<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource\Pages;
use App\Filament\Resources\PengajuanMarcommKebutuhanAmplopResource\RelationManagers;
use App\Models\PengajuanMarcommKebutuhanAmplop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanMarcommKebutuhanAmplopResource extends Resource
{
    protected static ?string $model = PengajuanMarcommKebutuhanAmplop::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Detail RAB Marcomm';
    protected static ?string $label = 'Amplop';
    protected static ?string $pluralLabel = 'Amplop';
    protected static ?string $slug = 'detail-amplop';
    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('pengajuan_id')
                ->relationship('pengajuan', 'no_rab')
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('cabang')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('jumlah')
                ->numeric()
                ->required()
                ->minValue(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('cabang'),
                Tables\Columns\TextColumn::make('jumlah'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
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
            'index' => Pages\ListPengajuanMarcommKebutuhanAmplops::route('/'),
            'create' => Pages\CreatePengajuanMarcommKebutuhanAmplop::route('/create'),
            'edit' => Pages\EditPengajuanMarcommKebutuhanAmplop::route('/{record}/edit'),
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
