<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            // flag pembuka edit saat expired
            $table->boolean('expired_unlocked')->default(false)->after('status');
            // siapa yang membuka & kapan (opsional tapi berguna)
            $table->foreignId('expired_unlocked_by')->nullable()->after('expired_unlocked')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('expired_unlocked_at')->nullable()->after('expired_unlocked_by');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropForeign(['expired_unlocked_by']);
            $table->dropColumn(['expired_unlocked', 'expired_unlocked_by', 'expired_unlocked_at']);
        });
    }
};
