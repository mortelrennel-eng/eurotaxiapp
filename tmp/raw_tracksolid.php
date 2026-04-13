<?php

// Tracksolid Pro API Credentials
$appKey = "8FB345B8693CCD00149EBCB96D0EAE85339A22A4105B6558";
$appSecret = "9ce8f4e1fe3b430c8b94f24aa83b809c";
$username = "Admin_shiellamarie";
$password = "3406d9a5d03ec8d3c3c7b433eee0a8a7";
$apiUrl = "https://hk-open.tracksolidpro.com/route/rest";
$drift = -22500; // Found working drift

function generateSignature($params, $secret) {
    ksort($params);
    $rawString = '';
    foreach ($params as $key => $value) {
        if ($key !== 'sign' && !is_null($value) && $value !== '') {
            $rawString .= $key . $value;
        }
    }
    return strtoupper(md5($secret . $rawString . $secret));
}

echo "--- RAW TRACKSOLID API TEST ---\n";

$timestamp = date('Y-m-d H:i:s', time() + $drift);
echo "Using Timestamp: $timestamp\n";

$params = [
    'method'      => 'jimi.oauth.token.get',
    'app_key'     => $appKey,
    'timestamp'   => $timestamp,
    'format'      => 'json',
    'v'           => '1.0',
    'sign_method' => 'md5',
    'expires_in'  => 7200,
    'user_id'     => $username,
    'user_pwd_md5'=> md5($password),
];

$params['sign'] = generateSignature($params, $appSecret);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($params),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($apiUrl, false, $context);

if ($result === FALSE) {
    echo "[FAILED] Network error or server unreachable.\n";
} else {
    echo "[SUCCESS] Raw Response: $result\n";
    $data = json_decode($result, true);
    if (isset($data['code']) && $data['code'] == 0) {
        echo "[SUCCESS] Access Token: " . $data['result']['accessToken'] . "\n";
    } else {
        echo "[ERROR] Code: " . ($data['code'] ?? 'N/A') . " Message: " . ($data['message'] ?? 'N/A') . "\n";
    }
}
