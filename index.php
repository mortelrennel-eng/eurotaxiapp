<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Google Site Verification - Only show for verification URL
if (strpos($_SERVER['REQUEST_URI'], 'google-site-verification.html') !== false) {
    echo '<!DOCTYPE html><html><head><meta name="google-site-verification" content="dbQmbMBCSaAiOHbRKPuXTz9dS8YxSyxOTSP49Jw7FAs"></head><body><p>Verification file</p></body></html>';
    exit;
}

// Check maintenance mode (kung nasa public_html ang storage)
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Vendor folder ay nasa public_html
require __DIR__.'/vendor/autoload.php';

// Bootstrap folder (kung nasa public_html)
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
