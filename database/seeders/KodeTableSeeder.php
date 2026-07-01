<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kode' => 'S-K',
                'description' => 'Surat Keputusan',
            ],
            [
                'kode' => 'S-Ket',
                'description' => 'Surat Keterangan',
            ],
            [
                'kode' => 'S-P',
                'description' => 'Surat Peringatan',
            ],
            [
                'kode' => 'P',
                'description' => 'Surat Pengumuman',
            ],
            [
                'kode' => 'S-T',
                'description' => 'Surat Tugas',
            ],
            [
                'kode' => 'S-U',
                'description' => 'Surat Undangan',
            ],
        ];

        foreach ($data as $item) {
            DB::table('kode')->updateOrInsert(
                [
                    'kode' => $item['kode'],
                ],
                [
                    'description' => $item['description'],
                ]
            );
        }

        $this->command->info('Data kode surat berhasil diperbarui.');
    }
}
