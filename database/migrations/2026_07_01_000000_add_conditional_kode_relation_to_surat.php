<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RELASI surat.kode_surat -> kode.kode
     * ------------------------------------------------------------------
     * Migrasi 2026_06_30_120000 sempat menambahkan FOREIGN KEY asli, tapi
     * langsung dihapus lagi oleh migrasi 2026_06_30_225256. Alasannya:
     * FOREIGN KEY murni MySQL berlaku untuk SEMUA baris, padahal kebutuhan
     * aplikasi berbeda per jenis surat:
     *
     *   - Surat Keluar  : kode_surat WAJIB diambil dari master tabel kode
     *                     (lihat StoreSuratRequest/UpdateSuratRequest:
     *                     Rule::when jenis_surat=Keluar -> exists:kode,kode)
     *   - Surat Masuk   : kode_surat bebas/teks dari instansi pengirim,
     *                     TIDAK wajib ada di master kode.
     *
     * FOREIGN KEY biasa tidak bisa "kondisional" seperti itu, sehingga
     * solusinya dibuat lewat TRIGGER: integritas relasi tetap ditegakkan
     * di level database (bukan cuma di level aplikasi/Eloquent), tapi
     * hanya untuk Surat Keluar. Surat Masuk tetap bebas seperti semula.
     */
    public function up(): void
    {
        // 1) Samakan kembali panjang kolom dengan kode.kode (varchar(10)).
        DB::statement('ALTER TABLE surat MODIFY kode_surat VARCHAR(10) NULL');

        // 2) Bersihkan data Surat Keluar yang kode_surat-nya yatim
        //    (tidak ada di master kode), supaya trigger di bawah tidak
        //    langsung menolak data lama yang sudah terlanjur tidak valid.
        DB::statement("
            UPDATE surat
            LEFT JOIN kode ON surat.kode_surat = kode.kode
            SET surat.kode_surat = NULL
            WHERE surat.jenis_surat = 'Keluar'
              AND surat.kode_surat IS NOT NULL
              AND kode.kode IS NULL
        ");

        // 3) TRIGGER: tolak INSERT/UPDATE surat Keluar yang kode_surat-nya
        //    tidak terdaftar di tabel kode.
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_insert');
        DB::unprepared("
            CREATE TRIGGER trg_surat_kode_before_insert
            BEFORE INSERT ON surat
            FOR EACH ROW
            BEGIN
                IF NEW.jenis_surat = 'Keluar'
                   AND NEW.kode_surat IS NOT NULL
                   AND NOT EXISTS (SELECT 1 FROM kode WHERE kode.kode = NEW.kode_surat) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'kode_surat untuk Surat Keluar harus terdaftar di tabel master kode.';
                END IF;
            END
        ");

        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_update');
        DB::unprepared("
            CREATE TRIGGER trg_surat_kode_before_update
            BEFORE UPDATE ON surat
            FOR EACH ROW
            BEGIN
                IF NEW.jenis_surat = 'Keluar'
                   AND NEW.kode_surat IS NOT NULL
                   AND NOT EXISTS (SELECT 1 FROM kode WHERE kode.kode = NEW.kode_surat) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'kode_surat untuk Surat Keluar harus terdaftar di tabel master kode.';
                END IF;
            END
        ");

        // 4) TRIGGER: cegah penghapusan kode di tabel master jika kode
        //    tersebut masih dipakai oleh Surat Keluar (mencegah data surat
        //    Keluar kehilangan rujukan kodenya secara tidak sengaja).
        DB::unprepared('DROP TRIGGER IF EXISTS trg_kode_before_delete');
        DB::unprepared("
            CREATE TRIGGER trg_kode_before_delete
            BEFORE DELETE ON kode
            FOR EACH ROW
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM surat
                    WHERE surat.kode_surat = OLD.kode
                      AND surat.jenis_surat = 'Keluar'
                ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Kode tidak bisa dihapus karena masih dipakai oleh Surat Keluar.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_kode_before_delete');
    }
};
