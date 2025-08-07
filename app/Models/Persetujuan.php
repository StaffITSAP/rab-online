<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persetujuan extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'approver_id', 'menggunakan_teknisi', 'use_pengiriman', 'use_manager', 'use_direktur','use_owner','use_car'];
    protected $casts = [
        'menggunakan_teknisi' => 'boolean',
        'use_manager' => 'boolean',
        'use_direktur' => 'boolean',
        'use_owner' => 'boolean',
        'use_car' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
    public function pengajuanApprovers()
    {
        return $this->hasMany(PersetujuanApprover::class);
    }
    public function approvers()
    {
        return $this->hasMany(PersetujuanApprover::class);
    }
}
