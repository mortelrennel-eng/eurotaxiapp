<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\TracksolidService::class);
// Bypass cache and get new token if we want to test that. But let's just dump the raw response of getAllLocations
$response = $service->getAllLocations(true); // Assuming I can modify the service or just make the API call myself.
// Let's just make the API call directly.

$token = $service->getAccessToken();
$params = [
    'method'       => 'jimi.device.track.list',
    'app_key'      => config('services.tracksolid.app_key'),
    'access_token' => $token,
    'timestamp'    => gmdate('Y-m-d H:i:s'),
    'format'       => 'json',
    'v'            => '1.0',
    'sign_method'  => 'md5',
    'target'       => config('services.tracksolid.account'),
];
ksort($params);
$rawString = '';
foreach ($params as $key => $value) {
    if ($key !== 'sign' && !is_null($value) && $value !== '') {
        $rawString .= $key . $value;
    }
}
$appSecret = config('services.tracksolid.app_secret');
$params['sign'] = strtoupper(md5($appSecret . $rawString . $appSecret));

$apiUrl = config('services.tracksolid.api_url');
$response = Illuminate\Support\Facades\Http::asForm()->post($apiUrl, $params);
echo "RAW JSON RESPONSE:\n";
echo $response->body() . "\n";
