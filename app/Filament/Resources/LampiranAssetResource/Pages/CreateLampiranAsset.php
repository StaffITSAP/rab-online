<?php

namespace App\Filament\Resources\LampiranAssetResource\Pages;

use App\Models\LampiranAsset;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Filament\Resources\LampiranAssetResource;

class CreateLampiranAsset extends CreateRecord
{
    protected static string $resource = LampiranAssetResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $pengajuanId = $data['pengajuan_id'];
        $paths = $data['file_path'] ?? [];

        $firstRecord = null;

        foreach ($paths as $idx => $path) {
            $record = LampiranAsset::create([
                'pengajuan_id'  => $pengajuanId,
                'file_path'     => $path,
                'original_name' => basename($path),
            ]);
            if ($idx === 0) {
                $firstRecord = $record;
            }
        }

        Notification::make()
            ->title('Upload berhasil')
            ->success()
            ->send();

        // Kembalikan record pertama agar signature valid
        return $firstRecord ?? LampiranAsset::create([
            'pengajuan_id' => $pengajuanId,
            'file_path'    => '',
            'original_name' => '',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
