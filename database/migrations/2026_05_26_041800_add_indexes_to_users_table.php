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
            // Add indexes untuk kolom yang sering di-query
            $table->index('email');
            $table->index('google_id');
            $table->index('reset_token');
            $table->index('role');
            
            // Composite index untuk query yang menggunakan email + reset_token
            $table->index(['email', 'reset_token'], 'users_email_reset_token_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['google_id']);
            $table->dropIndex(['reset_token']);
            $table->dropIndex(['role']);
            $table->dropIndex('users_email_reset_token_index');
        });
    }
};
