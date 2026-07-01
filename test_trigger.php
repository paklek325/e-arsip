<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    // Cari surat keluar yang ada kodenya
    $surat = App\Models\Surat::where('jenis_surat', 'Keluar')->whereNotNull('kode_surat')->first();
    if (!$surat) {
        echo "No surat keluar.\n";
        exit;
    }
    
    echo "Surat Keluar: " . $surat->no_surat . " with kode_surat: " . $surat->kode_surat . "\n";
    
    $kode = App\Models\Kode::where('kode', $surat->kode_surat)->first();
    if ($kode) {
        $old_kode = $kode->kode;
        echo "Found Kode. Changing kode to " . $old_kode . "_X\n";
        $kode->kode = $old_kode . '_X';
        $kode->save();
        
        echo "Kode changed. Now trying to update Surat perihal...\n";
        $surat->perihal = $surat->perihal . " Updated";
        $surat->save();
        
        echo "Surat updated successfully.\n";
        
        // Restore
        $kode->kode = $old_kode;
        $kode->save();
    } else {
        echo "Kode not found in master table.\n";
    }

} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
