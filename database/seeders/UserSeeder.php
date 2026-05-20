<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1 Admin ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@quizyfy.com'],
            [
                'name'      => 'Admin Quizyfy',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        // ── 2 Guru ───────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'guru1@quizyfy.com'],
            [
                'name'      => 'Budi Santoso',
                'password'  => Hash::make('password123'),
                'role'      => 'guru',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'guru2@quizyfy.com'],
            [
                'name'      => 'Siti Rahayu',
                'password'  => Hash::make('password123'),
                'role'      => 'guru',
                'is_active' => true,
            ]
        );

        // ── 3 Siswa ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'siswa1@quizyfy.com'],
            [
                'name'      => 'Andi Pratama',
                'password'  => Hash::make('password123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa2@quizyfy.com'],
            [
                'name'      => 'Dewi Lestari',
                'password'  => Hash::make('password123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa3@quizyfy.com'],
            [
                'name'      => 'Rizky Firmansyah',
                'password'  => Hash::make('password123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );
    }
}
