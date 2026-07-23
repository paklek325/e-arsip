<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Surat;
use App\Models\PesertaDidik;

echo "Total Data Surat di DB: " . Surat::count() . PHP_EOL;
echo "Surat Masuk: " . Surat::where('jenis_surat', 'Masuk')->count() . PHP_EOL;
echo "Surat Keluar: " . Surat::where('jenis_surat', 'Keluar')->count() . PHP_EOL;

echo "Total Data Peserta Didik di DB: " . PesertaDidik::count() . PHP_EOL;
