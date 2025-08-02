<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampiranResource\Pages;
use App\Models\Lampiran;
use App\Models\PengajuanStatus;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;


class LampiranResource extends Resource
{
    protected static ?string $model = Lampiran::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Detail Lampiran';
    protected static ?string $label = 'Lampiran';
    protected static ?string $pluralLabel = 'Lampiran';
    protected static ?int $navigationSort = -1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('pengajuan_id')
                ->relationship('pengajuan', 'id')
                ->required(),

            Forms\Components\Toggle::make('lampiran_asset')->label('Lampiran Asset'),
            Forms\Components\Toggle::make('lampiran_dinas')->label('Lampiran Dinas'),
            Forms\Components\Toggle::make('lampiran_marcomm_kegiatan')->label('Marcomm Kegiatan'),
            Forms\Components\Toggle::make('lampiran_marcomm_kebutuhan')->label('Marcomm Kebutuhan'),
            Forms\Components\Toggle::make('lampiran_marcomm_promosi')->label('Marcomm Promosi'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('pengajuan.no_rab')->label('No RAB')->sortable()->searchable(),
            Tables\Columns\IconColumn::make('lampiran_asset')->boolean()->label('Asset'),
            Tables\Columns\IconColumn::make('lampiran_dinas')->boolean()->label('Dinas'),
            Tables\Columns\IconColumn::make('lampiran_marcomm_kegiatan')->boolean()->label('Marcomm Kegiatan'),
            Tables\Columns\IconColumn::make('lampiran_marcomm_kebutuhan')->boolean()->label('Marcomm Kebutuhan'),
            Tables\Columns\IconColumn::make('lampiran_marcomm_promosi')->boolean()->label('Marcomm Promosi'),
        ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLampirans::route('/'),
            'create' => Pages\CreateLampiran::route('/create'),
            'edit' => Pages\EditLampiran::route('/{record}/edit'),
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
