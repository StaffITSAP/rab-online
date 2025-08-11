<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranMarcommPromosiResource\Pages;
use App\Filament\Resources\LampiranMarcommPromosiResource\RelationManagers;
use App\Models\LampiranMarcommPromosi;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LampiranMarcommPromosiResource extends Resource
{
    protected static ?string $model = LampiranMarcommPromosi::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Lampiran Promosi';
    protected static ?string $pluralLabel = 'Lampiran Promosi';
    protected static ?string $slug = 'lampiran-promosi';
    protected static ?int $navigationSort = 110;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'id')
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload Lampiran (PDF & Image)')
                    ->multiple()
                    ->preserveFilenames()
                    ->directory('lampiran/assets')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240),

                Forms\Components\TextInput::make('original_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
                Tables\Columns\ViewColumn::make('preview')
                    ->label('Preview Lampiran')
                    ->view('filament.tables.columns.lampiran-preview')
                    ->viewData(fn($record) => ['record' => $record]), // ⬅️ ini agar bisa pakai $record,
                Tables\Columns\TextColumn::make('original_name')->label('Nama Lampiran')->limit(40),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListLampiranMarcommPromosis::route('/'),
            'create' => Pages\CreateLampiranMarcommPromosi::route('/create'),
            'edit' => Pages\EditLampiranMarcommPromosi::route('/{record}/edit'),
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
