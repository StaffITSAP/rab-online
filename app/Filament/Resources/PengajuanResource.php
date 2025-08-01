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
use App\Models\PengajuanStatus;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Builder;

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

        ])->disabled(fn($livewire) => $livewire->isReadOnly ?? false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('no_rab')
                    ->disabled(fn($record) => $record && $record->status === 'selesai')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                Tables\Columns\TextColumn::make('total_biaya')->money('IDR', true),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y H:i')),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn($state) => match ($state) {
                    'selesai' => 'success',
                    'ditolak' => 'danger',
                    'expired' => 'gray',
                    'menunggu' => 'warning',
                }),
                TextColumn::make('approved_by')
                    ->label('Disetujui Oleh')
                    ->getStateUsing(function ($record) {
                        $latestApproved = $record->statuses()
                            ->where('is_approved', true)
                            ->latest('approved_at')
                            ->with('user')
                            ->first();

                        return $latestApproved?->user?->name ?? '-';
                    }),

                TextColumn::make('approved_at')
                    ->label('Tanggal Disetujui')
                    ->getStateUsing(function ($record) {
                        $latestApproved = $record->statuses()
                            ->where('is_approved', true)
                            ->latest('approved_at')
                            ->first();

                        return $latestApproved?->approved_at
                            ? \Carbon\Carbon::parse($latestApproved->approved_at)->format('d/m/Y H:i')
                            : '-';
                    }),
                Tables\Columns\TextColumn::make('menggunakan_teknisi')
                    ->label('Teknisi')
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
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Setujui')
                        ->label('Setujui')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $user = auth()->user();

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
                        ->action(function ($record) {
                            $user = auth()->user();

                            // Cegah pengaju menolak sendiri
                            if ($record->user_id === $user->id) {
                                Notification::make()
                                    ->title('Anda tidak dapat menolak pengajuan Anda sendiri.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $status = $record->statuses()->where('user_id', $user->id)->first();

                            if ($status && is_null($status->is_approved)) {
                                $status->update([
                                    'is_approved' => false,
                                    'approved_at' => now(),
                                ]);

                                $record->update(['status' => 'ditolak']);

                                Notification::make()
                                    ->title('Pengajuan telah ditolak.')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(
                            fn($record) =>
                            $record->statuses()
                                ->where('user_id', auth()->id())
                                ->whereNull('is_approved')
                                ->exists()
                                && $record->user_id !== auth()->id()
                        ),
                    Tables\Actions\ViewAction::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->slideOver() // lebih cocok untuk full lebar di HP
                        ->modalHeading('Preview RAB PDF')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(fn($record) => view('filament.components.pdf-preview', [
                            'record' => $record->load(['lampiran', 'lampiranAssets']),
                            'url' => URL::signedRoute('pengajuan.pdf.preview', $record),
                        ])),
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
                        ->visible(fn($record) => $record->trashed()),

                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            $user = auth()->user();
                            // Bulk hanya diaktifkan untuk superadmin (opsional, demi safety)
                            return $user && $user->hasRole('superadmin');
                        }),
                    Tables\Actions\RestoreBulkAction::make(),
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
        } // tambahkan elseif lagi jika ada tipe lain

        $record->update(['total_biaya' => $total]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Superadmin boleh lihat semua
        if ($user->hasRole('superadmin')) {
            return parent::getEloquentQuery();
        }

        // Ambil ID pengajuan yang user ini adalah approver
        $pengajuanIdsSebagaiApprover = PengajuanStatus::where('user_id', $user->id)
            ->pluck('pengajuan_id')
            ->toArray();

        return parent::getEloquentQuery()
            ->where(function ($query) use ($user, $pengajuanIdsSebagaiApprover) {
                $query
                    // sebagai pemilik pengajuan
                    ->where('user_id', $user->id)
                    // atau sebagai approver di pengajuan lain
                    ->orWhereIn('id', $pengajuanIdsSebagaiApprover);
            });
    }
}
