<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Divisi extends Model
{
    use SoftDeletes;

    protected $fillable = ['kode', 'nama'];

    // Contoh relasi: Divisi memiliki banyak user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
