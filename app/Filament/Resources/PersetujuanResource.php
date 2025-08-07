<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Models\Persetujuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
                        ->required()
                        ->default(Auth::id())
                        ->disabled(fn() => !Auth::user()->hasRole('superadmin')),

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
                        self::autoToggle('menggunakan_teknisi', 'koordinator teknisi'),
                        self::autoToggle('asset_teknisi', 'rt'),
                        self::autoToggle('use_pengiriman', 'koordinator gudang'),
                        self::autoToggle('use_car', 'koordinator gudang'),
                        self::autoToggle('use_manager', 'manager'),
                        self::autoToggle('use_direktur', 'direktur'),
                        self::autoToggle('use_owner', 'owner'),
                    ]),
                ])
                ->columns(1)
        ]);
    }

    public static function autoToggle(string $field, string $role)
    {
        return Toggle::make($field)
            ->label(ucwords(str_replace('_', ' ', $field)))
            ->default(false)
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($role) {
                $approvers = collect($get('approvers'));
                $user = User::role($role)->first();
                if (!$user) return;

                if ($state) {
                    if (!$approvers->contains('approver_id', $user->id)) {
                        $approvers->push(['approver_id' => $user->id]);
                    }
                } else {
                    $approvers = $approvers->reject(fn($item) => $item['approver_id'] == $user->id);
                }

                $set('approvers', $approvers->values()->all());
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('user_id')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('approvers')
                    ->label('Approver')
                    ->getStateUsing(function ($record) {
                        return $record->approvers
                            ->map(fn($item) => $item->approver?->name)
                            ->filter()
                            ->join(', ');
                    }),

                TextColumn::make('menggunakan_teknisi')->label('Teknisi')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('asset_teknisi')->label('RT')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_pengiriman')->label('Gudang')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_car')->label('Mobil')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_manager')->label('Manager')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_direktur')->label('Direktur')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
                TextColumn::make('use_owner')->label('Owner')->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')->badge()->color(fn($state) => $state ? 'success' : 'danger'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => Auth::user()?->hasRole('superadmin') || $record->user_id === Auth::id()),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
        return Auth::check() && (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('marcomm') || Auth::user()->hasRole('rt'));
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('marcomm') || Auth::user()->hasRole('rt'));
    }

    public static function canCreate(): bool
    {
        if (Auth::user()->hasRole('superadmin')) {
            return true;
        }

        if (Auth::user()->hasRole('marcomm') || Auth::user()->hasRole('rt')) {
            // Cek apakah sudah punya persetujuan
            return !Persetujuan::where('user_id', Auth::id())->exists();
        }

        return false;
    }


    public static function canEdit($record): bool
    {
        return Auth::check() && (
            Auth::user()->hasRole('superadmin') ||
            $record->user_id === Auth::id()
        );
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('approvers');

        // Batasi untuk marcomm/rt hanya melihat miliknya sendiri
        if (Auth::user()?->hasRole('marcomm') || Auth::user()?->hasRole('rt')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
