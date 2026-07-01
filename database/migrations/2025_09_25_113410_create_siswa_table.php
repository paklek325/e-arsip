<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id('id_siswa');

            // data pribadi
            $table->string('nama_siswa', 150);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->text('alamat');

            // data akademik
            $table->enum('rombel', ['A', 'B']);
            $table->string('tahun_angkatan', 10);

            // file upload (nullable)
            $table->string('file_ppdb')->nullable();
            $table->string('file_kk')->nullable();
            $table->string('file_akte')->nullable();
            $table->string('file_ktp')->nullable();
            $table->string('file_kts')->nullable();
            $table->string('file_foto')->nullable();
            $table->string('file_ijazah_smp')->nullable();
            $table->string('file_ijazah_sma')->nullable();

            // status kelengkapan file otomatis
            $table->enum('status', ['lengkap', 'belum lengkap'])->default('belum lengkap');

            // nama user penginput (bukan FK)
            $table->string('created_by', 150)->nullable();

            // timestamps (harus ada karena model pakai $timestamps = true)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
