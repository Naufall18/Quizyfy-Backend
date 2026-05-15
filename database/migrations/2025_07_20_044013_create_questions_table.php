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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('question');
            $table->enum('type', ['multiple', 'essay', 'true_false'])->default('multiple');
            $table->json('options')->nullable(); // For multiple choice options
            $table->string('correct_answer')->nullable(); // For essay or true/false questions
            $table->text('explanation')->nullable(); // Optional explanation for the answer
            $table->string('image')->nullable(); // Optional image for the question
            $table ->integer('order')->default(0);
            $table->boolean('is_active')->default(true); // To enable/disable the question
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
