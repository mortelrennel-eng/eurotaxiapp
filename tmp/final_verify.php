<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\TracksolidService();
$token = $service->getAccessToken();

if ($token) {
    echo "SUCCESS! Access Token: " . $token . "\n";
    // Test getAllLocations if token is valid
    $locations = $service->getAllLocations();
    if ($locations) {
        echo "SUCCESS! Fetched " . count($locations) . " unit locations.\n";
    } else {
        echo "FAILED to fetch locations (check signature/params).\n";
    }
} else {
    echo "FAILED to get access token.\n";
}
