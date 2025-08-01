<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->boolean('use_owner')->default(false)->after('use_direktur'); // atur 'after' sesuai kebutuhan
        });
    }

    public function down(): void
    {
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->dropColumn('use_owner');
        });
    }
};
