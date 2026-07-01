<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'Kepala Staf',
                'description' => 'Memiliki akses penuh terhadap sistem',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staf',
                'description' => 'Hanya dapat mengelola data tertentu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
