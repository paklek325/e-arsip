<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
auth()->login($user);

function testRoute($url, $kernel) {
    echo "--- Testing $url ---\n";
    try {
        $request = Illuminate\Http\Request::create($url, 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        app()->instance('request', $request);
        $response = $kernel->handle($request);
        echo "Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() == 500) {
            echo substr($response->getContent(), 0, 1000) . "\n";
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}

testRoute('/peserta-didik', $kernel);
testRoute('/user', $kernel);
