<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStagingLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'user_id',
        'user_name',
        'user_role',
        'old_staging',
        'new_staging',
        'keterangan'
    ];

    protected $casts = [
        'old_staging' => 'string',
        'new_staging' => 'string',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk label staging lama
    public function getOldStagingLabelAttribute(): string
    {
        return $this->old_staging ? \App\Enums\StagingEnum::from($this->old_staging)->label() : '-';
    }

    // Accessor untuk label staging baru
    public function getNewStagingLabelAttribute(): string
    {
        return \App\Enums\StagingEnum::from($this->new_staging)->label();
    }

    // Accessor untuk format waktu
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d M Y H:i:s');
    }

    // Accessor untuk badge color staging baru
    public function getNewStagingBadgeColorAttribute(): string
    {
        return match ($this->new_staging) {
            'request' => 'gray',
            'cek_kerusakan' => 'blue',
            'ada_biaya' => 'orange',
            'close' => 'green',
            'approve' => 'purple',
            default => 'gray',
        };
    }

    // Accessor untuk badge color staging lama
    public function getOldStagingBadgeColorAttribute(): string
    {
        if (!$this->old_staging) return 'transparent';

        return match ($this->old_staging) {
            'request' => 'gray',
            'cek_kerusakan' => 'blue',
            'ada_biaya' => 'orange',
            'close' => 'green',
            'approve' => 'purple',
            default => 'gray',
        };
    }
}
