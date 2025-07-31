<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Filament\Resources\PersetujuanResource\RelationManagers;
use App\Models\Persetujuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

class PersetujuanResource extends Resource
{
    protected static ?string $model = Persetujuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Persetujuan';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $label = 'Persetujuan';
    protected static ?string $slug = 'persetujuan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Pengajuan Persetujuan')
                ->description('Pilih user dan daftarkan siapa saja yang harus menyetujui')
                ->schema([
                    Select::make('user_id')
                        ->label('User yang Diajukan')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Repeater::make('approvers')
                        ->label('Daftar Approver')
                        ->relationship('approvers')
                        ->schema([
                            Select::make('approver_id')
                                ->label('Pilih Approver')
                                ->options(fn() => User::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->addActionLabel('Tambah Approver')
                        ->minItems(1)
                        ->columns(1)
                        ->required(),
                    Grid::make(4)->schema([
                        Toggle::make('menggunakan_teknisi')
                            ->label('Menggunakan Teknisi')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $approvers = collect($get('approvers'));
                                $koordinator = User::role('koordinator teknisi')->first();
                                if (!$koordinator) return;

                                if ($state) {
                                    if (!$approvers->contains('approver_id', $koordinator->id)) {
                                        $approvers->push(['approver_id' => $koordinator->id]);
                                    }
                                } else {
                                    $approvers = $approvers->reject(fn($item) => $item['approver_id'] == $koordinator->id);
                                }

                                $set('approvers', $approvers->values()->all());
                            }),
                        Toggle::make('use_pengiriman')
                            ->label('Pengiriman Barang/Gudang')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $approvers = collect($get('approvers'));
                                $koordinator = User::role('koordinator gudang')->first();
                                if (!$koordinator) return;

                                if ($state) {
                                    if (!$approvers->contains('approver_id', $koordinator->id)) {
                                        $approvers->push(['approver_id' => $koordinator->id]);
                                    }
                                } else {
                                    $approvers = $approvers->reject(fn($item) => $item['approver_id'] == $koordinator->id);
                                }

                                $set('approvers', $approvers->values()->all());
                            }),

                        Toggle::make('use_manager')
                            ->label('Persetujuan Manager')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $approvers = collect($get('approvers'));
                                $manager = User::role('manager')->first();
                                if (!$manager) return;

                                if ($state) {
                                    if (!$approvers->contains('approver_id', $manager->id)) {
                                        $approvers->push(['approver_id' => $manager->id]);
                                    }
                                } else {
                                    $approvers = $approvers->reject(fn($item) => $item['approver_id'] == $manager->id);
                                }

                                $set('approvers', $approvers->values()->all());
                            }),

                        Toggle::make('use_direktur')
                            ->label('Persetujuan Direktur')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $approvers = collect($get('approvers'));
                                $direktur = User::role('direktur')->first();
                                if (!$direktur) return;

                                if ($state) {
                                    if (!$approvers->contains('approver_id', $direktur->id)) {
                                        $approvers->push(['approver_id' => $direktur->id]);
                                    }
                                } else {
                                    $approvers = $approvers->reject(fn($item) => $item['approver_id'] == $direktur->id);
                                }

                                $set('approvers', $approvers->values()->all());
                            }),
                    ]),


                ])
                ->columns(1)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User'),

                TextColumn::make('approvers')
                    ->label('Approver')
                    ->getStateUsing(function ($record) {
                        return $record->approvers
                            ->map(fn($item) => $item->approver?->name)
                            ->filter()
                            ->join(', ');
                    }),

                TextColumn::make('menggunakan_teknisi')
                    ->label('Teknisi')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_pengiriman')
                    ->label('Gudang/Pengiriman')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_manager')
                    ->label('Manager')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_direktur')
                    ->label('Direktur')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPersetujuans::route('/'),
            'create' => Pages\CreatePersetujuan::route('/create'),
            'edit' => Pages\EditPersetujuan::route('/{record}/edit'),
        ];
    }
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('superadmin');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('superadmin');
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('approvers');
    }
}
