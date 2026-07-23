<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

function scanDisk($dir = '') {
    $files = Storage::disk('public')->files($dir);
    foreach ($files as $f) {
        echo "FILE: " . $f . PHP_EOL;
    }
    $dirs = Storage::disk('public')->directories($dir);
    foreach ($dirs as $d) {
        scanDisk($d);
    }
}

echo "=== SCANNING STORAGE APP PUBLIC ===" . PHP_EOL;
scanDisk();
