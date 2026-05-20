<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // google_id: ID unik dari Google, nullable karena user biasa tidak punya
            $table->string('google_id')->nullable()->unique()->after('email');
            // google_avatar: URL foto profil dari Google
            $table->string('google_avatar')->nullable()->after('google_id');
            // password nullable agar user Google tidak perlu password
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'google_avatar']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
