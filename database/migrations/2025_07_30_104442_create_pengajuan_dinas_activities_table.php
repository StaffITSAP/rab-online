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
        Schema::create('pengajuan_dinas_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained()->onDelete('cascade');
            $table->string('no_activity');
            $table->string('nama_dinas');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_dinas_activities');
    }
};
