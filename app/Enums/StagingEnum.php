<?php

namespace App\Enums;

enum StagingEnum: string
{
    case REQUEST = 'request';
    case CEK_KERUSAKAN = 'cek_kerusakan';
    case ADA_BIAYA = 'ada_biaya';
    case CLOSE = 'close';
    case APPROVE = 'approve';

    public function label(): string
    {
        return match ($this) {
            self::REQUEST => 'Request',
            self::CEK_KERUSAKAN => 'Cek Kerusakan',
            self::ADA_BIAYA => 'Ada Biaya',
            self::CLOSE => 'Close',
            self::APPROVE => 'Approve',
        };
    }

    public static function options(): array
    {
        return [
            self::REQUEST->value => self::REQUEST->label(),
            self::CEK_KERUSAKAN->value => self::CEK_KERUSAKAN->label(),
            self::ADA_BIAYA->value => self::ADA_BIAYA->label(),
            self::CLOSE->value => self::CLOSE->label(),
            self::APPROVE->value => self::APPROVE->label(),
        ];
    }

    public static function fromValue(string $value): self
    {
        return match ($value) {
            'request' => self::REQUEST,
            'cek_kerusakan' => self::CEK_KERUSAKAN,
            'ada_biaya' => self::ADA_BIAYA,
            'close' => self::CLOSE,
            'approve' => self::APPROVE,
            default => self::REQUEST,
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::REQUEST        => 'gray',
            self::CEK_KERUSAKAN  => 'blue',
            self::ADA_BIAYA      => 'orange',
            self::CLOSE          => 'green',
            self::APPROVE        => 'purple',
        };
    }
}
