<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\TracksolidService::class);
$data = $service->getAllLocations();

echo "Raw Tracksolid Response Sample:\n";
if ($data && count($data) > 0) {
    print_r($data[0]);
} else {
    echo "No devices found or API error.\n";
    print_r($data);
}
