<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_marcomm_kegiatans_cabang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_marcomm_kegiatans')->onDelete('cascade');
            $table->string('cabang');
            $table->string('nama');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_marcomm_kegiatans_cabang');
    }
};
