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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('is_active');
            $table->timestamp('banned_at')->nullable()->after('is_banned');
            $table->foreignId('banned_by')->nullable()->constrained('users')->after('banned_at');
            $table->text('ban_reason')->nullable()->after('banned_by');
            $table->timestamp('ban_expires_at')->nullable()->after('ban_reason'); // Temporary ban uchun
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn([
                'is_banned',
                'banned_at', 
                'banned_by',
                'ban_reason',
                'ban_expires_at'
            ]);
        });
    }
};
