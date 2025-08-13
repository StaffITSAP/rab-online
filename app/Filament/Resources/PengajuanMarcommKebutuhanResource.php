<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanMarcommKebutuhanResource\Pages;
use App\Models\PengajuanMarcommKebutuhan;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanMarcommKebutuhanResource extends Resource
{
    protected static ?string $model = PengajuanMarcommKebutuhan::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Detail Pengajuan RAB';
    protected static ?string $label = 'Kebutuhan Pusat dan Sales';
    protected static ?string $pluralLabel = 'Kebutuhan Pusat dan Sales';
    protected static ?string $slug = 'marcomm-kebutuhan';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('deskripsi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('harga_satuan')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('tipe')
                    ->maxLength(255),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('deskripsi')->searchable(),
                Tables\Columns\TextColumn::make('qty'),
                Tables\Columns\TextColumn::make('harga_satuan')->money('IDR'),
                Tables\Columns\TextColumn::make('subtotal')->money('IDR'),
                Tables\Columns\TextColumn::make('tipe'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
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
            'index' => Pages\ListPengajuanMarcommKebutuhans::route('/'),
            'create' => Pages\CreatePengajuanMarcommKebutuhan::route('/create'),
            'edit' => Pages\EditPengajuanMarcommKebutuhan::route('/{record}/edit'),
        ];
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
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Superadmin lihat semua
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

        // Cari ID pengajuan di mana user adalah approver
        $pengajuanIdsSebagaiApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return parent::getEloquentQuery()
            ->whereHas('pengajuan', function ($query) use ($user, $pengajuanIdsSebagaiApprover) {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereIn('id', $pengajuanIdsSebagaiApprover);
            });
    }
}
