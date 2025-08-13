<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_marcomm_kegiatans', function (Blueprint $table) {
            $table->boolean('tim_pusat')->default(false)->after('deleted_at');
            $table->boolean('tim_cabang')->default(false)->after('tim_pusat');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_marcomm_kegiatans', function (Blueprint $table) {
            $table->dropColumn(['tim_pusat', 'tim_cabang']);
        });
    }
};
