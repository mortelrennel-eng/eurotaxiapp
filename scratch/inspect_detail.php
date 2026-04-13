<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(App\Services\TracksolidService::class);
$token = $service->getAccessToken();

// Let's try jimi.track.device.detail for a specific IMEI
$params = [
    'method'       => 'jimi.track.device.detail',
    'app_key'      => config('services.tracksolid.app_key'),
    'access_token' => $token,
    'timestamp'    => gmdate('Y-m-d H:i:s'),
    'format'       => 'json',
    'v'            => '1.0',
    'sign_method'  => 'md5',
    'imei'         => '352503097297284', // First imei from previous list
];

// Re-implement signature generation for scratch script
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
$data = $response->json();

echo "Device Detail Response:\n";
print_r($data);
