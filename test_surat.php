<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$kodes = App\Models\Kode::take(5)->get();
foreach ($kodes as $k) {
    echo "Kode: " . $k->kode . " (ID: " . $k->id_kode . ")\n";
}

$surats = App\Models\Surat::with('kode')->take(5)->get();
foreach ($surats as $s) {
    echo "Surat: " . $s->no_surat . ", kode_surat: " . $s->kode_surat . ", relasi kode=" . (optional($s->kode)->kode ?: 'NULL') . "\n";
}
