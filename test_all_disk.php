<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== DISK PUBLIC REAL ABSOLUTE PATH ===" . PHP_EOL;
echo storage_path('app/public') . PHP_EOL . PHP_EOL;

echo "=== ALL FILES FOUND IN DISK PUBLIC ===" . PHP_EOL;
$files = Storage::disk('public')->allFiles();
foreach ($files as $f) {
    echo $f . PHP_EOL;
}
