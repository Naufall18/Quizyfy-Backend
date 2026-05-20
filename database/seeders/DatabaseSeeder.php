<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan seeding penting — ikuti dependency:
     * Users → Categories → Exams (butuh user & category) → Plans
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ExamSeeder::class,
            PlanSeeder::class,
        ]);
    }
}
