<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_marcomm_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->onDelete('cascade');
            $table->enum('deskripsi', [
                'Biaya Hotel',
                'Biaya Konsumsi',
                'Biaya Transportasi',
                'Biaya Lain-lain'
            ]);
            $table->string('keterangan')->nullable();
            $table->string('pic')->nullable();
            $table->integer('jml_hari')->default(0);
            $table->bigInteger('harga_satuan')->default(0);
            $table->bigInteger('subtotal')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_marcomm_kegiatans');
    }
};
