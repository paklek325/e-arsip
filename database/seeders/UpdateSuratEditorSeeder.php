<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSuratEditorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $total = DB::table('surat')->count();

        if ($total === 0) {
            $this->command->warn('Tidak ada data surat.');
            return;
        }

        // Kasus 1: id_user sudah terisi tapi updated_by masih kosong
        // → updated_by ikut nilai id_user (asumsi: belum pernah diedit user lain).
        $affected = DB::table('surat')
            ->whereNull('updated_by')
            ->whereNotNull('id_user')
            ->update([
                'updated_by' => DB::raw('id_user'),
            ]);

        $this->command->info("Berhasil memperbarui {$affected} data surat (updated_by mengikuti id_user).");

        // Kasus 2: id_user DAN updated_by sama-sama kosong (data legacy tanpa pencatatan).
        // Pembuat aslinya tidak bisa direkonstruksi dari data yang ada, jadi diisi
        // ke user default (ganti $defaultUserId sesuai id_user admin/superadmin Anda).
        $defaultUserId = (int) (env('SURAT_LEGACY_DEFAULT_USER_ID', 0));

        if ($defaultUserId > 0) {
            $affectedLegacy = DB::table('surat')
                ->whereNull('id_user')
                ->whereNull('updated_by')
                ->update([
                    'id_user'    => $defaultUserId,
                    'updated_by' => $defaultUserId,
                ]);

            $this->command->info("Berhasil memperbarui {$affectedLegacy} data surat legacy (id_user & updated_by diisi default user #{$defaultUserId}).");
        } else {
            $sisa = DB::table('surat')
                ->whereNull('id_user')
                ->whereNull('updated_by')
                ->count();

            if ($sisa > 0) {
                $this->command->warn("Masih ada {$sisa} data surat tanpa id_user & updated_by. Set env SURAT_LEGACY_DEFAULT_USER_ID lalu jalankan ulang seeder ini untuk mengisi user default.");
            }
        }
    }
}
