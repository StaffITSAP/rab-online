<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    protected $fillable = ['kode', 'nama'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class); // jika user memiliki cabang_id
    }
}
