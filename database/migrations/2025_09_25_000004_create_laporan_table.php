<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id_rekap');
            $table->integer('tahun');
            $table->integer('bulan')->nullable();
            $table->enum('tipe_rekap', ['Tahun', 'Bulan', 'Minggu']);
            $table->integer('total_surat_masuk')->default(0);
            $table->integer('total_surat_keluar')->default(0);
            $table->integer('total_jenis')->default(0);
            $table->string('dibuat_oleh', 150)->nullable();
            $table->unique(['tahun', 'bulan', 'tipe_rekap'], 'idx_rekap_periode');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
