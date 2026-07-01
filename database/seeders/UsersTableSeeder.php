<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            [
                'email' => 'admin@admin.com',
            ],
            [
                'name'       => 'Sulthoni',
                'password'   => Hash::make('123'),
                'foto'       => 'default_admin.png',
                'id_role'    => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            [
                'email' => 'staf@admin.com',
            ],
            [
                'name'       => 'Ahmad',
                'password'   => Hash::make('123'),
                'foto'       => 'default_staf.png',
                'id_role'    => 2,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
