<?php
// Deep diagnostic — test different signature formats and endpoints
$envFile = __DIR__ . '/../.env';
$envVars = [];
foreach (file($envFile) as $line) {
    if (empty(trim($line)) || str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$key, $value] = explode('=', trim($line), 2);
    $envVars[trim($key)] = trim($value);
}

$appKey    = $envVars['TRACKSOLID_APP_KEY'] ?? '';
$appSecret = $envVars['TRACKSOLID_APP_SECRET'] ?? '';
$username  = $envVars['TRACKSOLID_USERNAME'] ?? '';
$password  = $envVars['TRACKSOLID_PASSWORD'] ?? '';

// Test multiple endpoints
$endpoints = [
    'https://hk-open.tracksolidpro.com/route/rest',
    'https://eu-open.tracksolidpro.com/route/rest',
    'http://open.10000track.com/route/rest',
];

// The password stored in env – is it already md5 or plain?
$pwdMd5 = strlen($password) === 32 ? $password : md5($password);

echo "=== DEEP DIAGNOSTIC ===\n";
echo "appKey length: " . strlen($appKey) . "\n";
echo "appSecret length: " . strlen($appSecret) . "\n";
echo "Username: $username\n";
echo "Password MD5: $pwdMd5\n\n";

// Try current timestamp (GMT+8) using gmdate
$timestamp = gmdate('Y-m-d H:i:s', time() + 28800);
echo "Timestamp being sent: $timestamp\n\n";

$baseParams = [
    'method'      => 'jimi.oauth.token.get',
    'app_key'     => $appKey,
    'timestamp'   => $timestamp,
    'format'      => 'json',
    'v'           => '1.0',
    'sign_method' => 'md5',
    'expires_in'  => 7200,
    'user_id'     => $username,
    'user_pwd_md5'=> $pwdMd5,
];

function makeSign(array $params, string $secret, bool $uppercase = true, bool $wrapSecret = true): string {
    $sorted = $params;
    unset($sorted['sign']);
    ksort($sorted);
    $raw = '';
    foreach ($sorted as $k => $v) {
        if (!is_null($v) && $v !== '') $raw .= $k . $v;
    }
    $string = $wrapSecret ? ($secret . $raw . $secret) : $raw;
    $hash = md5($string);
    return $uppercase ? strtoupper($hash) : $hash;
}

function callApi(string $url, array $params): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    if ($curlError) return ['curl_error' => $curlError];
    return json_decode($response, true) ?? ['raw' => $response];
}

// Test variants
$variants = [
    'uppercase+wrap' => makeSign($baseParams, $appSecret, true, true),
    'lowercase+wrap' => makeSign($baseParams, $appSecret, false, true),
    'uppercase+nowrap' => makeSign($baseParams, $appSecret, true, false),
    'lowercase+nowrap' => makeSign($baseParams, $appSecret, false, false),
];

foreach ($endpoints as $endpoint) {
    echo "=== Endpoint: $endpoint ===\n";
    foreach ($variants as $variantName => $sign) {
        $params = $baseParams;
        $params['sign'] = $sign;
        $result = callApi($endpoint, $params);
        $code = $result['code'] ?? ($result['curl_error'] ?? 'ERR');
        $msg = $result['message'] ?? ($result['raw'] ?? '');
        $status = $code == 0 ? '✅ SUCCESS' : "❌ $code";
        echo "  $variantName: $status $msg\n";
        if ($code == 0) {
            echo "  TOKEN: " . ($result['result']['accessToken'] ?? 'N/A') . "\n";
            file_put_contents(__DIR__ . '/good_token.txt', $result['result']['accessToken'] . "\n$endpoint\n$variantName\n");
        }
    }
    echo "\n";
}
