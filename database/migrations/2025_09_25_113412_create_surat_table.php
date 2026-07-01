<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();

            // Nomor surat (tidak pakai unique di kolom ini)
            $table->string('no_surat', 100);

            $table->string('kode_surat', 50)->nullable();
            $table->enum('jenis_surat', ['Masuk', 'Keluar']);
            $table->date('tanggal_surat');
            $table->string('perihal', 255);

            // Saran: instansi wajib supaya kombinasi unik jelas
            $table->string('instansi', 255);

            $table->string('pengirim', 255)->nullable();
            $table->string('penerima', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->json('file_surat')->nullable();
            $table->string('created_by', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
