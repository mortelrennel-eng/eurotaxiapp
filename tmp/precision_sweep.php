<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = '8FB345B8693CCD00149EBCB96D0EAE85';
$secret = '9ce8f4e1fe3b430c8b94f24aa83b809c';
$user = 'Admin_shiellamarie';
$pwd_md5 = '3406d9a5d03ec8d3c3c7b433eee0a8a7';
$url = 'https://hk-open.tracksolidpro.com/route/rest';

function try_precision_auth($offset_seconds) {
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
    return $resp->body();
}

// Precision sweep around 5 hours (18000s)
for ($s = -18030; $s <= -17970; $s++) {
    $res = try_precision_auth($s);
    if (strpos($res, '"code":0') !== false) {
        echo "SUCCESS AT OFFSET $s: $res\n";
        break;
    } else {
        echo "Offset $s: $res\n";
    }
}
