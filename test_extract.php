<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = App\Models\User::find(1);
Illuminate\Support\Facades\Auth::login($user);
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$response = app()->handle($request);
$html = $response->getContent();
preg_match('/const peserta_didikPerTahun = (.*?);/s', $html, $matches1);
preg_match('/const dataPesertaDidikSemua = (.*?);/s', $html, $matches2);
echo "peserta_didikPerTahun: " . ($matches1[1] ?? 'NOT FOUND') . "\n";
echo "dataPesertaDidikSemua: " . ($matches2[1] ?? 'NOT FOUND') . "\n";
