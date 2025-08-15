<?php

namespace App\Filament\Resources;

use App\Models\Pengajuan;
use App\Models\PengajuanStatus;
use Carbon\Carbon;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class PenggunaanTeknisiResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Detail Perjalanan Dinas';
    protected static ?string $label = 'Penggunaan Teknisi';
    protected static ?string $pluralLabel = 'Penggunaan Teknisi';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'penggunaan-teknisi';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),

                TextColumn::make('tgl_realisasi')
                    ->label('Tanggal Realisasi')
                    ->formatStateUsing(fn($state) => $state ? Carbon::parse($state)->translatedFormat('d F Y') : '-'),

                TextColumn::make('nama_dinas')
                    ->label('Nama Dinas')
                    ->getStateUsing(function ($record) {
                        return optional(
                            $record->dinasActivities()
                                ->orderBy('id', 'asc')
                                ->first()
                        )->nama_dinas ?? '-';
                    }),
                Tables\Columns\TextColumn::make('dinas_personils_list')
                    ->label('Personil')
                    ->html()
                    ->getStateUsing(function ($record) {
                        // Ambil semua nama_personil dari relasi
                        $names = $record->dinasPersonils()
                            ->pluck('nama_personil') // langsung ambil kolom
                            ->filter()               // hilangkan yang null/kosong
                            ->toArray();

                        // Gabungkan dengan koma sebagai pemisah
                        return count($names) ? implode(', ', $names) : '-';
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'selesai' => 'success',
                        'ditolak' => 'danger',
                        'expired' => 'danger',
                        'menunggu' => 'warning',
                        default => 'gray',
                    })
                    ->description(function ($record) {
                        if ($record->status === 'ditolak') {
                            $status = $record->statuses()
                                ->where('is_approved', false)
                                ->latest('approved_at')
                                ->first();
                            return $status?->alasan_ditolak ? 'Alasan: ' . $status->alasan_ditolak : null;
                        }
                        if (in_array($record->status, ['selesai', 'menunggu'])) {
                            $status = $record->statuses()
                                ->where('is_approved', true)
                                ->latest('approved_at')
                                ->first();
                            return $status?->catatan_approve ? 'Catatan: ' . $status->catatan_approve : null;
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('menggunakan_teknisi')
                    ->label('Request Teknisi')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        true => 'success',
                        false => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak'),
                TextColumn::make('no_rab')->label('No RAB'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Setujui')
                        ->label('Setujui')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('catatan_approve')->label('Catatan (opsional)')->rows(2),
                        ])
                        ->action(function ($record, array $data) {
                            $user = auth()->user();

                            // Tidak bisa setujui jika expired
                            if ($record->status === 'expired') {
                                Notification::make()
                                    ->title('Pengajuan sudah expired dan tidak dapat disetujui.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // Cegah pengaju menyetujui pengajuan sendiri
                            if ($record->user_id === $user->id) {
                                Notification::make()
                                    ->title('Anda tidak dapat menyetujui pengajuan Anda sendiri.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $status = $record->statuses()->where('user_id', $user->id)->first();

                            if ($status && is_null($status->is_approved)) {
                                $status->update([
                                    'is_approved' => true,
                                    'approved_at' => now(),
                                    'catatan_approve' => $data['catatan_approve'] ?? null,
                                ]);

                                // Cek jika semua sudah approve
                                $total = $record->statuses()->count();
                                $approved = $record->statuses()->where('is_approved', true)->count();

                                if ($approved === $total) {
                                    $record->update(['status' => 'selesai']);
                                }

                                Notification::make()
                                    ->title('Pengajuan berhasil disetujui.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(
                            fn($record) =>
                            $record->status !== 'expired' && // tambahkan pengecekan ini
                                $record->statuses()
                                ->where('user_id', auth()->id())
                                ->whereNull('is_approved')
                                ->exists()
                                && $record->user_id !== auth()->id()
                        ),

                    Tables\Actions\Action::make('Tolak')
                        ->label('Tolak')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('alasan_ditolak')->label('Alasan Penolakan')->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $user = auth()->user();

                            // Tidak bisa tolak jika expired
                            if ($record->status === 'expired') {
                                Notification::make()
                                    ->title('Pengajuan sudah expired dan tidak dapat ditolak.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            if ($record->user_id === $user->id) {
                                Notification::make()->title('Anda tidak dapat menolak pengajuan Anda sendiri.')->danger()->send();
                                return;
                            }
                            $status = $record->statuses()->where('user_id', $user->id)->first();
                            if ($status && is_null($status->is_approved)) {
                                $status->update([
                                    'is_approved' => false,
                                    'approved_at' => now(),
                                    'alasan_ditolak' => $data['alasan_ditolak'],
                                ]);
                                $record->update(['status' => 'ditolak']);
                                Notification::make()->title('Pengajuan telah ditolak.')->danger()->send();
                            }
                        })
                        ->visible(
                            fn($record) =>
                            $record->status !== 'expired' && // tambahkan pengecekan ini
                                $record->statuses()
                                ->where('user_id', auth()->id())
                                ->whereNull('is_approved')
                                ->exists()
                                && $record->user_id !== auth()->id()
                        ),
                    Tables\Actions\Action::make('open_expired')
                        ->label('Buka Expired')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(function ($record) {
                            $user = auth()->user();
                            // Bisa jika:
                            // 1. Superadmin
                            if ($user && $user->hasRole('superadmin')) {
                                return $record->status === 'expired';
                            }
                            // 2. Atau Koordinator yang jadi approver pengajuan ini
                            if (
                                $user
                                && $user->hasRole('koordinator')
                                && $record->statuses()
                                ->where('user_id', $user->id)
                                ->exists()
                            ) {
                                return $record->status === 'expired';
                            }
                            // selain itu, tidak boleh
                            return false;
                        })
                        ->action(function ($record) {
                            $record->update(['status' => 'menunggu']);
                            \Filament\Notifications\Notification::make()
                                ->title('Status berhasil diubah menjadi menunggu!')
                                ->success()
                                ->send();
                        }),


                    Tables\Actions\ViewAction::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->slideOver()
                        ->modalWidth('screen') // full screen width untuk slideOver
                        ->modalHeading('Preview RAB PDF')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(fn($record) => view('filament.components.pdf-preview', [
                            'record' => $record->load(['lampiran', 'lampiranAssets', 'lampiranDinas', 'lampiranPromosi', 'lampiranKebutuhan']),
                            'url' => URL::signedRoute('pengajuan.pdf.preview', $record),
                        ]))
                        ->closeModalByClickingAway(false),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn($record) => route('pengajuan.pdf.download', $record), shouldOpenInNewTab: false),
                    Tables\Actions\DeleteAction::make()
                        ->visible(function ($record) {
                            $user = auth()->user();
                            $isSuperadmin = $user && $user->hasRole('superadmin');
                            $isOwner = $user && $record->user_id === $user->id;

                            // Jika status 'selesai', hanya superadmin yang bisa hapus
                            if ($record->status === 'selesai') {
                                return $isSuperadmin;
                            }

                            // Jika belum selesai, owner atau superadmin boleh hapus
                            return $isOwner || $isSuperadmin;
                        })
                        ->requiresConfirmation()
                        ->form(form: [
                            Textarea::make('deletion_reason')
                                ->label('Alasan Penghapusan')
                                ->required()
                        ])
                        ->action(function (Model $record, array $data): void {
                            $record->deletion_reason = $data['deletion_reason'];
                            $record->save();
                            $record->delete();
                        }),
                    Tables\Actions\RestoreAction::make()
                        ->visible(
                            fn($record) =>
                            auth()->user()->hasRole('superadmin') && $record->trashed()
                        ),

                ]),
            ])->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns)
            ->defaultSort('tgl_realisasi', 'desc')->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'draft'    => 'Draft',
                        'ditolak'  => 'Ditolak',
                        'selesai'  => 'Selesai',
                        'expired'  => 'Expired',
                    ])
                    ->indicator('Status'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(2) // atur jumlah kolom di atas
            ->filtersFormWidth(MaxWidth::FourExtraLarge) // kalau perlu lebih lebar
            ->filtersFormMaxHeight('400px');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\PenggunaanTeknisiResource\Pages\ListPenggunaanTeknisis::route('/'),
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

        $query = parent::getEloquentQuery()->where('menggunakan_teknisi', true);

        if ($user->hasRole('superadmin')) {
            return $query;
        }

        $pengajuanIdsApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return $query->where(function ($q) use ($user, $pengajuanIdsApprover) {
            $q->where('user_id', $user->id)
                ->orWhereIn('id', $pengajuanIdsApprover);
        });
    }
}
