<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\User;

class SiswaTableSeeder extends Seeder
{
    public function run(): void
    {
        $sulthoni = User::where('name', 'Sulthoni')->value('id_user');
        $ahmad    = User::where('name', 'Ahmad')->value('id_user');

        Siswa::create([
            'nama_siswa' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2005-08-21',
            'alamat' => 'Jl. Merdeka No. 12 Jakarta',
            'rombel' => 'A',
            'tahun_angkatan' => '2023',
            'id_user' => $sulthoni,
        ]);

        Siswa::create([
            'nama_siswa' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2006-01-11',
            'alamat' => 'Jl. Asia Afrika No. 45 Bandung',
            'rombel' => 'B',
            'tahun_angkatan' => '2023',
            'id_user' => $ahmad,
        ]);
    }
}
