<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersetujuanApprover extends Model
{
    use HasFactory;

    protected $fillable = ['persetujuan_id', 'approver_id'];

    // Relasi ke user yang menjadi approver
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Relasi ke tabel persetujuan
    public function persetujuan()
    {
        return $this->belongsTo(Persetujuan::class, 'persetujuan_id');
    }
}
