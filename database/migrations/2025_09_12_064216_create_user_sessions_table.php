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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id', 191)->unique(); // Uzunlikni cheklash
            $table->string('device_id', 191)->nullable(); // Uzunlikni cheklash
            $table->string('device_name', 191)->nullable(); // Uzunlikni cheklash
            $table->string('device_type', 50)->nullable(); // mobile, web, desktop
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('platform', 50)->nullable(); // iOS, Android, Windows, macOS
            $table->string('browser', 50)->nullable(); // Chrome, Safari, Firefox
            $table->string('location', 100)->nullable(); // Toshkent, Buxoro
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_activity_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['device_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
