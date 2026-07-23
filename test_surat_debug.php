<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\Surat::all() as $s) {
    echo "ID: " . $s->id . " | Jenis: " . $s->jenis_surat . " | Raw File: " . json_encode($s->getRawOriginal('file_surat')) . " | Cast File: " . json_encode($s->file_surat) . PHP_EOL;
}
