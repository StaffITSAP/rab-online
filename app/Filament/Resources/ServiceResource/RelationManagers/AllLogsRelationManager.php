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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AllLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceLogs';
    protected static ?string $title = 'Log Perubahan Service';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_name')->label('User')->disabled(),
            Forms\Components\TextInput::make('user_role')->label('Role')->disabled(),
            Forms\Components\TextInput::make('field_changed')
                ->label('Field yang Diubah')
                ->formatStateUsing(fn($state) => $this->getFieldLabel($state))
                ->disabled(),
            Forms\Components\TextInput::make('change_type')
                ->label('Tipe Perubahan')
                ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state)))
                ->disabled(),
            Forms\Components\Textarea::make('old_value_formatted')->label('Nilai Lama')->disabled(),
            Forms\Components\Textarea::make('new_value_formatted')->label('Nilai Baru')->disabled(),
            Forms\Components\Textarea::make('keterangan')->label('Keterangan')->disabled(),
            Forms\Components\TextInput::make('created_at')->label('Waktu')->disabled(),
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

                // ===== Nilai Lama (preview ≤500 kata, tooltip plain & copyable) =====
                Tables\Columns\TextColumn::make('old_value_formatted')
                    ->label('Nilai Lama')
                    ->formatStateUsing(function ($state, $record) {
                        $full    = $this->formatValue($record->field_changed, $record->old_value, $record);
                        $preview = $this->previewText($full, 500);
                        return new HtmlString(nl2br(e($preview)));
                    })
                    ->html()
                    ->extraAttributes([
                        'style' => 'max-width:40rem; white-space:pre-line; overflow-wrap:anywhere; word-break:break-word;',
                        'class' => 'align-top',
                    ])
                    ->tooltip(fn($record) => $this->tooltipPlain(
                        $this->formatValue($record->field_changed, $record->old_value, $record)
                    ))
                    ->copyable()
                    ->copyableState(fn($record) => $this->formatValue($record->field_changed, $record->old_value, $record))
                    ->copyMessage('Disalin'),

                // ===== Nilai Baru (preview ≤500 kata, tooltip plain & copyable) =====
                Tables\Columns\TextColumn::make('new_value_formatted')
                    ->label('Nilai Baru')
                    ->formatStateUsing(function ($state, $record) {
                        $full    = $this->formatValue($record->field_changed, $record->new_value, $record);
                        $preview = $this->previewText($full, 500);
                        return new HtmlString(nl2br(e($preview)));
                    })
                    ->html()
                    ->extraAttributes([
                        'style' => 'max-width:40rem; white-space:pre-line; overflow-wrap:anywhere; word-break:break-word;',
                        'class' => 'align-top',
                    ])
                    ->tooltip(fn($record) => $this->tooltipPlain(
                        $this->formatValue($record->field_changed, $record->new_value, $record)
                    ))
                    ->copyable()
                    ->copyableState(fn($record) => $this->formatValue($record->field_changed, $record->new_value, $record))
                    ->copyMessage('Disalin'),

                // ===== Keterangan (preview ≤500 kata, tooltip plain & copyable) =====
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->formatStateUsing(function ($state) {
                        $full    = $state ?: '-';
                        $preview = $this->previewText($full, 500);
                        return new HtmlString(nl2br(e($preview)));
                    })
                    ->html()
                    ->extraAttributes([
                        'style' => 'max-width:28rem; white-space:pre-line; overflow-wrap:anywhere; word-break:break-word;',
                        'class' => 'align-top',
                    ])
                    ->tooltip(fn($record) => $this->tooltipPlain($record->keterangan ?: '-'))
                    ->copyable()
                    ->copyableState(fn($record) => $record->keterangan ?: '-')
                    ->copyMessage('Disalin'),

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
            ->headerActions([])
            ->actions([
                // ViewAction dihapus
                Tables\Actions\RestoreAction::make()
                    ->visible(fn($record): bool => $record->trashed() && auth()->user()->hasRole('superadmin')),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn($record): bool => $record->trashed() && auth()->user()->hasRole('superadmin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
                    Tables\Actions\RestoreBulkAction::make()->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
                    Tables\Actions\ForceDeleteBulkAction::make()->visible(fn(): bool => auth()->user()->hasRole('superadmin')),
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

    private function formatValue(string $field, $value, $record = null): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        // Decode JSON jika perlu
        $decoded = null;
        if (is_string($value)) {
            $tmp = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                $decoded = $tmp;
            }
        } elseif (is_array($value)) {
            $decoded = $value;
        }

        $isCreateLike = ($field === 'all') || ($record && ($record->change_type === 'create'));
        if ($isCreateLike && is_array($decoded)) {
            return $this->prettyLinesFromArray($decoded);
        }

        if ($field === 'staging') {
            return StagingEnum::tryFrom((string) $value)?->label() ?? (string) $value;
        }

        if ($field === 'masih_garansi') {
            if (is_bool($value)) {
                return $value ? 'Ya' : 'Tidak';
            }
            $val = strtoupper((string) $value);
            return in_array($val, ['Y', '1', 'TRUE'], true) ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /** Preview teks dengan batas kata (default 500) + fallback karakter. */
    private function previewText(string $full, int $maxWords = 500, int $maxCharsFallback = 5000): string
    {
        $normalized = preg_replace("/[ \t]+/u", ' ', $full ?? '-');
        $byWords    = Str::words($normalized, $maxWords, '…');

        return mb_strlen($byWords) > $maxCharsFallback
            ? (mb_substr($byWords, 0, $maxCharsFallback) . '…')
            : $byWords;
    }

    /** Array → multi-baris "Label : Nilai" dengan urutan rapi. */
    private function prettyLinesFromArray(array $data): string
    {
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

        $ignored = ['id', 'user_id', 'created_at', 'updated_at'];
        $lines = [];

        foreach ($orderedKeys as $k) {
            if (array_key_exists($k, $data)) {
                $lines[] = $this->lineFor($k, $data[$k]);
            }
        }

        foreach ($data as $k => $v) {
            if (in_array($k, $orderedKeys, true) || in_array($k, $ignored, true)) {
                continue;
            }
            $lines[] = $this->lineFor($k, $v);
        }

        return implode("\n", array_filter($lines, fn($l) => $l !== null && $l !== ''));
    }

    private function lineFor(string $key, $rawValue): string
    {
        $label = $this->getFieldLabel($key);

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

    /**
     * Tooltip plain-text yang rapi:
     * - Pertahankan newline
     * - Sisipkan Zero-Width Space tiap 40 karakter non-spasi agar tetap wrap
     */
    private function tooltipPlain(string $text, int $chunk = 40): string
    {
        $text = $text === '' ? '-' : $text;

        // normalisasi line break
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $zwsp = "\u{200B}"; // Zero-Width Space

        return preg_replace_callback('/\S{' . $chunk . ',}/u', function ($m) use ($chunk, $zwsp) {
            // pecah token panjang menjadi potongan 40 char dengan ZWSP
            $parts = [];
            $str = $m[0];
            $len = mb_strlen($str);
            for ($i = 0; $i < $len; $i += $chunk) {
                $parts[] = mb_substr($str, $i, $chunk);
            }
            return implode($zwsp, $parts);
        }, $text);
    }
}
