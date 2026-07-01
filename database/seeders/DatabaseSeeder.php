<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan semua seeder secara berurutan
        $this->call([
            RolesTableSeeder::class,   // isi role kepala_staf & staf (wajib, FK id_role di users)
            UsersTableSeeder::class,   // isi user admin & staf
            KodeTableSeeder::class,    // isi master kode (wajib ada dulu sebelum surat Keluar diisi)
            // SiswaTableSeeder::class,  // isi contoh data siswas
            SuratTableSeeder::class,   // isi contoh data surat
            UpdateSuratEditorSeeder::class, // lengkapi updated_by; harus SETELAH ada data surat
        ]);
    }
}
