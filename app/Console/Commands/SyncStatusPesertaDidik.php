<?php

namespace App\Console\Commands;

use App\Models\PesertaDidik;
use Illuminate\Console\Command;

class SyncStatusPesertaDidik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-status-peserta-didik';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung ulang status kelengkapan berkas (lengkap/belum lengkap) untuk semua data peserta didik yang sudah ada, berdasarkan dokumen wajib (KK, KTP Orang Tua, Akte Kelahiran, Ijazah SMP/MTs). KIP opsional, tidak dihitung.';

    /**
     * Dokumen wajib untuk status "lengkap". Harus SAMA PERSIS dengan
     * daftar di PesertaDidikController::wajibFileFields().
     */
    private array $wajibFileFields = [
        'file_kk',
        'file_akte',
        'file_ktp',
        'file_ijazah_smp',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;
        $total = PesertaDidik::count();

        $this->info("Memproses {$total} data peserta didik...");

        PesertaDidik::chunkById(200, function ($rows) use (&$updated) {
            foreach ($rows as $peserta_didik) {
                $statusBaru = 'lengkap';

                foreach ($this->wajibFileFields as $field) {
                    if (empty($peserta_didik->$field)) {
                        $statusBaru = 'belum lengkap';
                        break;
                    }
                }

                if ($peserta_didik->status !== $statusBaru) {
                    $peserta_didik->update(['status' => $statusBaru]);
                    $updated++;
                }
            }
        }, 'id_peserta_didik');

        $this->info("Selesai. {$updated} dari {$total} data status-nya diperbarui.");

        return self::SUCCESS;
    }
}