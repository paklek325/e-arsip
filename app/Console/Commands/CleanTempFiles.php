<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTempFiles extends Command
{
    /**
     * Nama & signature command console.
     *
     * --hours menentukan usia minimum file (dalam jam) sebelum dihapus.
     * Default 24 jam, supaya backup yang baru saja dibuat (dan mungkin
     * sedang dalam proses diunduh) tidak ikut terhapus.
     */
    protected $signature = 'app:clean-temp-files {--hours=24 : Usia minimum file (jam) sebelum dihapus}';

    protected $description = 'Menghapus file ZIP backup sisa di storage/app/temp_backup yang sudah tidak terpakai (gagal/lupa diunduh)';

    public function handle(): int
    {
        $folder      = storage_path('app/temp_backup');
        $minAgeHours = (int) $this->option('hours');

        if (!File::isDirectory($folder)) {
            $this->info("Folder temp_backup belum ada, tidak ada yang perlu dibersihkan.");
            return self::SUCCESS;
        }

        $now     = now();
        $deleted = 0;

        foreach (File::files($folder) as $file) {
            $ageInHours = $now->diffInHours(
                \Illuminate\Support\Carbon::createFromTimestamp($file->getMTime())
            );

            if ($ageInHours >= $minAgeHours) {
                File::delete($file->getPathname());
                $deleted++;
                $this->line("Dihapus: {$file->getFilename()} (umur {$ageInHours} jam)");
            }
        }

        $this->info("Selesai. {$deleted} file backup sisa berhasil dihapus dari temp_backup.");

        return self::SUCCESS;
    }
}
