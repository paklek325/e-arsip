<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Surat;

echo "=== CHECKING DB SURAT vs STORAGE FILES ===" . PHP_EOL;
$allSuratDiskFiles = Illuminate\Support\Facades\Storage::disk('public')->allFiles('surat');

foreach (Surat::all() as $s) {
    echo "Surat ID: {$s->id} | No: {$s->no_surat} | Jenis: {$s->jenis_surat} | DB File Field: " . json_encode($s->file_surat) . PHP_EOL;
}

echo PHP_EOL . "=== ALL SURAT FILES ON DISK ===" . PHP_EOL;
foreach ($allSuratDiskFiles as $f) {
    echo "  " . $f . PHP_EOL;
}
