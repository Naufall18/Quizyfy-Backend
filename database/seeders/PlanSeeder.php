<?php

namespace Database\Seeders;
use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void{
Plan::insert([
    ['type'=>'basic',      'duration_months'=>1,  'price'=>0   ],
    ['type'=>'premium',    'duration_months'=>6,  'price'=>500000],
    ['type'=>'enterprise', 'duration_months'=>12, 'price'=>900000],
]);
    }
}
