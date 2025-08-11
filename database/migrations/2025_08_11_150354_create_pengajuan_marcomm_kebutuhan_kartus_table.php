<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengajuan_marcomm_kebutuhan_kartus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')
                ->constrained('pengajuans')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('kartu_nama', 150);  // nama pada kartu
            $table->string('id_card', 150);     // nomor/id card

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pengajuan_id']);
            $table->index(['kartu_nama']);
            $table->index(['id_card']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_marcomm_kebutuhan_kartus');
    }
};
