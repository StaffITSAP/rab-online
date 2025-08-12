<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Models\Pengajuan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;
use App\Filament\Forms\Pengajuan\BasePengajuanForm;
use App\Filament\Forms\Pengajuan\AssetFormSection;
use App\Filament\Forms\Pengajuan\DinasFormSection;
use App\Filament\Forms\Pengajuan\PromosiFormSection;
use App\Filament\Forms\Pengajuan\KebutuhanFormSection;
use App\Models\PengajuanStatus;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationLabel = 'Pengajuan RAB';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    public static function form(Form $form): Form
    {
        return $form->schema([
            ...BasePengajuanForm::schema(),
            ...AssetFormSection::schema(),
            ...DinasFormSection::schema(),
            ...PromosiFormSection::schema(),
            ...KebutuhanFormSection::schema(),

            TextInput::make('total_biaya')
                ->label('Total Biaya')
                ->hidden()
                ->disabled()
                ->dehydrated(false)
                ->formatStateUsing(
                    fn($state, $record) =>
                    number_format($record?->total_biaya ?? 0, 0, ',', '.')
                ),
        ])->disabled(fn($livewire) => $livewire->isReadOnly ?? false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('no_rab')->label('No RAB')
                    ->disabled(fn($record) => $record && $record->status === 'selesai')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon')->searchable(),
                Tables\Columns\TextColumn::make('total_biaya')->money('IDR', true),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y H:i')),
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
                    })->searchable(),

                TextColumn::make('pending_approvers')
                    ->label('Belum Disetujui Oleh')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $pending = $record->statuses()
                            ->whereNull('is_approved')
                            ->with('user')
                            ->get();

                        $names = $pending->pluck('user.name')->filter()->toArray();

                        // Gabungkan pakai <br> agar nama tampil ke bawah
                        return count($names) ? implode('<br>', $names) : '-';
                    }),


                TextColumn::make('approved_info')
                    ->label('Disetujui / Ditolak Oleh (Tanggal)')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $approvedStatuses = $record->statuses()
                            ->whereNotNull('is_approved')
                            ->with('user')
                            ->orderBy('approved_at')
                            ->get();

                        if ($approvedStatuses->isEmpty()) {
                            return '-';
                        }

                        $list = $approvedStatuses->map(function ($status) {
                            $name = e($status->user?->name ?? '-');
                            $approvedText = $status->is_approved ? 'Disetujui' : 'Ditolak';
                            $date = $status->approved_at
                                ? \Carbon\Carbon::parse($status->approved_at)->translatedFormat('d F Y H:i')
                                : '-';
                            return "<div>{$name} ({$approvedText})<br><span style=\"font-size:13px;color:#aaa;\">{$date}</span></div>";
                        })->implode('');

                        return $list;
                    }),

                Tables\Columns\TextColumn::make('menggunakan_teknisi')
                    ->label('Teknisi')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        true => 'success',
                        false => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak'),
                Tables\Columns\TextColumn::make('use_car')
                    ->label('Mobil')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        true => 'success',
                        false => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak'),
                Tables\Columns\TextColumn::make('tipeRAB.nama')->label('Tipe RAB'),
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
            ->filters([
                TrashedFilter::make()
                    ->visible(fn() => Auth::user()->hasRole('superadmin')), // gunakan ini jika pakai Spatie
                TernaryFilter::make('menggunakan_teknisi')
                    ->label('Menggunakan Teknisi')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->queries(
                        true: fn($query) => $query->where('menggunakan_teknisi', 1),
                        false: fn($query) => $query->where('menggunakan_teknisi', 0),
                        blank: fn($query) => $query // untuk semua data
                    ),
                TernaryFilter::make('use_car')
                    ->label('Mobil')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak')
                    ->queries(
                        true: fn($query) => $query->where('use_car', 1),
                        false: fn($query) => $query->where('use_car', 0),
                        blank: fn($query) => $query // untuk semua data
                    ),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->hasRole('superadmin')),

                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn() => auth()->user()?->hasRole('superadmin')),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->hasRole('superadmin')), // jika ingin sekalian
                ]),
            ])->actionsPosition(\Filament\Tables\Enums\ActionsPosition::BeforeColumns);
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }

    public static function afterSave(Form $form): void
    {
        $record = $form->getRecord();
        $total = 0;

        if ($record->tipe_rab_id == 1) {
            $total = $record->pengajuan_assets()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 2) {
            $total = $record->pengajuan_dinas()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 4) {
            $total = $record->pengajuan_marcomm_promosis()->sum('subtotal');
        } elseif ($record->tipe_rab_id == 5) {
            $total = $record->pengajuan_marcomm_kebutuhans()->sum('subtotal');
        } // tambahkan elseif lagi jika ada tipe lain

        $record->update(['total_biaya' => $total]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        // Update expired pengajuan terlebih dahulu sebelum menampilkan data
        static::updateExpiredPengajuan();

        // Superadmin boleh lihat semua, tapi hanya status menunggu
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery()
                ->where('status', 'menunggu');
        }

        // Pengajuan yang user ini adalah approver DAN belum approve/tolak (is_approved masih null)
        $pengajuanIdsBelumApprove = \App\Models\PengajuanStatus::where('user_id', $user->id)
            ->whereNull('is_approved')
            ->pluck('pengajuan_id')
            ->toArray();

        return parent::getEloquentQuery()
            ->where('status', 'menunggu') // ⬅️ CUKUP TAMBAH BARIS INI
            ->where(function ($query) use ($user, $pengajuanIdsBelumApprove) {
                $query
                    // Tampilkan jika sebagai pemilik pengajuan
                    ->where('user_id', $user->id)
                    // Atau tampilkan jika sebagai approver yang belum approve/tolak
                    ->orWhere(function ($q) use ($pengajuanIdsBelumApprove, $user) {
                        $q->whereIn('id', $pengajuanIdsBelumApprove)
                            ->where('user_id', '!=', $user->id); // agar tidak tampil dobel jika owner sekaligus approver
                    });
            });
    }

    private static function updateExpiredPengajuan(): void
    {
        $today = Carbon::now()->startOfDay();
        Pengajuan::where('status', 'menunggu')
            ->whereNotNull('tgl_realisasi')
            ->whereDate('tgl_realisasi', '<=', $today->copy()->subDays(1))
            ->update(['status' => 'expired']);
    }
}
