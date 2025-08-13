<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranMarcommKegiatanResource\Pages;
use App\Models\LampiranMarcommKegiatan;
use App\Models\PengajuanStatus;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class LampiranMarcommKegiatanResource extends Resource
{
    protected static ?string $model = LampiranMarcommKegiatan::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Event/Kegiatan';
    protected static ?string $pluralLabel = 'Event/Kegiatan';
    protected static ?string $slug = 'lampiran-marcomm-kegiatan';
    protected static ?int $navigationSort = 112;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pengajuan_id')
                    ->relationship('pengajuan', 'id')
                    ->required(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload Lampiran (PDF & Image)')
                    ->preserveFilenames()
                    ->directory('lampiran/marcomm-kegiatan')
                    ->disk('public')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(10240) // max 10 MB
                    ->required(),

                Forms\Components\TextInput::make('original_name')
                    ->label('Nama Lampiran')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pengajuan.no_rab')
                    ->label('No RAB')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ViewColumn::make('preview')
                    ->label('Preview Lampiran')
                    ->view('filament.tables.columns.lampiran-preview')
                    ->viewData(fn($record) => ['record' => $record]),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('Nama Lampiran')
                    ->limit(40),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLampiranMarcommKegiatans::route('/'),
            'create' => Pages\CreateLampiranMarcommKegiatan::route('/create'),
            'edit' => Pages\EditLampiranMarcommKegiatan::route('/{record}/edit'),
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
