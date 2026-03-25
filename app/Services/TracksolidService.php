<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TracksolidService
{
    private string $apiUrl;
    private string $appKey;
    private string $appSecret;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->apiUrl    = rtrim(config('tracksolid.api_url'), '/');
        $this->appKey    = config('tracksolid.app_key');
        $this->appSecret = config('tracksolid.app_secret');
        $this->username  = config('tracksolid.username');
        $this->password  = config('tracksolid.password');
    }

    // ─── Signature Generation ──────────────────────────────

    /**
     * Generate the MD5 signature required by every TracksolidPro API call.
     * Formula (from official API v2.7.14):
     *   UPPERCASE( MD5( appSecret + key1value1key2value2... + appSecret ) )
     * Parameters are sorted alphabetically, 'sign' is excluded.
     */
    private function generateSign(array $params): string
    {
        ksort($params);
        $str = $this->appSecret;
        foreach ($params as $k => $v) {
            $str .= $k . $v;
        }
        $str .= $this->appSecret;

        return strtoupper(md5($str));
    }

    // ─── Common Parameters ─────────────────────────────────

    private function commonParams(string $method): array
    {
        return [
            'method'       => $method,
            'app_key'      => $this->appKey,
            'timestamp'    => gmdate('Y-m-d H:i:s'),   // UTC as required by spec
            'format'       => 'json',
            'v'            => '1.0',                    // API v1.0 enables signature verification
            'sign_method'  => 'md5',
        ];
    }

    // ─── Access Token ──────────────────────────────────────

    /**
     * Retrieve an access token, cached for 100 minutes (tokens valid ~2 hours).
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('tracksolid_access_token', now()->addMinutes(100), function () {
            return $this->fetchAccessToken();
        });
    }

    private function fetchAccessToken(): ?string
    {
        if (!$this->appKey || $this->appKey === 'your_app_key_here') {
            Log::warning('TracksolidPro: API credentials not configured in .env');
            return null;
        }

        $params = $this->commonParams('jimi.oauth.token.get');
        $params['user_id']      = $this->username;
        // Spec requires LOWERCASE md5 of the password
        $params['user_pwd_md5'] = md5($this->password);
        $params['expires_in']   = 7200;

        $params['sign'] = $this->generateSign($params);

        try {
            $response = Http::timeout(10)->post($this->apiUrl, $params);
            $data     = $response->json();

            if (isset($data['result']['accessToken'])) {
                return $data['result']['accessToken'];
            }

            Log::error('TracksolidPro token error: ' . json_encode($data));
        } catch (\Exception $e) {
            Log::error('TracksolidPro token exception: ' . $e->getMessage());
        }

        return null;
    }

    // ─── Real-time Location ────────────────────────────────

    /**
     * Fetch the latest GPS location for one or more device IMEIs.
     *
     * @param  string|array  $imeis
     * @return array  Keyed by IMEI → ['lat','lng','speed','heading','status','acc','last_update']
     */
    public function getDeviceLocations(string|array $imeis): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        $imeiStr = is_array($imeis) ? implode(',', $imeis) : $imeis;

        $params = $this->commonParams('jimi.device.location.get');
        $params['access_token'] = $token;
        $params['imeis']        = $imeiStr;
        // Use GOOGLE map type so lat/lng are GPS-calibrated for maps
        $params['map_type']     = 'GOOGLE';

        $params['sign'] = $this->generateSign($params);

        try {
            $response = Http::timeout(10)->post($this->apiUrl, $params);
            $data     = $response->json();

            if (!isset($data['result']) || !is_array($data['result'])) {
                Log::error('TracksolidPro location error: ' . json_encode($data));
                return [];
            }

            $locations = [];
            foreach ($data['result'] as $device) {
                $imei = $device['imei'] ?? '';
                if (!$imei) continue;

                // API spec: status 0=offline, 1=online; accStatus 0=ACC OFF, 1=ACC ON
                $online    = (int)($device['status']    ?? 0);
                $accStatus = (int)($device['accStatus'] ?? 0);
                $speed     = (float)($device['speed']   ?? 0);

                $locations[$imei] = [
                    'lat'         => (float)($device['lat']  ?? 0),
                    'lng'         => (float)($device['lng']  ?? 0),
                    'speed'       => $speed,
                    'heading'     => (int)($device['course'] ?? 0),
                    'status'      => $this->parseStatus($online, $accStatus, $speed),
                    'acc'         => $accStatus === 1,
                    'last_update' => $device['gpsTime'] ?? now()->toDateTimeString(),
                    'mileage'     => (float)($device['currentMileage'] ?? 0),
                ];
            }

            return $locations;

        } catch (\Exception $e) {
            Log::error('TracksolidPro location exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Determine a human-readable status.
     */
    private function parseStatus(int $online, int $accStatus, float $speed): string
    {
        if (!$online) return 'offline';
        if (!$accStatus) return 'idle';
        return $speed > 0 ? 'active' : 'idle';
    }

    // ─── Single Device ─────────────────────────────────────

    /**
     * Convenience method to get location for a single IMEI.
     */
    public function getDeviceLocation(string $imei): ?array
    {
        $results = $this->getDeviceLocations($imei);
        return $results[$imei] ?? null;
    }
}
