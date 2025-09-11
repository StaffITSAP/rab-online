<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['kerusakan', 'nama_barang', 'noserial', 'masih_garansi']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('kerusakan')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('noserial')->nullable();
            $table->enum('masih_garansi', ['Y', 'T'])->default('T');
        });
    }
};
