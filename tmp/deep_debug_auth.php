<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = config('services.tracksolid.app_key');
$secret = config('services.tracksolid.app_secret');
$user = config('services.tracksolid.username');

echo "Key: [". $key . "] Length: " . strlen($key) . "\n";
echo "Secret: [". $secret . "] Length: " . strlen($secret) . "\n";
echo "User: [". $user . "] Length: " . strlen($user) . "\n";

// Try different signature logic
function try_auth_variants($offset_seconds) {
    global $key, $secret, $user;
    $time = date('Y-m-d H:i:s', time() + $offset_seconds);
    
    $params = [
        'method'      => 'jimi.oauth.token.get',
        'app_key'     => $key,
        'timestamp'   => $time,
        'format'      => 'json',
        'v'           => '1.0',
        'sign_method' => 'md5',
        'expires_in'  => 7200,
        'user_id'     => $user,
        'user_pwd_md5'=> config('services.tracksolid.password'),
    ];

    ksort($params);
    $raw = '';
    foreach ($params as $k => $v) {
        if ($k !== 'sign' && !is_null($v) && $v !== '') $raw .= $k . $v;
    }
    
    // Variant 1: secret + raw + secret (Common)
    $sign1 = strtoupper(md5($secret . $raw . $secret));
    
    // Variant 2: raw + secret (Standard API)
    $sign2 = strtoupper(md5($raw . $secret));
    
    // Variant 3: secret + raw (Less common)
    $sign3 = strtoupper(md5($secret . $raw));

    $api = config('services.tracksolid.api_url');
    
    echo "Time: $time\n";
    foreach (['V1' => $sign1, 'V2' => $sign2, 'V3' => $sign3] as $vName => $s) {
        $p = $params;
        $p['sign'] = $s;
        $resp = Illuminate\Support\Facades\Http::asForm()->post($api, $p);
        echo "$vName: {$resp->body()}\n";
    }
}

try_auth_variants(-18000); // 5 hour subtraction
