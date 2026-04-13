<?php

require 'C:/xampp/htdocs/eurotaxisystem/vendor/autoload.php';
$app = require_once 'C:/xampp/htdocs/eurotaxisystem/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\TracksolidService;
use Illuminate\Support\Facades\Http;

class BruteForceTracksolid extends TracksolidService {
    public function testDrift($drift) {
        $this->drift = $drift;
        $params = [
            'method'      => 'jimi.oauth.token.get',
            'app_key'     => $this->appKey,
            'timestamp'   => date('Y-m-d H:i:s', time() + $this->drift),
            'format'      => 'json',
            'v'           => '1.0',
            'sign_method' => 'md5',
            'expires_in'  => 7200,
            'user_id'     => $this->username,
            'user_pwd_md5'=> strlen($this->password) === 32 ? $this->password : md5($this->password),
        ];

        $params['sign'] = $this->generateSignature($params);
        
        try {
            $response = Http::asForm()->post($this->apiUrl, $params);
            $data = $response->json();
            return $data;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

$tester = new BruteForceTracksolid();
echo "Testing different drifts from -28800 to 28800 with 300s steps...\n";

for ($offset = -28800; $offset <= 28800; $offset += 300) {
    echo "Testing drift: $offset ... ";
    $res = $tester->testDrift($offset);
    if (isset($res['code']) && $res['code'] == 0) {
        echo "[SUCCESS] Found working drift: $offset\n";
        print_r($res);
        exit;
    } else {
        echo "[FAILED] code: " . ($res['code'] ?? 'N/A') . " message: " . ($res['message'] ?? 'N/A') . "\n";
    }
}
echo "No working drift found.\n";
