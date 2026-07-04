<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('no_surat', 100);
            $table->string('kode_surat', 10)->nullable();
            $table->foreignId('id_kode')
                ->nullable()
                ->constrained('kode', 'id_kode')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->unsignedBigInteger('id_rekap')->nullable();
            $table->foreign('id_rekap')
                ->references('id_rekap')
                ->on('laporan')
                ->nullOnDelete();
            $table->enum('jenis_surat', ['Masuk', 'Keluar']);
            $table->date('tanggal_surat');
            $table->string('perihal', 255);
            $table->string('instansi', 255)->nullable();
            $table->string('pengirim', 255)->nullable();
            $table->string('penerima', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->json('file_surat')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
            $table->timestamps();
        });

        // Trigger: Surat Keluar wajib pakai kode dari tabel master kode
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_insert');
        DB::unprepared("
            CREATE TRIGGER trg_surat_kode_before_insert
            BEFORE INSERT ON surat FOR EACH ROW
            BEGIN
                IF NEW.jenis_surat = 'Keluar'
                   AND NEW.kode_surat IS NOT NULL
                   AND NOT EXISTS (SELECT 1 FROM kode WHERE kode.kode = NEW.kode_surat) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'kode_surat untuk Surat Keluar harus terdaftar di tabel master kode.';
                END IF;
            END
        ");

        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_update');
        DB::unprepared("
            CREATE TRIGGER trg_surat_kode_before_update
            BEFORE UPDATE ON surat FOR EACH ROW
            BEGIN
                IF NEW.jenis_surat = 'Keluar'
                   AND NEW.kode_surat IS NOT NULL
                   AND NOT EXISTS (SELECT 1 FROM kode WHERE kode.kode = NEW.kode_surat) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'kode_surat untuk Surat Keluar harus terdaftar di tabel master kode.';
                END IF;
            END
        ");

        // Trigger: kode di master tidak bisa dihapus jika masih dipakai Surat Keluar
        DB::unprepared('DROP TRIGGER IF EXISTS trg_kode_before_delete');
        DB::unprepared("
            CREATE TRIGGER trg_kode_before_delete
            BEFORE DELETE ON kode FOR EACH ROW
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM surat
                    WHERE surat.kode_surat = OLD.kode AND surat.jenis_surat = 'Keluar'
                ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Kode tidak bisa dihapus karena masih dipakai oleh Surat Keluar.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_surat_kode_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_kode_before_delete');
        Schema::dropIfExists('surat');
    }
};
