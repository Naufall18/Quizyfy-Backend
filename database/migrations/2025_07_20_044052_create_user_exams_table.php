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
        Schema::create('user_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->datetime('started_at')->nullable();
            $table->datetime('finished_at')->nullable();
            $table->datetime('deadline')->nullable();
            $table->integer('score')->nullable();
            $table->integer('correct_answers')->nullable(); // Number of correct answers
            $table->integer('wrong_answers')->nullable(); // Number of wrong answers
            $table->integer('unanswered')->nullable(); // Number of unanswered questions
            $table->json('answers')->nullable(); // Store user's answers in JSON format
            $table->enum('status', ['in_progress', 'registered', 'completed','time_up'])->default('registered');
            $table->integer('attempt_number')->default(1); // Track the number of attempts
            $table->text('notes')->nullable(); // Optional notes for the exam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_exams');
    }
};
