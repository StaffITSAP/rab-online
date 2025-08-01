<?php

namespace App\Filament\Resources\LampiranDinasResource\Pages;

use App\Filament\Resources\LampiranDinasResource;
use App\Models\LampiranDinas;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;


class CreateLampiranDinas extends CreateRecord
{
    protected static string $resource = LampiranDinasResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        $pengajuanId = $data['pengajuan_id'];
        $paths = $data['file_path'] ?? [];

        $firstRecord = null;

        foreach ($paths as $idx => $path) {
            $record = LampiranDinas::create([
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
        return $firstRecord ?? LampiranDinas::create([
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
