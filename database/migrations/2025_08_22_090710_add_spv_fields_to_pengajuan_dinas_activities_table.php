<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_dinas_activities', function (Blueprint $table) {
            // setelah "keterangan"
            $table->string('pekerjaan')->nullable()->after('keterangan');
            $table->unsignedInteger('nilai')->nullable()->after('pekerjaan');
            $table->string('target')->nullable()->after('nilai');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_dinas_activities', function (Blueprint $table) {
            $table->dropColumn(['pekerjaan', 'nilai', 'target']);
        });
    }
};
