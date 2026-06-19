<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "1. Getting JWT from SSO...\n";
$loginResponse = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
    'email' => 'warga32@ktp.iae.id',
    'password' => 'KtpDigital2026!'
]);

if ($loginResponse->failed()) {
    echo "Login failed: " . $loginResponse->body() . "\n";
    exit(1);
}

$token = $loginResponse->json('token') ?? $loginResponse->json('access_token');
echo "Token obtained successfully!\n\n";

echo "2. Creating a new listing (POST /api/v1/listings)...\n";
$createResponse = Http::withToken($token)
    ->post('http://localhost:8001/api/v1/listings', [
        'unit_code' => 'UNIT-' . rand(1000, 9999),
        'unit_name' => 'Apartemen Test',
        'tower' => 'Tower A',
        'floor' => 10,
        'room_number' => '10-01',
        'unit_type' => '2BR',
        'status' => 'available',
        'tenant_name' => '',
        'tenant_phone' => ''
    ]);

if ($createResponse->failed()) {
    echo "Create Listing failed: " . $createResponse->body() . "\n";
    exit(1);
}

echo "Create Listing Response:\n";
echo json_encode($createResponse->json(), JSON_PRETTY_PRINT) . "\n";

