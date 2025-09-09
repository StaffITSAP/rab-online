<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StagingLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'stagingLogs';

    protected static ?string $title = 'Log Perubahan Staging';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_name')
                    ->label('User')
                    ->disabled(),
                Forms\Components\TextInput::make('user_role')
                    ->label('Role')
                    ->disabled(),
                Forms\Components\TextInput::make('old_staging')
                    ->label('Staging Lama')
                    ->formatStateUsing(fn($state) => $state ? \App\Enums\StagingEnum::from($state)->label() : '-')
                    ->disabled(),
                Forms\Components\TextInput::make('new_staging')
                    ->label('Staging Baru')
                    ->formatStateUsing(fn($state) => \App\Enums\StagingEnum::from($state)->label())
                    ->disabled(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_name')
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_role')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'servis' => 'primary',
                        'sales' => 'info',
                        'admin_service' => 'success',
                        'manager' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('old_staging_label')
                    ->label('Staging Lama')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_staging_label')
                    ->label('Staging Baru')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Request' => 'gray',
                        'Cek Kerusakan' => 'blue',
                        'Ada Biaya' => 'orange',
                        'Close' => 'green',
                        'Approve' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                // Filter untuk menampilkan log yang dihapus - HANYA untuk superadmin
                Tables\Filters\TrashedFilter::make()
                    ->label('Status Log')
                    ->placeholder('Log aktif')
                    ->options([
                        'withoutTrashed' => 'Log aktif',
                        'onlyTrashed' => 'Log dihapus',
                        'all' => 'Semua log',
                    ])
                    ->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Restore action untuk log yang dihapus - HANYA untuk superadmin
                Tables\Actions\RestoreAction::make()
                    ->visible(
                        fn($record): bool =>
                        $record->trashed() && auth()->user()->hasRole('superadmin')
                    ),

                // Force delete action - HANYA untuk superadmin
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(
                        fn($record): bool =>
                        $record->trashed() && auth()->user()->hasRole('superadmin')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Bulk actions hanya untuk superadmin
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Pastikan query tidak null sebelum memanggil withoutGlobalScope
        if ($query) {
            return $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        // Fallback: buat query manual jika parent::getTableQuery() mengembalikan null
        return $this->getRelationship()->getQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }
}
