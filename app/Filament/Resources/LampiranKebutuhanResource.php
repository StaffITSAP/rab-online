<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranKebutuhanResource\Pages;
use App\Models\LampiranKebutuhan;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class LampiranKebutuhanResource extends Resource
{
    protected static ?string $model = LampiranKebutuhan::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Lampiran Kebutuhan';
    protected static ?string $pluralLabel = 'Lampiran Kebutuhan';
    protected static ?string $slug = 'lampiran-kebutuhan';
    protected static ?int $navigationSort = 111;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'id')
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->disk('public')
                    ->directory('lampiran_marcomm_kebutuhan')
                    ->required(),
                Forms\Components\TextInput::make('original_name')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.id')->label('ID Pengajuan'),
                Tables\Columns\TextColumn::make('original_name'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLampiranKebutuhans::route('/'),
            'create' => Pages\CreateLampiranKebutuhan::route('/create'),
            'edit' => Pages\EditLampiranKebutuhan::route('/{record}/edit'),
        ];
    }
}
