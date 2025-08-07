<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanDinasActivityResource\Pages;
use App\Filament\Resources\PengajuanDinasActivityResource\RelationManagers;
use App\Models\PengajuanDinasActivity;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\PengajuanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanDinasActivityResource extends Resource
{
    protected static ?string $model = PengajuanDinasActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-forward';
    protected static ?string $navigationGroup = 'Detail Perjalanan Dinas';
    protected static ?string $label = 'Perjalanan Dinas Activity';
    protected static ?string $pluralLabel = 'Perjalanan Dinas Activity';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'no_rab')
                    ->searchable()
                    ->required(),
                TextInput::make('no_activity')
                    ->required(),
                TextInput::make('nama_dinas')
                    ->required(),
                Textarea::make('keterangan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                TextColumn::make('no_activity'),
                TextColumn::make('nama_dinas'),
                TextColumn::make('keterangan')->limit(30),
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
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
            'index' => Pages\ListPengajuanDinasActivities::route('/'),
            'create' => Pages\CreatePengajuanDinasActivity::route('/create'),
            'edit' => Pages\EditPengajuanDinasActivity::route('/{record}/edit'),
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

        // Superadmin boleh melihat semua
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

        // Ambil ID pengajuan yang user ini adalah approver-nya
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
