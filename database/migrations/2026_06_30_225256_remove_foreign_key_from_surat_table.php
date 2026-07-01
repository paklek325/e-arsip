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
            $table->dropForeign('fk_surat_kode_surat');

            // Alternatif jika nama constraint berbeda:
            // $table->dropForeign(['kode_surat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->foreign('kode_surat', 'fk_surat_kode_surat')
                ->references('kode')
                ->on('kode')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }
};
