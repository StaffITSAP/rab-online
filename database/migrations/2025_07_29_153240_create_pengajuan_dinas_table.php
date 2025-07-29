<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuan_dinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained()->onDelete('cascade');
            $table->enum('deskripsi', ['Transportasi', 'Makan', 'Lain-lain']);
            $table->text('keterangan')->nullable();
            $table->string('pic')->nullable();
            $table->integer('jml_hari')->nullable();
            $table->integer('harga_satuan')->nullable();
            $table->integer('subtotal')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_dinas');
    }
};
