<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('id_paket');
            $table->string('nama_dinas');
            $table->string('kontak');
            $table->string('no_telepon');
            $table->text('kerusakan');
            $table->string('nama_barang');
            $table->string('noserial');
            $table->enum('masih_garansi', ['Y', 'T']);
            $table->string('nomer_so')->nullable();
            $table->enum('staging', ['request', 'cek_kerusakan', 'ada_biaya', 'close', 'approve'])->default('request');
            $table->text('keterangan_staging')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};