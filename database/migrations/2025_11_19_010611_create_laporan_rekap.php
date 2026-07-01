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
        // DIUBAH: Nama tabel menjadi 'laporan' agar konsisten dengan LaporanSeeder.php
        Schema::create('laporan', function (Blueprint $table) { 
            // Kolom Primary Key dan Auto-Increment
            // Menggunakan id() akan otomatis membuat kolom bigint UNSIGNED dan primary key
            $table->id('id_rekap'); 

            // Kolom Data Periode
            $table->integer('tahun')->comment('Tahun rekap data (contoh: 2025)');
            $table->integer('bulan')->nullable()->comment('Bulan rekap (1-12). NULL jika rekap Tahunan.');
            $table->enum('tipe_rekap', ['Tahun', 'Bulan', 'Minggu'])->comment('Tipe periode laporan');
            
            // Kolom Angka Laporan
            $table->integer('total_surat_masuk')->default(0);
            $table->integer('total_surat_keluar')->default(0);
            
            // BARU: Kolom untuk menyimpan total gabungan surat masuk dan keluar
            $table->integer('total_jenis')
                  ->default(0)
                  ->comment('Total gabungan Surat Masuk dan Surat Keluar'); 
            
            // Kolom Lainnya
            $table->string('dibuat_oleh', 150)->nullable(); // Sudah ada dan diizinkan null

            // Unique Index untuk mencegah duplikasi periode
            $table->unique(['tahun', 'bulan', 'tipe_rekap'], 'idx_rekap_periode');

            // Kolom Timestamp
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DIUBAH: Nama tabel menjadi 'laporan'
        Schema::dropIfExists('laporan'); 
    }
};