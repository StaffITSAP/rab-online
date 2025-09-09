<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Models\Persetujuan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PersetujuanResource extends Resource
{
    protected static ?string $model = Persetujuan::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Persetujuan';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $label           = 'Persetujuan';
    protected static ?string $slug            = 'persetujuan';

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
                        ->default(fn() => Auth::id())
                        // hanya superadmin boleh mengubah pemilik
                        ->disabled(fn() => ! Auth::user()?->hasRole('superadmin')),

                    // ====== DAFTAR APPROVER ======
                    Repeater::make('approvers')
                        ->label('Daftar Approver')
                        ->relationship('approvers') // HasMany ke model detail approver untuk persetujuan ini
                        ->schema([
                            Select::make('approver_id')
                                ->label('Pilih Approver')
                                ->options(fn() => User::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->addActionLabel('Tambah Approver')
                        ->minItems(1)
                        ->columns(1)
                        ->required()
                        /**
                         * KUNCI: Simpan relasi dengan kontrol penuh per-role.
                         * - marcomm/rt: upsert item yang ada di form, lalu hapus hanya item yang benar2 dihapus di form
                         *   (hanya pada record ini), tidak pernah menyentuh pengajuan lain.
                         * - superadmin: full reconcile (hapus yang tidak ada di form) pada record ini.
                         */
                        ->saveRelationshipsUsing(function ($record, $state) {
                            $state = is_array($state) ? $state : [];

                            // Normalisasi: ambil daftar approver_id dari form
                            $incomingApproverIds = collect($state)
                                ->map(fn($row) => $row['approver_id'] ?? null)
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            // Ambil item existing pada record INI saja
                            $existing = $record->approvers()->get(['id', 'approver_id']);
                            $existingByApproverId = $existing->keyBy('approver_id');

                            // Upsert (buat jika belum ada)
                            $keptIds = [];
                            foreach ($incomingApproverIds as $approverId) {
                                $row = $record->approvers()->updateOrCreate(
                                    ['approver_id' => $approverId],
                                    [] // tambah kolom lain bila ada (mis. role, urutan, dll.)
                                );
                                $keptIds[] = $row->id;
                            }

                            // Hapus item yang dihilangkan di form — tetap hanya dalam scope record ini.
                            // - marcomm/rt: boleh menghapus yang mereka keluarkan dari form (tetap lokal record ini).
                            // - superadmin: sama (full kontrol).
                            if (count($keptIds) > 0) {
                                $record->approvers()
                                    ->whereNotIn('id', $keptIds)
                                    ->delete();
                            } else {
                                // Jika form kosong (meski minItems=1 harusnya tidak terjadi), jangan sentuh existing untuk amannya.
                                // Jika ingin kosongkan secara eksplisit, uncomment baris di bawah:
                                // $record->approvers()->delete();
                            }
                        }),
                    // =================================

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
                ->columns(1),
        ]);
    }

    /**
     * Toggle yang menambah/menghapus approver otomatis sesuai role target.
     * Aktif untuk semua role, karena marcomm/rt boleh mengatur persetujuannya sendiri.
     * Perubahan tetap lokal pada form; saat save, penyimpanan terkontrol di saveRelationshipsUsing di atas.
     */
    public static function autoToggle(string $field, string $role)
    {
        return Toggle::make($field)
            ->label(ucwords(str_replace('_', ' ', $field)))
            ->default(false)
            ->onIcon('heroicon-s-check')
            ->offIcon('heroicon-s-x-mark')
            ->onColor('success')
            ->offColor('danger')
            ->reactive()
            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($role) {
                $approvers = collect($get('approvers'));
                $user      = User::role($role)->first();
                if (! $user) {
                    return;
                }

                if ($state) {
                    if (! $approvers->contains('approver_id', $user->id)) {
                        $approvers->push(['approver_id' => $user->id]);
                    }
                } else {
                    $approvers = $approvers->reject(fn($item) => ($item['approver_id'] ?? null) == $user->id);
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

                TextColumn::make('menggunakan_teknisi')
                    ->label('Teknisi')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),

                TextColumn::make('asset_teknisi')
                    ->label('RT')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),

                TextColumn::make('use_pengiriman')
                    ->label('Gudang')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),

                TextColumn::make('use_car')
                    ->label('Mobil')
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

                TextColumn::make('use_owner')
                    ->label('Owner')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
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
            'index'  => Pages\ListPersetujuans::route('/'),
            'create' => Pages\CreatePersetujuan::route('/create'),
            'edit'   => Pages\EditPersetujuan::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check()
            && (Auth::user()->hasRole('superadmin')
                || Auth::user()->hasRole('marcomm')
                || Auth::user()->hasRole('rt'));
    }

    public static function canViewAny(): bool
    {
        return Auth::check()
            && (Auth::user()->hasRole('superadmin')
                || Auth::user()->hasRole('marcomm')
                || Auth::user()->hasRole('rt'));
    }

    /**
     * IZINKAN marcomm/rt membuat lebih dari satu pengajuan.
     * Pengajuan baru TIDAK akan mengubah pengajuan sebelumnya.
     */
    public static function canCreate(): bool
    {
        if (Auth::user()->hasRole('superadmin')) {
            return true;
        }

        if (Auth::user()->hasRole('marcomm') || Auth::user()->hasRole('rt')) {
            return true; // tidak dibatasi satu per user lagi
        }

        return false;
    }

    public static function canEdit($record): bool
    {
        return Auth::check()
            && (Auth::user()->hasRole('superadmin') || $record->user_id === Auth::id());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('approvers');

        // Batasi marcomm/rt hanya melihat miliknya sendiri
        if (Auth::user()?->hasRole('marcomm') || Auth::user()?->hasRole('rt')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }
}
