<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->index('tahun_angkatan');
            $table->index('rombel');
            $table->index('jenis_kelamin');
            $table->index('status');
            $table->index(['tahun_angkatan', 'rombel']);
            $table->index(['tahun_angkatan', 'jenis_kelamin']);
            $table->index(['status', 'tahun_angkatan']);
        });

        Schema::table('surat', function (Blueprint $table) {
            $table->index('jenis_surat');
            $table->index('tanggal_surat');
            $table->index(['jenis_surat', 'tanggal_surat']);
        });
    }

    public function down(): void
    {
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->dropIndex(['tahun_angkatan']);
            $table->dropIndex(['rombel']);
            $table->dropIndex(['jenis_kelamin']);
            $table->dropIndex(['status']);
            $table->dropIndex(['tahun_angkatan', 'rombel']);
            $table->dropIndex(['tahun_angkatan', 'jenis_kelamin']);
            $table->dropIndex(['status', 'tahun_angkatan']);
        });

        Schema::table('surat', function (Blueprint $table) {
            $table->dropIndex(['jenis_surat']);
            $table->dropIndex(['tanggal_surat']);
            $table->dropIndex(['jenis_surat', 'tanggal_surat']);
        });
    }
};
