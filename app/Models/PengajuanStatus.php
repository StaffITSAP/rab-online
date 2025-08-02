<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanStatus extends Model
{
    protected $fillable = ['pengajuan_id', 'persetujuan_id', 'user_id', 'is_approved', 'approved_at', 'alasan_ditolak'];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function persetujuan()
    {
        return $this->belongsTo(Persetujuan::class);
    }
}
