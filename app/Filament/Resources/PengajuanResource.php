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
use Filament\Notifications\Notification;

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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_rab')->label('No RAB'),
                Tables\Columns\TextColumn::make('user.name')->label('Pemohon'),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->translatedFormat('d F Y H:i')),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn($state) => match ($state) {
                    'selesai' => 'success',
                    'ditolak' => 'danger',
                    'expired' => 'gray',
                    'menunggu' => 'warning',
                }),
                Tables\Columns\TextColumn::make('total_biaya')->money('IDR', true),
                Tables\Columns\TextColumn::make('tipeRAB.nama')->label('Tipe RAB'),
            ])
            ->defaultSort('created_at', 'desc') // ⬅️ Tambahkan ini
            ->filters([
                //
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
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
        $record->update([
            'total_biaya' => $record->pengajuan_assets()->sum('subtotal'),
        ]);
    }
}
