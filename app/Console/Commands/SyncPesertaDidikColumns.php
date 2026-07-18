<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncPesertaDidikColumns extends Command
{
    protected $signature   = 'peserta-didik:sync-columns';
    protected $description = 'Sinkronisasi kolom tabel peserta_didik ke skema terbaru (KK, KTP, Akte, Ijazah SMP, KIP) tanpa hapus data — pakai ALTER TABLE.';

    /**
     * Kolom dokumen yang HARUS ada di tabel peserta_didik beserta definisinya.
     */
    private array $expectedColumns = [
        'file_kk'         => "VARCHAR(255) NULL COMMENT 'Kartu Keluarga' AFTER `tahun_angkatan`",
        'file_akte'       => "VARCHAR(255) NULL COMMENT 'Akta Kelahiran'",
        'file_ktp'        => "VARCHAR(255) NULL COMMENT 'KTP Orang Tua'",
        'file_ijazah_smp' => "VARCHAR(255) NULL COMMENT 'Ijazah SMP/MTs'",
        'file_kip'        => "VARCHAR(255) NULL COMMENT 'Kartu Indonesia Pintar (opsional)'",
    ];

    /**
     * Kolom lama yang perlu DIHAPUS (dari skema sebelumnya).
     */
    private array $obsoleteColumns = [
        'file_ppdb',        // tidak ada di skema baru
        'file_kts',         // tidak ada di skema baru
        'file_foto',        // tidak ada di skema baru
        'file_ijazah_sma',  // tidak ada di skema baru (diganti file_ijazah_smp)
    ];

    public function handle(): int
    {
        $table      = 'peserta_didik';
        $connection = DB::connection();

        // Ambil kolom yang sudah ada di tabel
        $existing = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))
            ->pluck('Field')
            ->map(fn($f) => strtolower($f))
            ->all();

        $this->info("Kolom saat ini di `{$table}`: " . implode(', ', $existing));
        $this->newLine();

        $changed = false;

        // ── TAMBAH kolom yang belum ada ──────────────────────────────────
        $prevCol = 'tahun_angkatan'; // kolom acuan posisi (AFTER)
        foreach ($this->expectedColumns as $col => $definition) {
            if (!in_array(strtolower($col), $existing)) {
                $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$definition}";
                $this->line("  ✚ Menambahkan kolom <fg=green>`{$col}`</>");
                DB::statement($sql);
                $changed = true;
            } else {
                $this->line("  ✔ Kolom `{$col}` sudah ada, dilewati.");
            }
            $prevCol = $col;
        }

        // ── HAPUS kolom obsolet yang tidak lagi diperlukan ───────────────
        foreach ($this->obsoleteColumns as $col) {
            if (in_array(strtolower($col), $existing)) {
                $this->line("  ✖ Menghapus kolom lama <fg=red>`{$col}`</>");
                DB::statement("ALTER TABLE `{$table}` DROP COLUMN `{$col}`");
                $changed = true;
            }
        }

        // ── Pastikan kolom status masih ada & tipe-nya benar ─────────────
        if (!in_array('status', $existing)) {
            $this->line("  ✚ Menambahkan kolom `status`");
            DB::statement("ALTER TABLE `{$table}` ADD COLUMN `status` ENUM('lengkap','belum lengkap') NOT NULL DEFAULT 'belum lengkap'");
            $changed = true;
        }

        $this->newLine();

        if (!$changed) {
            $this->info('✅ Tidak ada perubahan kolom — tabel sudah sesuai skema terbaru.');
        } else {
            $this->info('✅ Sinkronisasi kolom selesai.');
            $this->newLine();
            $this->comment('Jalankan juga:');
            $this->comment('  php artisan app:sync-status-peserta-didik');
            $this->comment('untuk menghitung ulang status kelengkapan semua data yang sudah ada.');
        }

        return self::SUCCESS;
    }
}
