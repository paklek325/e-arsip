<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Batasi aplikasi hanya boleh memiliki SATU Kepala Staf.
     *
     * Menggunakan functional unique index (MySQL 8.0.13+):
     * ekspresi hanya bernilai 1 untuk baris Kepala Staf, dan NULL untuk
     * baris lain. Karena MySQL mengizinkan NULL berulang pada unique index,
     * baris non-Kepala-Staf tidak terbatas, sedangkan baris Kepala Staf
     * dipaksa hanya boleh satu.
     */
    public function up(): void
    {
        $kepalaRoleId = DB::table('roles')->where('name', 'Kepala Staf')->value('id_role');
        $stafRoleId   = DB::table('roles')->where('name', 'Staf')->value('id_role');

        // Bereskan data lama: bila ada lebih dari satu Kepala Staf,
        // pertahankan yang terlama (id_user terkecil) dan turunkan sisanya
        // menjadi Staf agar unique index dapat dibuat & akun tetap ada.
        if ($kepalaRoleId && $stafRoleId) {
            $firstKepala = DB::table('users')
                ->where('id_role', $kepalaRoleId)
                ->orderBy('id_user')
                ->value('id_user');

            if ($firstKepala) {
                DB::table('users')
                    ->where('id_role', $kepalaRoleId)
                    ->where('id_user', '!=', $firstKepala)
                    ->update(['id_role' => $stafRoleId, 'updated_at' => now()]);
            }
        }

        // Hardcode id_role di ekspresi index karena index tidak boleh
        // memakai subquery. Nilai id_role Kepala Staf diambil dinamis di atas.
        $kepalaRoleId = (int) $kepalaRoleId;

        DB::statement(
            "CREATE UNIQUE INDEX users_single_kepala_staf_unique
             ON users ((CASE WHEN id_role = {$kepalaRoleId} THEN 1 END))"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX users_single_kepala_staf_unique ON users');
    }
};
