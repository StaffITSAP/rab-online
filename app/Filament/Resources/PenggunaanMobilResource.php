<?php

namespace App\Filament\Resources;

use App\Models\Pengajuan;
use App\Models\PengajuanStatus;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;


class PenggunaanMobilResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Detail Perjalanan Dinas';
    protected static ?string $label = 'Penggunaan Mobil';
    protected static ?string $pluralLabel = 'Penggunaan Mobil';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug ='penggunaan-mobil';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                TextColumn::make('realisasi')
                    ->label('Tanggal Realisasi')
                    ->getStateUsing(function ($record) {
                        // Ambil tanggal dari tgl_realisasi
                        $tanggal = $record->tgl_realisasi
                            ? Carbon::parse($record->tgl_realisasi)->translatedFormat('d F Y')
                            : '-';
                        // Ambil jam dari kolom jam
                        $jam = $record->jam ?? ' ';
                        // Gabungkan
                        return "{$tanggal} {$jam}";
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        'expired' => 'danger',
                        'menunggu' => 'warning',
                    })
                    ->description(function ($record) {
                        // Jika status ditolak, tampilkan alasan
                        if ($record->status === 'ditolak') {
                            $status = $record->statuses()
                                ->where('is_approved', false)
                                ->latest('approved_at')
                                ->first();
                            return $status?->alasan_ditolak ? 'Alasan: ' . $status->alasan_ditolak : null;
                        }
                        // Jika status selesai/disetujui, tampilkan catatan approve terakhir (jika ada)
                        if ($record->status === 'selesai' || $record->status === 'menunggu') {
                            $status = $record->statuses()
                                ->where('is_approved', true)
                                ->latest('approved_at')
                                ->first();
                            return $status?->catatan_approve ? 'Catatan: ' . $status->catatan_approve : null;
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('use_car')
                    ->label('Request Mobil')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        true => 'success',
                        false => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak'),
                TextColumn::make('no_rab')->label('No RAB'),
            ])
            ->defaultSort('tgl_realisasi', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PenggunaanMobilResource\Pages\ListPenggunaanMobils::route('/'),
        ];
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

        $query = parent::getEloquentQuery()->where('use_car', true);

        // Superadmin atau koordinator gudang lihat semua
        if ($user->hasRole(['superadmin', 'koordinator gudang'])) {
            return $query;
        }

        // Selain itu hanya lihat pengajuannya sendiri atau yang dia approve
        $pengajuanIdsApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return $query->where(function ($q) use ($user, $pengajuanIdsApprover) {
            $q->where('user_id', $user->id)
                ->orWhereIn('id', $pengajuanIdsApprover);
        });
    }
}
