<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMonitor extends Model
{
    protected $table = 'customer'; // nama tabel di DB monitor
    public $timestamps = false;

    // gunakan koneksi monitor_sales_pgsql
    protected $connection = 'monitor_sales_pgsql';
}
