<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserExam>
 */
class UserExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory()->create(['role' => 'user'])->id,
            'exam_id'           => Exam::factory(),
            'status'            => 'completed',
            'score'             => fake()->numberBetween(40, 100),
            'correct_answers'   => fake()->numberBetween(5, 20),
            'wrong_answers'     => fake()->numberBetween(0, 5),
            'started_at'        => now()->subHour(),
            'finished_at'       => now(),
        ];
    }
}
