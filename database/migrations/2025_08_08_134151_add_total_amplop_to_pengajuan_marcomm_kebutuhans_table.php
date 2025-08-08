<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_marcomm_kebutuhans', function (Blueprint $table) {
            $table->decimal('total_amplop', 15, 2)->nullable()->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_marcomm_kebutuhans', function (Blueprint $table) {
            $table->dropColumn('total_amplop');
        });
    }
};
