<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengganti kolom 'created_by' (varchar nama) menjadi 'id_user'
     * (foreign key ke tabel users) pada tabel 'surat' dan 'siswa'.
     *
     * ON DELETE SET NULL dipakai supaya kalau user dihapus,
     * data surat/siswa TIDAK ikut terhapus -- hanya id_user-nya
     * menjadi NULL.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom id_user (nullable dulu, biar bisa diisi dari data lama)
        Schema::table('surat', function (Blueprint $table) {
            $table->foreignId('id_user')
                ->nullable()
                ->after('created_by')
                ->constrained('users', 'id_user')
                ->nullOnDelete();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('id_user')
                ->nullable()
                ->after('created_by')
                ->constrained('users', 'id_user')
                ->nullOnDelete();
        });

        // 2. Migrasi data lama: cocokkan created_by (nama) -> users.name
        //    Kalau nama tidak ditemukan di tabel users, id_user dibiarkan NULL.
        $users = DB::table('users')->pluck('id_user', 'name');

        DB::table('surat')->whereNotNull('created_by')->orderBy('id')
            ->chunk(200, function ($rows) use ($users) {
                foreach ($rows as $row) {
                    if (isset($users[$row->created_by])) {
                        DB::table('surat')->where('id', $row->id)
                            ->update(['id_user' => $users[$row->created_by]]);
                    }
                }
            });

        DB::table('siswa')->whereNotNull('created_by')->orderBy('id_siswa')
            ->chunk(200, function ($rows) use ($users) {
                foreach ($rows as $row) {
                    if (isset($users[$row->created_by])) {
                        DB::table('siswa')->where('id_siswa', $row->id_siswa)
                            ->update(['id_user' => $users[$row->created_by]]);
                    }
                }
            });

        // 3. Hapus kolom created_by yang lama
        Schema::table('surat', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->string('created_by', 100)->nullable()->after('keterangan');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('created_by', 150)->nullable()->after('status');
        });

        // Kembalikan nama dari relasi id_user sebelum kolomnya dihapus
        DB::table('surat')->whereNotNull('id_user')->orderBy('id')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $name = DB::table('users')->where('id_user', $row->id_user)->value('name');
                    if ($name) {
                        DB::table('surat')->where('id', $row->id)->update(['created_by' => $name]);
                    }
                }
            });

        DB::table('siswa')->whereNotNull('id_user')->orderBy('id_siswa')
            ->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    $name = DB::table('users')->where('id_user', $row->id_user)->value('name');
                    if ($name) {
                        DB::table('siswa')->where('id_siswa', $row->id_siswa)->update(['created_by' => $name]);
                    }
                }
            });

        Schema::table('surat', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_user');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_user');
        });
    }
};
