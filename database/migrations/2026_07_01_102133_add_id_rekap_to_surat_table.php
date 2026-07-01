<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            // Relasi ke tabel laporan
            $table->unsignedBigInteger('id_rekap')->nullable()->after('id_kode');
            $table->foreign('id_rekap')->references('id_rekap')->on('laporan')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropForeign(['id_rekap']);
            $table->dropColumn('id_rekap');
        });
    }
};
