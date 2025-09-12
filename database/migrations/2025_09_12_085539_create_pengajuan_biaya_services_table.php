<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_biaya_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');
            $table->foreignId('service_item_id')
                ->nullable()
                ->constrained('service_items')
                ->onDelete('set null');

            $table->string('deskripsi')->nullable();
            $table->integer('jumlah')->default(1);
            $table->integer('harga_satuan')->default(0);
            $table->integer('subtotal')->default(0);

            // Pajak
            $table->decimal('pph_persen', 5, 2)->nullable();
            $table->integer('pph_nominal')->nullable();
            $table->integer('dpp_jual')->nullable();
            $table->integer('total')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_id', 'service_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_biaya_services');
    }
};
