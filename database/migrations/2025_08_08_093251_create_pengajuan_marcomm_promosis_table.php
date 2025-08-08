<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_marcomm_promosis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')
                  ->constrained('pengajuans')
                  ->onDelete('cascade');
            $table->string('deskripsi');
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_marcomm_promosis');
    }
};
