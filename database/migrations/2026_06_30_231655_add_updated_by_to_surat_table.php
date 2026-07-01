<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom updated_by
     */
    public function up(): void
    {
        Schema::table('surat', function (Blueprint $table) {

            $table->unsignedBigInteger('updated_by')
                ->nullable()
                ->after('id_user');

            $table->foreign('updated_by', 'fk_surat_updated_by')
                ->references('id_user')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Menghapus kolom updated_by
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {

            $table->dropForeign('fk_surat_updated_by');

            $table->dropColumn('updated_by');
        });
    }
};
