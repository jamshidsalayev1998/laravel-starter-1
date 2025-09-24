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
            // Avval unique indeksni olib tashlash
            $table->dropUnique(['email']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Ustunni nullable qilish
            $table->string('email', 191)->nullable()->change();
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Yana unique indeksni qo'shish
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Unique indeksni olib tashlash
            $table->dropUnique(['email']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Ustunni not null qilish
            $table->string('email', 191)->nullable(false)->change();
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Unique indeksni qaytarish
            $table->unique('email');
        });
    }
};
