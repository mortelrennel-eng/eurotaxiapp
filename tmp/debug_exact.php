<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\TracksolidService();

// Force dump the configuration
echo "App Key: " . config('services.tracksolid.app_key') . "\n";
echo "App Secret: " . config('services.tracksolid.app_secret') . "\n";
echo "Drift: " . env('TRACKSOLID_TIME_DRIFT') . "\n";

// Manual request to see full error
$time = date('Y-m-d H:i:s', time() + (int)env('TRACKSOLID_TIME_DRIFT', 0));
$pwd = config('services.tracksolid.password');
$pw_md5 = strlen($pwd) === 32 ? $pwd : md5($pwd);

$params = [
    'method'      => 'jimi.oauth.token.get',
    'app_key'     => config('services.tracksolid.app_key'),
    'timestamp'   => $time,
    'format'      => 'json',
    'v'           => '1.0',
    'sign_method' => 'md5',
    'expires_in'  => 7200,
    'user_id'     => config('services.tracksolid.username'),
    'user_pwd_md5'=> $pw_md5,
];

ksort($params);
$raw = '';
foreach ($params as $k => $v) {
    if ($k !== 'sign' && !is_null($v) && $v !== '') $raw .= $k . $v;
}
$sec = config('services.tracksolid.app_secret');
$sign = strtoupper(md5($sec . $raw . $sec));
$params['sign'] = $sign;

echo "Timestamp: $time\n";
echo "Sign: $sign\n";

$resp = Illuminate\Support\Facades\Http::asForm()->post(config('services.tracksolid.api_url'), $params);
echo "Response: " . $resp->body() . "\n";
