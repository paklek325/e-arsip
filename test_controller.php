<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/peserta-didik', 'GET');
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
app()->instance('request', $request);

$user = App\Models\User::first();
auth()->login($user);

$controller = app()->make(\App\Http\Controllers\PesertaDidikController::class);
try {
    $response = $controller->index($request);
    if ($response instanceof \Illuminate\View\View) {
        echo $response->render();
    } else if (is_string($response)) {
        echo $response;
    } else {
        echo get_class($response);
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
