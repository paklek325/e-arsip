<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

        // Buat functional unique index agar hanya ada 1 Kepala Staf.
        // Dilakukan di sini karena index butuh id_role yang baru diketahui setelah seeding.
        $kepalaRoleId = (int) DB::table('roles')->where('name', 'Kepala Staf')->value('id_role');
        if ($kepalaRoleId > 0) {
            try {
                DB::statement('DROP INDEX users_single_kepala_staf_unique ON users');
            } catch (\Throwable) {}
            DB::statement(
                "CREATE UNIQUE INDEX users_single_kepala_staf_unique
                 ON users ((CASE WHEN id_role = {$kepalaRoleId} THEN 1 END))"
            );
        }
    }
}
