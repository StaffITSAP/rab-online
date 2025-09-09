<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_staging_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('user_name');
            $table->string('user_role');
            $table->enum('old_staging', ['request', 'cek_kerusakan', 'ada_biaya', 'close', 'approve'])->nullable();
            $table->enum('new_staging', ['request', 'cek_kerusakan', 'ada_biaya', 'close', 'approve']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staging_logs');
    }
};
