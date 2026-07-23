<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PesertaDidik;
use Illuminate\Support\Facades\Storage;

echo "=== DB PESERTA DIDIK ===" . PHP_EOL;
foreach (PesertaDidik::all() as $p) {
    echo "ID: {$p->id_peserta_didik} | Nama: {$p->nama_peserta_didik} | Angkatan: {$p->tahun_angkatan} | Rombel: {$p->rombel}" . PHP_EOL;
    echo "   KK: " . json_encode($p->file_kk) . PHP_EOL;
    echo "   Akte: " . json_encode($p->file_akte) . PHP_EOL;
    echo "   KTP: " . json_encode($p->file_ktp) . PHP_EOL;
    echo "   Ijazah SMP: " . json_encode($p->file_ijazah_smp) . PHP_EOL;
    echo "   KIP: " . json_encode($p->file_kip) . PHP_EOL;
}

echo PHP_EOL . "=== STORAGE DISK FILES IN peserta_didik ===" . PHP_EOL;
$files = Storage::disk('public')->allFiles('peserta_didik');
foreach ($files as $f) {
    echo "  " . $f . PHP_EOL;
}
