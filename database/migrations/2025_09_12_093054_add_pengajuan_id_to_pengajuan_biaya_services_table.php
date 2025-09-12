<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_biaya_services', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_biaya_services', 'pengajuan_id')) {
                $table->foreignId('pengajuan_id')
                    ->after('id')
                    ->nullable()
                    ->constrained('pengajuans')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_biaya_services', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_biaya_services', 'pengajuan_id')) {
                $table->dropForeign(['pengajuan_id']);
                $table->dropColumn('pengajuan_id');
            }
        });
    }
};
