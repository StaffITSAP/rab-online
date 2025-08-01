<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lampirans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_id')->constrained()->cascadeOnDelete();

            $table->boolean('lampiran_asset')->default(false);
            $table->boolean('lampiran_dinas')->default(false);
            $table->boolean('lampiran_marcomm_kegiatan')->default(false);
            $table->boolean('lampiran_marcomm_kebutuhan')->default(false);
            $table->boolean('lampiran_marcomm_promosi')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampirans');
    }
};
