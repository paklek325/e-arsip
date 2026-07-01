<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah kolom `jurusan` menjadi `rombel` (rombongan belajar).
 *
 * Pemetaan data lama → baru:
 *   IPA  → A
 *   IPS  → B
 *
 * Selain mengubah kolom & data di database, migrasi ini juga memindahkan
 * folder fisik berkas siswa di disk `public` yang sebelumnya bernama
 * siswa/<tahun>/IPA dan siswa/<tahun>/IPS menjadi .../A dan .../B,
 * agar tautan unduh/preview tetap valid (pathFolder memakai nilai rombel).
 */
return new class extends Migration
{
    /**
     * Pemetaan rename: lama => baru
     */
    private array $map = ['IPA' => 'A', 'IPS' => 'B'];

    public function up(): void
    {
        // 1) Pindahkan folder berkas fisik: siswa/<tahun>/IPA → siswa/<tahun>/A, dst.
        $this->moveStorageFolders($this->map);

        // 2) Rename kolom + migrasi data (hanya jika kolom lama masih ada)
        if (Schema::hasColumn('siswa', 'jurusan')) {
            // Lebarkan enum sementara agar menampung nilai lama & baru, sekaligus rename kolom.
            DB::statement("ALTER TABLE `siswa` CHANGE `jurusan` `rombel` ENUM('IPA','IPS','A','B') NOT NULL");

            foreach ($this->map as $lama => $baru) {
                DB::table('siswa')->where('rombel', $lama)->update(['rombel' => $baru]);
            }

            // Persempit enum ke nilai final.
            DB::statement("ALTER TABLE `siswa` MODIFY `rombel` ENUM('A','B') NOT NULL");
        }
    }

    public function down(): void
    {
        // Balik nama folder fisik: A → IPA, B → IPS
        $this->moveStorageFolders(array_flip($this->map));

        if (Schema::hasColumn('siswa', 'rombel')) {
            DB::statement("ALTER TABLE `siswa` CHANGE `rombel` `jurusan` ENUM('IPA','IPS','A','B') NOT NULL");

            foreach ($this->map as $lama => $baru) {
                DB::table('siswa')->where('jurusan', $baru)->update(['jurusan' => $lama]);
            }

            DB::statement("ALTER TABLE `siswa` MODIFY `jurusan` ENUM('IPA','IPS') NOT NULL");
        }
    }

    /**
     * Pindahkan folder siswa/<tahun>/<dari> menjadi siswa/<tahun>/<ke>
     * untuk setiap pemetaan yang diberikan, pada disk `public`.
     *
     * Memakai File facade (operasi rename native) — bukan Storage/Flysystem —
     * karena Flysystem v3 tidak menangani pemindahan direktori (apalagi yang
     * berisi sub-folder) secara andal.
     */
    private function moveStorageFolders(array $map): void
    {
        $base = storage_path('app/public/siswa');

        if (!File::isDirectory($base)) {
            return;
        }

        foreach (File::directories($base) as $tahunDir) {
            foreach ($map as $dari => $ke) {
                $src = $tahunDir . DIRECTORY_SEPARATOR . $dari;
                $dst = $tahunDir . DIRECTORY_SEPARATOR . $ke;

                if (!File::isDirectory($src)) {
                    continue;
                }

                if (!File::isDirectory($dst)) {
                    // Tidak ada konflik → cukup ganti nama folder.
                    File::moveDirectory($src, $dst);
                    continue;
                }

                // Folder tujuan sudah ada → pindahkan isinya satu per satu, lalu hapus folder asal.
                foreach (File::allFiles($src) as $file) {
                    $relative = ltrim(str_replace($src, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                    $target   = $dst . DIRECTORY_SEPARATOR . $relative;

                    File::ensureDirectoryExists(dirname($target));
                    File::move($file->getPathname(), $target);
                }
                File::deleteDirectory($src);
            }
        }
    }
};
