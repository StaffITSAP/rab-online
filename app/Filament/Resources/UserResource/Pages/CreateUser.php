<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\UserStatus;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected array $userStatusData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pisahkan userStatus data untuk nanti
        $this->userStatusData = [
            'signature_path' => $data['userStatus']['signature_path'] ?? null,
            'is_active' => $data['userStatus']['is_active'] ?? false,
            'cabang_id' => $data['userStatus']['cabang_id'] ?? null,
            'divisi_id' => $data['userStatus']['divisi_id'] ?? null,
            'atasan_id' => $data['userStatus']['atasan_id'] ?? null,
        ];

        unset($data['userStatus']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->userStatus()->create($this->userStatusData);
    }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
