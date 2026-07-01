<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('siswa', 'peserta_didik');

        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->renameColumn('id_siswa', 'id_peserta_didik');
            $table->renameColumn('nama_siswa', 'nama_peserta_didik');
        });
    }

    public function down(): void
    {
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->renameColumn('id_peserta_didik', 'id_siswa');
            $table->renameColumn('nama_peserta_didik', 'nama_siswa');
        });

        Schema::rename('peserta_didik', 'siswa');
    }
};
