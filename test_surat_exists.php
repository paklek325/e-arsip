<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

foreach (App\Models\Surat::all() as $s) {
    echo "--- SURAT ID {$s->id} ({$s->jenis_surat}) ---" . PHP_EOL;
    $files = is_array($s->file_surat) ? $s->file_surat : [];
    foreach ($files as $f) {
        $exists = Storage::disk('public')->exists($f);
        echo "   File: {$f} | Exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;
    }
}
