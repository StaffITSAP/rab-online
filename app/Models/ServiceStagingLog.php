<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStagingLog extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Service::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk label staging
    public function getOldStagingLabelAttribute(): string
    {
        return $this->old_staging ? \App\Enums\StagingEnum::from($this->old_staging)->label() : '-';
    }

    public function getNewStagingLabelAttribute(): string
    {
        return \App\Enums\StagingEnum::from($this->new_staging)->label();
    }

    // Accessor untuk format waktu
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d M Y H:i:s');
    }
}
