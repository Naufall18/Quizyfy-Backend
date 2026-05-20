<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Informatika',      'description' => 'Mata pelajaran Informatika dan Teknologi'],
            ['name' => 'Matematika',        'description' => 'Mata pelajaran Matematika'],
            ['name' => 'Bahasa Indonesia',  'description' => 'Mata pelajaran Bahasa Indonesia'],
            ['name' => 'IPA',               'description' => 'Ilmu Pengetahuan Alam'],
            ['name' => 'IPS',               'description' => 'Ilmu Pengetahuan Sosial'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
