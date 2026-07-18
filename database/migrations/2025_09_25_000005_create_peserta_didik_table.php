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
            // Dokumen berkas peserta didik
            $table->string('file_kk')->nullable()->comment('Kartu Keluarga');
            $table->string('file_akte')->nullable()->comment('Akta Kelahiran');
            $table->string('file_ktp')->nullable()->comment('KTP Orang Tua');
            $table->string('file_ijazah_smp')->nullable()->comment('Ijazah SMP/MTs');
            $table->string('file_kip')->nullable()->comment('Kartu Indonesia Pintar (opsional)');
            // Status dihitung otomatis dari 4 dok wajib (KK, Akte, KTP, Ijazah)
            // KIP tidak wajib — tidak mempengaruhi status
            $table->enum('status', ['lengkap', 'belum lengkap'])->default('belum lengkap');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();

            // Indexes for Performance (Merged from add_performance_indexes)
            $table->index('tahun_angkatan');
            $table->index('rombel');
            $table->index('jenis_kelamin');
            $table->index('status');
            $table->index(['tahun_angkatan', 'rombel']);
            $table->index(['tahun_angkatan', 'jenis_kelamin']);
            $table->index(['status', 'tahun_angkatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_didik');
    }
};
