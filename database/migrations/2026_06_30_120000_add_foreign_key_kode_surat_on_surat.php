<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan FOREIGN KEY asli: surat.kode_surat -> kode.kode
     *
     * Kenapa belum ada sebelumnya:
     * - kode.kode    : varchar(10)  UNIQUE
     * - surat.kode_surat : varchar(50) (tanpa constraint)
     * Panjang kolom beda, jadi harus disamakan dulu sebelum FK bisa dibuat.
     */
    public function up(): void
    {
        // 1) Samakan panjang kolom surat.kode_surat menjadi varchar(10)
        //    agar match dengan kode.kode (syarat MySQL untuk FK).
        //    Pakai raw SQL (bukan ->change()) supaya tidak perlu paket doctrine/dbal.
        DB::statement('ALTER TABLE surat MODIFY kode_surat VARCHAR(10) NULL');

        // 2) Bersihkan data "yatim": kode_surat yang nilainya tidak ada
        //    di tabel kode akan di-NULL-kan dulu, supaya constraint tidak gagal.
        DB::statement("
            UPDATE surat
            LEFT JOIN kode ON surat.kode_surat = kode.kode
            SET surat.kode_surat = NULL
            WHERE surat.kode_surat IS NOT NULL
              AND kode.kode IS NULL
        ");

        // 3) Tambahkan foreign key constraint asli.
        //    ON UPDATE CASCADE: kalau 'kode' di tabel kode diubah, otomatis ikut di surat
        //    ON DELETE SET NULL: kalau kode dihapus, surat tidak ikut terhapus, hanya kode_surat jadi NULL
        Schema::table('surat', function (Blueprint $table) {
            $table->foreign('kode_surat', 'fk_surat_kode_surat')
                ->references('kode')->on('kode')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign('fk_surat_kode_surat');
        });

        DB::statement('ALTER TABLE surat MODIFY kode_surat VARCHAR(50) NULL');
    }
};
