<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('user_name');
            $table->string('user_role');

            // Kolom untuk melacak perubahan
            $table->string('field_changed');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('change_type'); // create, update, delete, staging_change, etc.

            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Index untuk performa query
            $table->index('service_id');
            $table->index('field_changed');
            $table->index('change_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};
