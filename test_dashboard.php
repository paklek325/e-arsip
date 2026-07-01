<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::find(1);
Illuminate\Support\Facades\Auth::login($user);

$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$response = app()->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo substr($response->getContent(), 0, 500);
} else {
    echo "SUCCESS\n";
    // echo substr($response->getContent(), -2000);
}
