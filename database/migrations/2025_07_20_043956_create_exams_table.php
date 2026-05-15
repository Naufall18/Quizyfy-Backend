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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table ->string('titles');
            $table->text('description')->nullable();
            $table->string('token', 10)->unique();
            $table->foreignId('category_id')->nullable()->constrained()->OnDelete('set null');
            $table->foreignId('created_by')->constrained('users')->OnDelete('cascade');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->integer('duration_minutes');
            $table->integer('total_questions')->default(0);
            $table->integer('kkm_score')->default(50);
            $table->enum('status', ['draft', 'aktif', 'nonaktif','berlangsung', 'selesai'])->default('draft'); 
            $table->boolean('shuffle_question')->default(false);
            $table->boolean('shuffle_option')->default(false);
            $table->boolean('show_result')->default(true);
            $table->integer('max_attempts')->default(1);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
