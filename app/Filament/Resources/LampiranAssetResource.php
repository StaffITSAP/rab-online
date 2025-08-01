<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranAssetResource\Pages;
use App\Models\LampiranAsset;
use App\Models\PengajuanStatus;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;


class LampiranAssetResource extends Resource
{
    protected static ?string $model = LampiranAsset::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Lampiran Asset';
    protected static ?string $pluralLabel = 'Lampiran Asset';
    protected static ?int $navigationSort = 99;


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
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

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('pengajuan.id')->label('Pengajuan'),
            Tables\Columns\ImageColumn::make('file_path')
                ->label('Preview')
                ->disk('public')
                ->height(50)
                ->circular(false),
            Tables\Columns\TextColumn::make('original_name')->limit(40),
        ])->filters([
            Tables\Filters\TrashedFilter::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLampiranAssets::route('/'),
            'create' => Pages\CreateLampiranAsset::route('/create'),
            'edit'   => Pages\EditLampiranAsset::route('/{record}/edit'),
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
