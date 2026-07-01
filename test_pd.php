<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/peserta-didik', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');

$kernel->bootstrap();
// We don't login because middleware might redirect us to login, but let's see.
$response = $kernel->handle($request);

echo $response->getStatusCode() . "\n";
echo substr($response->getContent(), 0, 500);
