<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * KENAPA KOLOM BARU, BUKAN PAKAI kode_surat LANGSUNG?
     * ------------------------------------------------------------------
     * FOREIGN KEY murni MySQL berlaku untuk SEMUA baris tanpa terkecuali.
     * Padahal:
     *   - Surat Keluar : kode_surat WAJIB ada di master tabel kode
     *   - Surat Masuk  : kode_surat bebas teks dari instansi pengirim,
     *                    boleh TIDAK ada di master kode
     *
     * Migrasi sebelumnya (2026_07_01_000000) menegakkan aturan ini lewat
     * TRIGGER -- itu sudah cukup untuk validasi data, tapi TRIGGER tidak
     * terdeteksi sebagai relasi oleh tool diagram ERD (yang membaca
     * information_schema.KEY_COLUMN_USAGE / FOREIGN KEY asli).
     *
     * Solusinya: tambah kolom id_kode (nullable) yang benar-benar berupa
     * FOREIGN KEY ke kode.id_kode. Kolom kode_surat (varchar) TETAP ada
     * apa adanya untuk tampilan & pencarian teks serta untuk kode bebas
     * Surat Masuk. id_kode hanya diisi kalau kode_surat itu memang cocok
     * dengan salah satu baris di master tabel kode (SELALU terisi untuk
     * Surat Keluar yang valid, OPSIONAL untuk Surat Masuk).
     */
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {

            $table->foreignId('id_kode')
                ->nullable()
                ->after('kode_surat')
                ->constrained('kode', 'id_kode')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // Backfill: isi id_kode untuk data yang sudah ada, berdasarkan
        // kecocokan kode_surat dengan master tabel kode.
        DB::statement("
            UPDATE surat
            INNER JOIN kode ON surat.kode_surat = kode.kode
            SET surat.id_kode = kode.id_kode
            WHERE surat.kode_surat IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign(['id_kode']);
            $table->dropColumn('id_kode');
        });
    }
};
