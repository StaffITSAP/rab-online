<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_marcomm_kebutuhans', function (Blueprint $table) {
            $table->boolean('kebutuhan_amplop')->default(false)->after('total_amplop');
            $table->boolean('kebutuhan_kartu')->default(false)->after('kebutuhan_amplop');
            $table->boolean('kebutuhan_kemeja')->default(false)->after('kebutuhan_kartu');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_marcomm_kebutuhans', function (Blueprint $table) {
            $table->dropColumn(['kebutuhan_amplop', 'kebutuhan_kartu', 'kebutuhan_kemeja']);
        });
    }
};
