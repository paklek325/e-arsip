<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->bootstrap();
$user = App\Models\User::first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/peserta-didik', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$response = $kernel->handle($request);

echo $response->getStatusCode() . "\n";
echo substr($response->getContent(), 0, 500);
