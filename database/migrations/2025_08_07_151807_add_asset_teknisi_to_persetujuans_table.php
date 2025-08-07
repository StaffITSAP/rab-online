<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->boolean('asset_teknisi')->default(false)->after('menggunakan_teknisi');
        });
    }

    public function down(): void
    {
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->dropColumn('asset_teknisi');
        });
    }
};
