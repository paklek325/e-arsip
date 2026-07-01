<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SuratTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel surat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('surat')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil user
        $sulthoni = User::where('email', 'admin@admin.com')->value('id_user');
        $ahmad    = User::where('email', 'staf@admin.com')->value('id_user');

        DB::table('surat')->insert([

            /*
            |--------------------------------------------------------------------------
            | SURAT 1
            | Dibuat Sulthoni
            | Belum pernah diedit
            |--------------------------------------------------------------------------
            */
            [
                'no_surat'      => '001/SMK/2026',
                'kode_surat'    => '420/001',
                'jenis_surat'   => 'Masuk',
                'tanggal_surat' => '2026-01-10',
                'perihal'       => 'Undangan Rapat Kepala Sekolah',
                'instansi'      => 'Dinas Pendidikan',
                'pengirim'      => 'Dinas Pendidikan',
                'penerima'      => 'SMA Babussalam',
                'file_surat'    => json_encode([
                    'surat/2026/01/Masuk/undangan.pdf'
                ]),
                'id_user'       => $sulthoni,
                'updated_by'    => $sulthoni,
                'keterangan'    => 'Belum pernah diubah.',
                'created_at'    => now()->subDays(20),
                'updated_at'    => now()->subDays(20),
            ],

            /*
            |--------------------------------------------------------------------------
            | SURAT 2
            | Dibuat Ahmad
            | Belum pernah diedit
            |--------------------------------------------------------------------------
            */
            [
                'no_surat'      => '002/SMK/2026',
                'kode_surat'    => 'S-K',
                'jenis_surat'   => 'Keluar',
                'tanggal_surat' => '2026-01-15',
                'perihal'       => 'Surat Keputusan Panitia',
                'instansi'      => 'SMA Babussalam',
                'pengirim'      => 'SMA Babussalam',
                'penerima'      => 'Guru',
                'file_surat'    => json_encode([
                    'surat/2026/01/Keluar/sk-panitia.pdf'
                ]),
                'id_user'       => $ahmad,
                'updated_by'    => $ahmad,
                'keterangan'    => 'Belum pernah diubah.',
                'created_at'    => now()->subDays(18),
                'updated_at'    => now()->subDays(18),
            ],

            /*
            |--------------------------------------------------------------------------
            | SURAT 3
            | Dibuat Sulthoni
            | Diedit Ahmad
            |--------------------------------------------------------------------------
            */
            [
                'no_surat'      => '003/SMK/2026',
                'kode_surat'    => '421/OPS',
                'jenis_surat'   => 'Masuk',
                'tanggal_surat' => '2026-02-02',
                'perihal'       => 'Permohonan Data Siswa',
                'instansi'      => 'Kementerian Pendidikan',
                'pengirim'      => 'Kemendikbud',
                'penerima'      => 'SMA Babussalam',
                'file_surat'    => json_encode([
                    'surat/2026/02/Masuk/permohonan.pdf'
                ]),
                'id_user'       => $sulthoni,
                'updated_by'    => $ahmad,
                'keterangan'    => 'Tanggal surat diperbaiki.',
                'created_at'    => now()->subDays(15),
                'updated_at'    => now()->subDays(5),
            ],

            /*
            |--------------------------------------------------------------------------
            | SURAT 4
            | Dibuat Ahmad
            | Diedit Sulthoni
            |--------------------------------------------------------------------------
            */
            [
                'no_surat'      => '004/SMK/2026',
                'kode_surat'    => 'S-P',
                'jenis_surat'   => 'Keluar',
                'tanggal_surat' => '2026-02-10',
                'perihal'       => 'Surat Peringatan',
                'instansi'      => 'SMA Babussalam',
                'pengirim'      => 'Kepala Sekolah',
                'penerima'      => 'Guru',
                'file_surat'    => json_encode([
                    'surat/2026/02/Keluar/peringatan.pdf'
                ]),
                'id_user'       => $ahmad,
                'updated_by'    => $sulthoni,
                'keterangan'    => 'Perihal surat diperbarui.',
                'created_at'    => now()->subDays(12),
                'updated_at'    => now()->subDays(2),
            ],

        ]);

        // Isi id_kode (FOREIGN KEY asli ke master tabel kode) untuk data
        // contoh di atas, berdasarkan kecocokan kode_surat dengan kode.kode.
        DB::statement("
            UPDATE surat
            INNER JOIN kode ON surat.kode_surat = kode.kode
            SET surat.id_kode = kode.id_kode
            WHERE surat.kode_surat IS NOT NULL
        ");

        $this->command->info('Seeder surat berhasil dijalankan.');
    }
}
