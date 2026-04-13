<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$full_key = '8FB345B8693CCD00149EBCB96D0EAE85339A22A4105B6558';
$env_secret = '9ce8f4e1fe3b430c8b94f24aa83b809c';
$user = 'Admin_shiellamarie';
$pwd_md5 = '3406d9a5d03ec8d3c3c7b433eee0a8a7';
$url = 'https://hk-open.tracksolidpro.com/route/rest';

$possible_keys = [
    $full_key,
    substr($full_key, 0, 32),
    substr($full_key, 32),
];

$possible_secrets = [
    $env_secret,
    substr($full_key, 0, 16),
    substr($full_key, 32),
];

function try_combo($k, $s, $offset) {
    global $user, $url, $pwd_md5;
    $time = date('Y-m-d H:i:s', time() + $offset);
    $params = [
        'method'      => 'jimi.oauth.token.get',
        'app_key'     => $k,
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
    foreach ($params as $kp => $vp) {
        if ($kp !== 'sign' && !is_null($vp) && $vp !== '') $raw .= $kp . $vp;
    }
    $sign = strtoupper(md5($s . $raw . $s));
    $params['sign'] = $sign;
    $resp = Illuminate\Support\Facades\Http::asForm()->post($url, $params);
    return $resp->body();
}

$offset = -18000; // -5h correction
foreach ($possible_keys as $k) {
    foreach ($possible_secrets as $s) {
        $res = try_combo($k, $s, $offset);
        echo "Key[".substr($k,0,8)."...] Secret[".substr($s,0,8)."...] -> $res\n";
    }
}
