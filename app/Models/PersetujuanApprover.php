<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersetujuanApprover extends Model
{
    use HasFactory;

    protected $fillable = ['persetujuan_id', 'approver_id'];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function persetujuan()
    {
        return $this->belongsTo(Persetujuan::class);
    }
}
