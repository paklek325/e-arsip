<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->string('password', 255);

            // tambahkan kolom foto
            $table->string('foto', 255)->nullable();

            // Relasi ke roles
            $table->unsignedBigInteger('id_role');
            $table->foreign('id_role')
                ->references('id_role')
                ->on('roles')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
        });
        Schema::dropIfExists('users');
    }
};
