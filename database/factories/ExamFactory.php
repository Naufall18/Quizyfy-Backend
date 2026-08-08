<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titles'            => fake()->sentence(4),
            'description'       => fake()->paragraph(),
            'token'             => Str::upper(Str::random(6)),
            'created_by'        => User::factory()->create(['role' => 'guru'])->id,
            'status'            => fake()->randomElement(['draft', 'aktif']),
            'duration_minutes'  => fake()->numberBetween(30, 120),
            'kkm_score'         => 75,
            'shuffle_question'  => false,
            'shuffle_option'    => false,
            'show_result'       => true,
            'max_attempts'      => 1,
        ];
    }
}
