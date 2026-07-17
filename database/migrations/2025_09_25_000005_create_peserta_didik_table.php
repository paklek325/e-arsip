<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peserta_didik', function (Blueprint $table) {
            $table->id('id_peserta_didik');
            $table->string('nama_peserta_didik', 150);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->text('alamat')->nullable();
            $table->enum('rombel', ['A', 'B']);
            $table->string('tahun_angkatan', 10);
            $table->string('file_kk')->nullable();
            $table->string('file_akte')->nullable();
            $table->string('file_ktp')->nullable();
            $table->string('file_ijazah_smp')->nullable();
            $table->string('file_kip')->nullable();
            $table->enum('status', ['lengkap', 'belum lengkap'])->default('belum lengkap');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_didik');
    }
};
