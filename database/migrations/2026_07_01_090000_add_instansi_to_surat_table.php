<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom 'instansi' yang hilang dari tabel surat.
     *
     * Kolom ini ada di fillable model, semua controller, form request,
     * dan seeder — tapi tidak pernah dibuat di migration awal maupun
     * migration berikutnya. Akibatnya INSERT/UPDATE surat selalu gagal
     * karena Eloquent mencoba menulis ke kolom yang tidak ada.
     */
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // Tambahkan setelah 'perihal' agar urutan kolom logis
            $table->string('instansi', 255)->nullable()->after('perihal');
        });
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropColumn('instansi');
        });
    }
};
