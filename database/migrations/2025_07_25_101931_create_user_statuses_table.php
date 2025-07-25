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
        Schema::create('user_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('signature_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->foreignId('divisi_id')->nullable()->constrained('divisis')->nullOnDelete();
            $table->foreignId('atasan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_statuses');
    }
};
