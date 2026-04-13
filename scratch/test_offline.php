<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/live-tracking/units-live', 'GET');
$resp = app(App\Http\Controllers\LiveTrackingController::class)->getUnitsLive($req);
$data = $resp->getData(true);

foreach($data['units'] as $u) {
    if (strpos($u['plate_number'], 'NAN 1349') !== false) {
        print_r($u);
        break;
    }
}
