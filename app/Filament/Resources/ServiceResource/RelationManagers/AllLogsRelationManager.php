<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use App\Enums\StagingEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class AllLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceLogs';

    protected static ?string $title = 'Log Perubahan Service';

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
                Forms\Components\TextInput::make('field_changed')
                    ->label('Field yang Diubah')
                    ->formatStateUsing(fn($state) => $this->getFieldLabel($state))
                    ->disabled(),
                Forms\Components\TextInput::make('change_type')
                    ->label('Tipe Perubahan')
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->disabled(),
                Forms\Components\Textarea::make('old_value_formatted')
                    ->label('Nilai Lama')
                    ->disabled(),
                Forms\Components\Textarea::make('new_value_formatted')
                    ->label('Nilai Baru')
                    ->disabled(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->disabled(),
                Forms\Components\TextInput::make('created_at')
                    ->label('Waktu')
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

                Tables\Columns\TextColumn::make('field_changed')
                    ->label('Field')
                    ->formatStateUsing(fn($state) => $this->getFieldLabel($state))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('change_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn(string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'primary',
                        'delete' => 'danger',
                        'restore' => 'warning',
                        'staging_change' => 'purple',
                        'force_delete' => 'danger',
                        default => 'gray',
                    }),

                // ===== Nilai Lama =====
                Tables\Columns\TextColumn::make('old_value_formatted')
                    ->label('Nilai Lama')
                    ->formatStateUsing(function ($state, $record) {
                        $text = $this->formatValue($record->field_changed, $record->old_value, $record);
                        return new HtmlString(nl2br(e($text))); // render newline
                    })
                    ->wrap()
                    ->html()
                    ->extraAttributes(['class' => 'whitespace-pre-wrap text-left'])
                    ->tooltip(function ($record) {
                        return $this->formatValue($record->field_changed, $record->old_value, $record);
                    }),

                // ===== Nilai Baru =====
                Tables\Columns\TextColumn::make('new_value_formatted')
                    ->label('Nilai Baru')
                    ->formatStateUsing(function ($state, $record) {
                        $text = $this->formatValue($record->field_changed, $record->new_value, $record);
                        return new HtmlString(nl2br(e($text))); // render newline
                    })
                    ->wrap()
                    ->html()
                    ->extraAttributes(['class' => 'whitespace-pre-wrap text-left'])
                    ->tooltip(function ($record) {
                        return $this->formatValue($record->field_changed, $record->new_value, $record);
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->keterangan),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('field_changed')
                    ->label('Field')
                    ->options([
                        'id_paket' => 'ID Paket',
                        'staging' => 'Staging',
                        'nama_dinas' => 'Nama Dinas',
                        'kontak' => 'Kontak',
                        'no_telepon' => 'No. Telepon',
                        'kerusakan' => 'Kerusakan',
                        'nama_barang' => 'Nama Barang',
                        'noserial' => 'No. Serial',
                        'masih_garansi' => 'Status Garansi',
                        'nomer_so' => 'No. SO',
                        'keterangan_staging' => 'Keterangan Staging',
                    ]),

                Tables\Filters\SelectFilter::make('change_type')
                    ->label('Tipe Perubahan')
                    ->options([
                        'create' => 'Buat',
                        'update' => 'Update',
                        'delete' => 'Hapus',
                        'restore' => 'Pulihkan',
                        'staging_change' => 'Perubahan Staging',
                        'force_delete' => 'Hapus Permanen',
                    ]),

                Tables\Filters\SelectFilter::make('user_role')
                    ->label('Role User')
                    ->options([
                        'superadmin' => 'Super Admin',
                        'servis' => 'Servis',
                        'sales' => 'Sales',
                        'admin_service' => 'Admin Service',
                        'manager' => 'Manager',
                    ])
                    ->searchable(),

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

                Tables\Actions\RestoreAction::make()
                    ->visible(
                        fn($record): bool =>
                        $record->trashed() && auth()->user()->hasRole('superadmin')
                    ),

                Tables\Actions\ForceDeleteAction::make()
                    ->visible(
                        fn($record): bool =>
                        $record->trashed() && auth()->user()->hasRole('superadmin')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn(): bool => auth()->user()->hasRole('superadmin')),

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

        if ($query) {
            return $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $this->getRelationship()->getQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    private function getFieldLabel(string $field): string
    {
        $fieldLabels = [
            'id_paket'           => 'ID Paket',
            'staging'            => 'Staging',
            'nama_dinas'         => 'Nama Dinas',
            'kontak'             => 'Kontak',
            'no_telepon'         => 'No. Telepon',
            'kerusakan'          => 'Kerusakan',
            'nama_barang'        => 'Nama Barang',
            'noserial'           => 'No. Serial',
            'masih_garansi'      => 'Status Garansi',
            'nomer_so'           => 'No. SO',
            'keterangan_staging' => 'Keterangan Staging',
            'all'                => 'Semua Field',
        ];

        return $fieldLabels[$field] ?? $field;
    }

    /**
     * Format nilai untuk ditampilkan pada kolom.
     * - Jika field = 'all' atau change_type = 'create' dan value berisi JSON/array,
     *   tampilkan setiap field pada baris baru (wrap).
     */
    private function formatValue(string $field, $value, $record = null): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        // Jika string JSON → decode
        $decoded = null;
        if (is_string($value)) {
            $tmp = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                $decoded = $tmp;
            }
        } elseif (is_array($value)) {
            $decoded = $value;
        }

        // Kasus CREATE / ALL → render tiap field per baris
        $isCreateLike = ($field === 'all') || ($record && ($record->change_type === 'create'));

        if ($isCreateLike && is_array($decoded)) {
            return $this->prettyLinesFromArray($decoded);
        }

        // Single field khusus
        if ($field === 'staging') {
            return StagingEnum::tryFrom((string) $value)?->label() ?? (string) $value;
        }

        if ($field === 'masih_garansi') {
            // support true/false, 1/0, Y/N
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            $val = strtoupper((string) $value);
            return in_array($val, ['Y', '1', 'TRUE'], true) ? 'Ya' : 'Tidak';
        }

        // Array biasa (bukan create/all) → pretty JSON
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * Ubah array payload menjadi teks multi-baris "Label : Nilai" dengan urutan rapi.
     */
    private function prettyLinesFromArray(array $data): string
    {
        // Urutan field yang ingin ditonjolkan
        $orderedKeys = [
            'id_paket',
            'nama_dinas',
            'kontak',
            'no_telepon',
            'kerusakan',
            'nama_barang',
            'noserial',
            'masih_garansi',
            'nomer_so',
            'staging',
            'keterangan_staging',
        ];

        // Beberapa key yang tidak perlu ditampilkan
        $ignored = ['id', 'user_id', 'created_at', 'updated_at'];

        $lines = [];

        // 1) Tampilkan field sesuai urutan di atas
        foreach ($orderedKeys as $k) {
            if (array_key_exists($k, $data)) {
                $lines[] = $this->lineFor($k, $data[$k]);
            }
        }

        // 2) Tampilkan sisa field yang tidak terdaftar & tidak di-ignore
        foreach ($data as $k => $v) {
            if (in_array($k, $orderedKeys, true) || in_array($k, $ignored, true)) {
                continue;
            }
            $lines[] = $this->lineFor($k, $v);
        }

        return implode("\n", array_filter($lines, fn($l) => $l !== null && $l !== ''));
    }

    /**
     * Format satu baris "Label : Nilai" dengan normalisasi nilai khusus.
     */
    private function lineFor(string $key, $rawValue): string
    {
        $label = $this->getFieldLabel($key);

        // Normalisasi nilai
        $value = '-';
        if (is_null($rawValue) || $rawValue === '') {
            $value = '-';
        } elseif ($key === 'staging') {
            $value = StagingEnum::tryFrom((string) $rawValue)?->label() ?? (string) $rawValue;
        } elseif ($key === 'masih_garansi') {
            if (is_bool($rawValue)) {
                $value = $rawValue ? 'Ya' : 'Tidak';
            } else {
                $val = strtoupper((string) $rawValue);
                $value = in_array($val, ['Y', '1', 'TRUE'], true) ? 'Ya' : 'Tidak';
            }
        } elseif (is_array($rawValue) || is_object($rawValue)) {
            $value = json_encode($rawValue, JSON_UNESCAPED_UNICODE);
        } else {
            $value = (string) $rawValue;
        }

        return "{$label} : {$value}";
    }
}
