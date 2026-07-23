<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Storage;

echo "=== USERS FOTO IN DB vs DISK ===" . PHP_EOL;
foreach (User::all() as $u) {
    $exists = $u->foto ? Storage::disk('public')->exists('foto_admin/' . $u->foto) : false;
    echo "ID: {$u->id_user} | Name: {$u->name} | DB Foto: {$u->foto} | File Exists on Disk: " . ($exists ? 'YES' : 'NO (404 Error)') . PHP_EOL;
}
