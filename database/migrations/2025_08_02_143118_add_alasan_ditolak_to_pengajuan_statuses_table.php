<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pengajuan_statuses', function ($table) {
            $table->text('alasan_ditolak')->nullable()->after('approved_at');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_statuses', function ($table) {
            $table->dropColumn('alasan_ditolak');
        });
    }
};
