<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeRab extends Model
{
    protected $table = 'tipe_rabs';
    protected $fillable = ['kode', 'nama'];
}
