<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\LiveTrackingController;

$controller = $app->make(LiveTrackingController::class);
$response = $controller->getUnitsLive();
$data = json_decode($response->getContent(), true);

foreach($data['units'] as $u) {
    if ($u['plate_number'] == 'NEF 4940') {
        print_r($u);
    }
}
