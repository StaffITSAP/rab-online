<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommKebutuhanKemejaResource\Pages;
use App\Models\PengajuanMarcommKebutuhanKemeja;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengajuanMarcommKebutuhanKemejaResource extends Resource
{
    protected static ?string $model = PengajuanMarcommKebutuhanKemeja::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Detail RAB Marcomm';
    protected static ?string $label = 'Kemeja';
    protected static ?string $pluralLabel = 'Kemeja';
    protected static ?string $slug = 'detail-kemeja';
    protected static ?int $navigationSort = 111;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')
                    ->label('No RAB')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
            ->actions([]) // Tidak ada edit/delete
            ->bulkActions([]);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanMarcommKebutuhanKemejas::route('/'),
        ];
    }
}
