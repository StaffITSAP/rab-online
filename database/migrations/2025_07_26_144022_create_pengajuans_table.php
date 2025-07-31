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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('no_rab')->unique();
            $table->foreignId('tipe_rab_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai', 'expired'])->default('menunggu');
            $table->bigInteger('total_biaya')->default(0);
            $table->date('tgl_realisasi')->nullable();
            $table->date('tgl_pulang')->nullable();
            $table->string('jam')->nullable();
            $table->string('deletion_reason')->nullable();
            $table->integer('jml_personil')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
