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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->datetime('completed_at');
            $table->timestamps();
            $table->foreignId('user_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->integer('total_question');
            $table->integer('correct_answer');
            $table->integer('wrong_answer');
            $table->integer('unanswered');
            $table->decimal('score', 5, 2);
            $table->decimal('percentage',5,2);
            $table->boolean('is_passed')->default(false); // Indicates if the user passed the exam
            $table->json('detailed_answer');
            $table->integer('time_spent_minutes')->nullable(); // Time spent on the exam in minutes
            $table->text('feedback')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
