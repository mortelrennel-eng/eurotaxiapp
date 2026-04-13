<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = '8FB345B8693CCD00149EBCB96D0EAE85339A22A4105B6558';
$secret = '9ce8f4e1fe3b430c8b94f24aa83b809c';
$user = 'Admin_shiellamarie';
$pwd_md5 = '3406d9a5d03ec8d3c3c7b433eee0a8a7';
$url = 'https://hk-open.tracksolidpro.com/route/rest';

function try_exact_auth($offset_seconds) {
    global $key, $secret, $user, $url, $pwd_md5;
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
        'user_pwd_md5'=> $pwd_md5,
    ];

    ksort($params);
    $raw = '';
    foreach ($params as $k => $v) {
        if ($k !== 'sign' && !is_null($v) && $v !== '') $raw .= $k . $v;
    }
    $params['sign'] = strtoupper(md5($secret . $raw . $secret));

    $resp = Illuminate\Support\Facades\Http::asForm()->post($url, $params);
    return ["time" => $time, "response" => $resp->body()];
}

// Wide sweep around -5 hours
for ($m = -310; $m <= -290; $m++) { // -310 mins to -290 mins (-5h 10m to -4h 50m)
    $res = try_exact_auth($m * 60);
    echo "Offset " . ($m*60) . "s: " . $res['response'] . "\n";
    if (strpos($res['response'], '"code":0') !== false) {
        echo "SUCCESS AT OFFSET " . ($m*60) . "s\n";
        break;
    }
}
